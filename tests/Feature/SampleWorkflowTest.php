<?php

namespace Tests\Feature;

use App\Models\DyeingJob;
use App\Models\Lot;
use App\Models\User;
use App\Models\Yarn;
use App\Modules\Dyeing\DyedFabricBalance;
use App\Modules\Yarn\StockLedger;
use App\Support\Decimal as D;
use App\Support\JobTotals;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SampleWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_sample_workflow_reconciles_every_stage_and_preserves_owner_on_replacement(): void
    {
        $owner = User::factory()->create();
        $password = $owner->password;
        $this->artisan('garmentos:sample-workflow', ['--replace' => true])->assertSuccessful();
        $this->assertSame($password, $owner->fresh()->password);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('lots', 3);
        $this->assertDatabaseCount('yarns', 3);
        $this->assertDatabaseCount('dyeing_jobs', 2);
        $rollCount = 0;
        foreach (Lot::orderBy('id')->get() as $lot) {
            $totals = JobTotals::pipeline($lot);
            $accounted = D::sum(array_intersect_key($totals, array_flip(['knitting_loss', 'undyed', 'pending', 'dyeing_loss', 'returned'])));
            $this->assertSame($totals['input'], D::scale($accounted));
            $this->assertSame($totals['returned'], $totals['roll_weight']);
            $this->assertSame('0.000', $totals['unitemized']);
            $this->assertSame($totals['returned'], D::scale(D::sum(array_column(app(DyedFabricBalance::class)->forLot($lot), 'quantity_kg'))));
            $rollCount += $totals['roll_count'];
        }
        $this->assertSame(135, $rollCount);
        foreach (Lot::all() as $lot) {
            $this->assertCount(6, $lot->knittingJob->diaRows);
        }
        foreach (DyeingJob::all() as $job) {
            $this->assertCount(6, $job->colorBlocks);
            foreach ($job->colorBlocks as $block) {
                $this->assertCount(6, $block->rows);
                foreach ($block->rows as $row) {
                    if ($row->returned_fabric_kg !== null) {
                        $this->assertContains(count($row->rolls), [2, 3]);
                        $this->assertSame($row->returned_fabric_kg, D::scale(D::sum(array_column($row->rolls, 'weight_kg'))));
                    }
                }
            }
        }
        $finished = JobTotals::pipeline(Lot::orderBy('id')->firstOrFail());
        $this->assertSame('500.000', $finished['input']);
        $this->assertSame('7.500', $finished['knitting_loss']);
        $this->assertSame('472.800', $finished['returned']);
        $this->assertSame('19.700', $finished['dyeing_loss']);
        $this->assertSame('0.000', $finished['pending']);
        $this->assertSame('1700.000', app(StockLedger::class)->balance(Yarn::where('name', 'Combed Cotton')->firstOrFail()));
        $this->assertSame('500.000', app(StockLedger::class)->balance(Yarn::where('name', 'Grey Melange')->firstOrFail()));
        $this->artisan('garmentos:sample-workflow', ['--replace' => true])->assertSuccessful();
        $this->assertDatabaseCount('lots', 3);
        $this->assertDatabaseCount('dyeing_jobs', 2);
        $this->assertSame($password, $owner->fresh()->password);
    }

    public function test_changing_roll_color_keeps_job_weight_after_save_and_edit(): void
    {
        $this->seed();
        $this->actingAs(User::firstOrFail());
        $job = DyeingJob::orderBy('id')->firstOrFail();
        $blocks = $job->colorBlocks->map(fn ($block): array => ['color' => $block->color->name, 'rows' => $block->rows->map(fn ($row): array => ['dia' => $row->dia, 'given_fabric_kg' => $row->given_fabric_kg, 'returned_fabric_kg' => $row->returned_fabric_kg, 'rolls' => $row->rolls])->all()])->all();
        $before = JobTotals::pipeline($job->lot)['returned'];
        $roll = array_shift($blocks[0]['rows'][0]['rolls']);
        $blocks[1]['rows'][0]['rolls'][] = $roll;
        $data = ['confirm_excess_return' => true, 'company_id' => $job->company_id, 'lot_id' => $job->lot_id, 'entry_date' => $job->entry_date->format('Y-m-d'), 'return_date' => $job->return_date->format('Y-m-d'), 'blocks' => $blocks];
        $this->put(route('dyeing-jobs.update', $job), $data)->assertSessionHasNoErrors();
        $this->assertSame($before, JobTotals::pipeline($job->lot->fresh())['returned']);
        $target = $job->fresh()->colorBlocks->firstWhere('color.name', $blocks[1]['color'])->rows->first();
        $this->assertContains($roll['number'], array_column($target->rolls, 'number'));
        $this->get(route('dyeing-jobs.edit', $job))->assertOk()->assertSee('Roll color');
    }
}
