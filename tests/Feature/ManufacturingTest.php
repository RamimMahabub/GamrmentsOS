<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\DyeingJob;
use App\Models\KnittingJob;
use App\Models\User;
use App\Models\Yarn;
use App\Models\YarnStockMovement;
use App\Modules\Dyeing\DyedFabricBalance;
use App\Modules\Dyeing\FabricAvailability;
use App\Modules\Knitting\KnittingService;
use App\Modules\Knitting\LotGenerator;
use App\Modules\Yarn\StockLedger;
use App\Support\Decimal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ManufacturingFixtures;
use Tests\TestCase;

class ManufacturingTest extends TestCase
{
    use ManufacturingFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_purchase_knitting_and_six_color_dyeing_full_flow(): void
    {
        $company = Company::factory()->create();
        $this->post('/yarns', ['name' => 'Sudan', 'yarn_type' => '34 Single', 'country_of_origin' => 'India', 'price_per_kg' => '4.409245', 'price_unit' => 'lb',
            'bag_size_kg' => '45.35923700', 'bag_size_unit' => 'lb', 'initial_kg' => '1,133.981', 'movement_date' => '2026-05-01'])->assertSessionHasNoErrors()->assertRedirect();
        $yarn = Yarn::firstOrFail();
        $this->post(route('yarns.stock', $yarn), ['quantity_kg' => '100.000', 'movement_date' => '2026-05-02'])->assertSessionHasNoErrors();
        $this->assertSame('1233.981', app(StockLedger::class)->balance($yarn));
        $this->post('/knitting-jobs', $this->knittingData($yarn, $company))->assertSessionHasNoErrors()->assertRedirect();
        $job = KnittingJob::firstOrFail();
        $this->assertSame('SU-100526', $job->lot->lot_number);
        $this->assertSame(['226.796', '226.796', '226.796', '226.796'], $job->diaRows->pluck('output_fabric_kg')->all());
        $this->assertSame('326.797', app(StockLedger::class)->balance($yarn));
        $response = $this->get('/api/lots/'.$job->lot_id.'/fabric?colors=6')->assertOk();
        $this->assertSame('37.799', $response->json('rows.0.split.0'));
        $this->assertSame('37.801', $response->json('rows.0.split.5'));
        $this->post('/dyeing-jobs', $this->dyeingData($job->lot_id, $company))->assertSessionHasNoErrors()->assertRedirect();
        $dyeing = DyeingJob::with('colorBlocks.rows')->firstOrFail();
        $this->assertCount(6, $dyeing->colorBlocks);
        $this->assertSame('7.01', $dyeing->colorBlocks->first()->rows->first()->waste_percent);
        $this->assertSame('0.002', app(FabricAvailability::class)->forLot($job->lot->fresh())[0]['available_kg']);
        $this->assertSame('35.150', app(DyedFabricBalance::class)->forLot($job->lot)[0]['quantity_kg']);
        $this->assertSame(auth()->id(), $job->created_by);
        $this->assertTrue(ActivityLog::where('subject_type', KnittingJob::class)->where('event', 'created')->exists());
        $this->get(route('lots.show', $job->lot))->assertOk()->assertSee('SU-100526')->assertSee('Golden');
    }

    public function test_returned_rolls_preserve_units_and_total_weights_on_edit(): void
    {
        $yarn = Yarn::factory()->withStock()->create();
        $company = Company::factory()->create();
        $knitting = app(KnittingService::class)->save($this->knittingData($yarn, $company));
        $data = $this->dyeingData($knitting->lot_id, $company, '100.000', 1, null);
        $data['blocks'][0]['rows'][0]['rolls'] = [
            ['number' => 'ROLL-A', 'weight' => '10', 'unit' => 'kg'],
            ['number' => 'ROLL-B', 'weight' => '20', 'unit' => 'lb'],
        ];
        $this->post('/dyeing-jobs', $data)->assertSessionHasNoErrors();
        $job = DyeingJob::firstOrFail();
        $row = $job->colorBlocks->first()->rows->first();
        $this->assertSame('19.072', $row->returned_fabric_kg);
        $this->assertSame('lb', $row->rolls[1]['unit']);
        $this->assertSame('9.072', $row->rolls[1]['weight_kg']);
        $this->get(route('dyeing-jobs.edit', $job))->assertOk()->assertSee('ROLL-A');
        $this->get(route('lots.show', $knitting->lot))->assertOk()->assertSee('ROLL-B');
        $this->put(route('dyeing-jobs.update', $job), $data)->assertSessionHasNoErrors();
        $this->assertSame($row->rolls, $row->fresh()->rolls);
        $data['blocks'][0]['rows'][1]['rolls'] = [['number' => 'roll-a', 'weight' => '1', 'unit' => 'kg']];
        $this->put(route('dyeing-jobs.update', $job), $data)->assertSessionHasErrors('blocks.0.rows.1.rolls.0.number');
        $this->assertSame('19.072', $row->fresh()->returned_fabric_kg);
        $data['blocks'][0]['rows'][1]['rolls'] = [['number' => 'ROLL-C', 'weight' => '0', 'unit' => 'kg']];
        $this->put(route('dyeing-jobs.update', $job), $data)->assertSessionHasErrors('blocks.0.rows.1.rolls.0.weight');
    }

    public function test_stock_shortfall_rolls_back_all_writes(): void
    {
        $yarn = Yarn::factory()->withStock('100.000')->create();
        $company = Company::factory()->create();
        $this->post('/knitting-jobs', $this->knittingData($yarn, $company))->assertSessionHasErrors('yarn_ids');
        $this->assertDatabaseCount('lots', 0);
        $this->assertDatabaseCount('knitting_jobs', 0);
        $this->assertSame('100.000', app(StockLedger::class)->balance($yarn));
    }

    public function test_knitting_edit_reverses_old_issues_and_delete_restores_stock(): void
    {
        $yarn = Yarn::factory()->withStock('1000.000')->create(['name' => 'Sudan']);
        $company = Company::factory()->create();
        $job = app(KnittingService::class)->save($this->knittingData($yarn, $company));
        $changed = $this->knittingData($yarn, $company, '100.000');
        $changed['entry_date'] = '2026-05-11';
        $this->put(route('knitting-jobs.update', $job), $changed)->assertSessionHasNoErrors();
        $this->assertSame('600.000', app(StockLedger::class)->balance($yarn));
        $this->assertDatabaseHas('activity_logs', ['subject_type' => KnittingJob::class, 'subject_id' => $job->id, 'event' => 'updated']);
        $this->assertSame('SU-100526', $job->lot->fresh()->lot_number);
        $this->assertSame('2026-05-10', $job->lot->fresh()->lot_date->format('Y-m-d'));
        $this->assertDatabaseHas('yarn_stock_movements', ['type' => 'reversal', 'quantity_kg' => '907.184']);
        $this->delete(route('knitting-jobs.destroy', $job))->assertSessionHasNoErrors();
        $this->assertSame('1000.000', app(StockLedger::class)->balance($yarn));
        $this->assertSoftDeleted($job);
        $this->assertSoftDeleted($job->lot);
        $this->assertDatabaseCount('yarn_stock_movements', 5);
    }

    public function test_failed_edit_preserves_old_ledger_and_rows(): void
    {
        $yarn = Yarn::factory()->withStock()->create();
        $company = Company::factory()->create();
        $job = app(KnittingService::class)->save($this->knittingData($yarn, $company, '100.000'));
        $count = YarnStockMovement::count();
        $this->put(route('knitting-jobs.update', $job), $this->knittingData($yarn, $company, '500.000'))->assertSessionHasErrors('yarn_ids');
        $this->assertSame($count, YarnStockMovement::count());
        $this->assertSame('600.000', app(StockLedger::class)->balance($yarn));
        $this->assertSame('100.000', $job->diaRows()->first()->input_kg);
    }

    public function test_dyeing_over_allocation_is_rejected_across_jobs_and_edits(): void
    {
        $yarn = Yarn::factory()->withStock()->create();
        $company = Company::factory()->create();
        $job = app(KnittingService::class)->save($this->knittingData($yarn, $company));
        $this->post('/dyeing-jobs', $this->dyeingData($job->lot_id, $company))->assertSessionHasNoErrors();
        $this->post('/dyeing-jobs', $this->dyeingData($job->lot_id, $company, '0.003', 1, '0'))->assertSessionHasErrors('blocks');
        $dyeing = DyeingJob::first();
        $this->put(route('dyeing-jobs.update', $dyeing), $this->dyeingData($job->lot_id, $company, '38.000'))->assertSessionHasErrors('blocks');
        $this->assertDatabaseCount('dyeing_jobs', 1);
        $this->assertSame('0.002', app(FabricAvailability::class)->forLot($job->lot->fresh())[0]['available_kg']);
    }

    public function test_dyeing_edit_excludes_its_own_allocations_and_delete_releases_fabric(): void
    {
        $yarn = Yarn::factory()->withStock()->create();
        $company = Company::factory()->create();
        $job = app(KnittingService::class)->save($this->knittingData($yarn, $company));
        $data = $this->dyeingData($job->lot_id, $company, '100.000', 1, null);
        $this->post('/dyeing-jobs', $data)->assertSessionHasNoErrors();
        $dyeing = DyeingJob::first();
        $this->assertNull($dyeing->colorBlocks->first()->rows->first()->waste_percent);
        $data['blocks'][0]['rows'][0]['returned_fabric_kg'] = '90.000';
        $this->put(route('dyeing-jobs.update', $dyeing), $data)->assertSessionHasNoErrors();
        $this->assertSame('10.00', $dyeing->fresh()->colorBlocks->first()->rows->first()->waste_percent);
        $this->delete(route('dyeing-jobs.destroy', $dyeing))->assertSessionHasNoErrors();
        $this->assertSame('226.796', app(FabricAvailability::class)->forLot($job->lot->fresh())[0]['available_kg']);
        $this->assertSame('knitting', $job->lot->fresh()->current_stage);
        $this->assertSame([], app(DyedFabricBalance::class)->forLot($job->lot));
    }

    public function test_knitting_cannot_remove_dia_or_reduce_output_already_in_dyeing(): void
    {
        $yarn = Yarn::factory()->withStock()->create();
        $company = Company::factory()->create();
        $job = app(KnittingService::class)->save($this->knittingData($yarn, $company));
        $this->post('/dyeing-jobs', $this->dyeingData($job->lot_id, $company, '100.000', 1, null))->assertSessionHasNoErrors();
        $this->put(route('knitting-jobs.update', $job), $this->knittingData($yarn, $company, '90.000'))->assertSessionHasErrors('rows');
        $this->put(route('knitting-jobs.update', $job), $this->knittingData($yarn, $company, '226.796', [32, 34, 36]))->assertSessionHasErrors('rows');
        $this->delete(route('knitting-jobs.destroy', $job))->assertSessionHasErrors('job');
    }

    public function test_returns_greater_than_given_need_explicit_confirmation(): void
    {
        $yarn = Yarn::factory()->withStock()->create();
        $company = Company::factory()->create();
        $job = app(KnittingService::class)->save($this->knittingData($yarn, $company));
        $data = $this->dyeingData($job->lot_id, $company, '100.000', 1, '110.000');
        $this->post('/dyeing-jobs', $data)->assertSessionHasErrors('confirm_excess_return');
        $data['confirm_excess_return'] = '1';
        $this->post('/dyeing-jobs', $data)->assertSessionHasNoErrors();
        $this->assertSame('-10.00', DyeingJob::first()->colorBlocks->first()->rows->first()->waste_percent);
    }

    public function test_lot_prefixes_suffixes_and_deleted_numbers_are_not_reused(): void
    {
        $generator = app(LotGenerator::class);
        $yarn = Yarn::factory()->create(['name' => ' 1 Su dan 34 ']);
        $one = $generator->create($yarn, '2026-05-10');
        $two = $generator->create($yarn, '2026-05-10');
        $this->assertSame('SU-100526', $one->lot_number);
        $this->assertSame('SU-100526-2', $two->lot_number);
        $one->delete();
        $this->assertSame('SU-100526-3', $generator->create($yarn, '2026-05-10')->lot_number);
        $yarn->name = '7A';
        $this->assertSame('A-100526', $generator->base($yarn, '2026-05-10'));
        $yarn->name = '123';
        $this->assertSame('-100526', $generator->base($yarn, '2026-05-10'));
        $yarn->name = ' 9 é lan ';
        $this->assertSame('ÉL-100526', $generator->base($yarn, '2026-05-10'));
    }

    public function test_multiple_yarns_are_totaled_and_reconciled_separately(): void
    {
        $company = Company::factory()->create();
        $first = Yarn::factory()->withStock('100.000')->create(['name' => 'Sudan']);
        $second = Yarn::factory()->withStock('100.000')->create();
        $data = $this->knittingData($first, $company, '50.000', [32]);
        $data['yarn_ids'][] = $second->id;
        $data['rows'][0]['quantities'][$second->id] = '25.000';
        $this->post('/knitting-jobs', $data)->assertSessionHasNoErrors();
        $job = KnittingJob::first();
        $this->assertSame('75.000', $job->diaRows->first()->input_kg);
        $this->assertSame('50.000', app(StockLedger::class)->balance($first));
        $this->assertSame('75.000', app(StockLedger::class)->balance($second));
    }

    public function test_confirmed_added_mass_preserves_large_negative_waste_at_database_precision(): void
    {
        $yarn = Yarn::factory()->withStock()->create();
        $company = Company::factory()->create();
        $job = app(KnittingService::class)->save($this->knittingData($yarn, $company, '200.000', [32]));
        $data = $this->dyeingData($job->lot_id, $company, '0.001', 1, '1000000000.000', [32]);
        $data['confirm_excess_return'] = '1';
        $this->post('/dyeing-jobs', $data)->assertSessionHasNoErrors();
        $this->assertSame('-99999999999900.00', DyeingJob::first()->colorBlocks->first()->rows->first()->waste_percent);
    }

    public function test_stock_ledger_pagination_starts_from_the_previous_page_balance(): void
    {
        $yarn = Yarn::factory()->withStock()->create();
        for ($i = 0; $i < 30; $i++) {
            app(StockLedger::class)->write($yarn, 'purchase', '1.000', '2026-05-01');
        }
        $this->get(route('yarns.show', $yarn))->assertOk()->assertViewHas('running', fn ($value): bool => Decimal::scale($value) === '0.000');
        $this->get(route('yarns.show', $yarn).'?page=2')->assertOk()->assertViewHas('running', fn ($value): bool => Decimal::scale($value) === '1024.000')->assertSee('1025.000');
    }
}
