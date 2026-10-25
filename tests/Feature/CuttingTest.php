<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\CuttingJob;
use App\Models\CuttingJobRoll;
use App\Models\ReturnedRoll;
use App\Models\Staff;
use App\Models\User;
use App\Models\Yarn;
use App\Modules\Knitting\KnittingService;
use App\Support\CuttingTotals;
use App\Support\Decimal;
use App\Support\JobTotals;
use App\Support\StaffProductivity;
use Database\Seeders\ReturnedRollSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ManufacturingFixtures;
use Tests\TestCase;

class CuttingTest extends TestCase
{
    use ManufacturingFixtures,RefreshDatabase;

    private Staff $staff;

    private ReturnedRoll $roll;

    private array $dyeing;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
        $this->staff = Staff::factory()->create();
        $company = Company::factory()->create();
        $yarn = Yarn::factory()->withStock()->create();
        $knitting = app(KnittingService::class)->save($this->knittingData($yarn, $company, '100', [32]));
        $this->dyeing = $this->dyeingData($knitting->lot_id, $company, '100', 1, null, [32]);
        $this->dyeing['blocks'][0]['rows'][0]['rolls'] = [['number' => 'ROLL-1', 'weight' => '10', 'unit' => 'kg'], ['number' => 'ROLL-2', 'weight' => '10', 'unit' => 'kg']];
        $this->post(route('dyeing-jobs.store'), $this->dyeing)->assertSessionHasNoErrors();
        $this->roll = ReturnedRoll::where('number', 'ROLL-1')->firstOrFail();
    }

    private function data(array $row = [], array $header = []): array
    {
        return array_replace(['cutting_date' => '2026-10-18', 'staff_id' => $this->staff->id, 'rows' => [array_replace(['roll_id' => $this->roll->id, 'dozens' => '2.5', 'weight_per_dozen' => '2', 'weight_unit' => 'kg', 'remaining_fabric' => '1'], $row)]], $header);
    }

    public function test_kg_waste_and_staff_productivity_and_all_pages(): void
    {
        $this->post(route('cutting.body.store'), $this->data())->assertSessionHasNoErrors();
        $job = CuttingJob::firstOrFail();
        $row = $job->rows->first();
        $this->assertSame('5.000', $row->cut_weight_kg);
        $this->assertSame('4.000', $row->cutting_scrap_kg);
        $this->assertSame('1.000', $row->remaining_fabric_kg);
        $this->assertSame('5.000', $row->waste_kg);
        $this->assertSame('50.00', $row->waste_percent);
        $this->assertSame($row->waste_kg, Decimal::scale(Decimal::of($row->cutting_scrap_kg)->plus($row->remaining_fabric_kg)));
        $this->post(route('cutting.neck.store'), $this->data(['source_body_row_id' => $row->id, 'weight_per_dozen' => '1', 'remaining_fabric' => '0']))->assertSessionHasNoErrors();
        $this->post(route('cutting.rib.store'), $this->data(['dozens' => '0.5']))->assertSessionHasNoErrors();
        $report = app(StaffProductivity::class);
        $entries = $report->entries($this->staff, '2026-10-01', '2026-10-31');
        $totals = $report->totals($entries);
        $this->assertSame('2.500', $totals['cutting:body']['dozens']);
        $this->assertSame('30.000', $totals['cutting:body']['pieces']);
        $this->assertSame('2.500', $totals['cutting:neck']['dozens']);
        $this->assertSame('0.500', $totals['cutting:rib']['dozens']);
        $this->assertCount(0, $report->entries($this->staff, '2026-09-01', '2026-09-30'));
        $this->assertCount(0, $report->entries(Staff::factory()->create(), '2026-10-01', '2026-10-31'));
        foreach (['body', 'neck', 'rib'] as $type) {
            $this->get(route('cutting.'.$type.'.index'))->assertOk()->assertSee('Remaining fabric');
            $this->get(route('cutting.'.$type.'.create'))->assertOk()->assertSee($type === 'neck' ? 'Completed body batches' : 'Available returned rolls');
        }
        $this->get(route('cutting.body.show', $job))->assertOk()->assertSee('ROLL-1');
        $this->get(route('cutting.body.edit', $job))->assertOk();
        $this->get(route('cutting.neck.show', $job))->assertNotFound();
        $this->get(route('staffs.index'))->assertOk();
        $this->get(route('staffs.show', [$this->staff, 'from' => '2026-10-01', 'to' => '2026-10-31']))->assertOk()->assertSee('Grand total: 5.500')->assertSee('Remaining fabric');
        $this->get(route('lots.show', $row->lot_id))->assertOk()->assertSee('still uncut', false)->assertSee('Remaining fabric');
    }

    public function test_lb_normalization_empty_remaining_and_server_values_override_client_values(): void
    {
        $this->post(route('cutting.body.store'), $this->data(['dozens' => '2', 'weight_per_dozen' => '2', 'weight_unit' => 'lb', 'remaining_fabric' => '', 'cut_weight_kg' => '999', 'waste_kg' => '999']))->assertSessionHasNoErrors();
        $row = CuttingJobRoll::firstOrFail();
        $this->assertSame('0.90718474', $row->weight_per_dozen_kg);
        $this->assertSame('1.814', $row->cut_weight_kg);
        $this->assertSame('0.000', $row->remaining_fabric_kg);
        $this->assertSame('8.186', $row->waste_kg);
        $this->assertSame('lb', $row->remaining_fabric_unit);
        $this->put(route('cutting.body.update', $row->cutting_job_id), $this->data(['dozens' => '2', 'weight_per_dozen' => '2', 'weight_unit' => 'lb', 'remaining_fabric' => '1']))->assertSessionHasNoErrors();
        $this->assertSame('0.454', CuttingJobRoll::firstOrFail()->remaining_fabric_kg);
    }

    public function test_missing_remaining_defaults_to_zero(): void
    {
        $data = $this->data();
        unset($data['rows'][0]['remaining_fabric']);
        $this->post(route('cutting.body.store'), $data)->assertSessionHasNoErrors();
        $this->assertSame('0.000', CuttingJobRoll::firstOrFail()->remaining_fabric);
    }

    public function test_overweight_duplicate_invalid_and_inactive_staff_are_rejected_atomically(): void
    {
        $this->post(route('cutting.body.store'), $this->data(['dozens' => '5', 'remaining_fabric' => '0.001']))->assertSessionHasErrors('rows.0.remaining_fabric');
        $this->assertDatabaseCount('cutting_jobs', 0);
        foreach ([['dozens' => '0'], ['weight_per_dozen' => '0'], ['remaining_fabric' => '-1'], ['weight_unit' => 'oz']] as $row) {
            $this->post(route('cutting.body.store'), $this->data($row))->assertSessionHasErrors();
        }
        $data = $this->data();
        $data['rows'][] = $data['rows'][0];
        $this->post(route('cutting.body.store'), $data)->assertSessionHasErrors('rows.0.roll_id');
        $this->post(route('cutting.body.store'), $this->data())->assertSessionHasNoErrors();
        $this->post(route('cutting.body.store'), $this->data())->assertSessionHasErrors('rows.0.roll_id');
        $this->assertDatabaseCount('cutting_jobs', 1);
        $this->staff->update(['is_active' => false]);
        $this->post(route('cutting.neck.store'), $this->data())->assertSessionHasErrors('staff_id');
    }

    public function test_edit_recomputes_delete_releases_and_deactivation_retains_history(): void
    {
        $this->post(route('cutting.body.store'), $this->data())->assertSessionHasNoErrors();
        $job = CuttingJob::firstOrFail();
        $this->put(route('cutting.body.update', $job), $this->data(['dozens' => '3']))->assertSessionHasNoErrors();
        $this->assertSame('6.000', $job->fresh()->rows->first()->cut_weight_kg);
        $this->delete(route('staffs.destroy', $this->staff))->assertRedirect();
        $this->assertDatabaseHas('cutting_jobs', ['id' => $job->id, 'staff_id' => $this->staff->id]);
        $this->delete(route('cutting.body.destroy', $job))->assertRedirect();
        $this->assertDatabaseCount('cutting_job_rolls', 0);
        $this->assertSoftDeleted($job);
        $this->staff->refresh()->update(['is_active' => true]);
        $this->post(route('cutting.body.store'), $this->data())->assertSessionHasNoErrors();
        $this->assertTrue(ActivityLog::where('subject_type', CuttingJob::class)->where('event', 'deleted')->exists());
    }

    public function test_dyeing_cannot_remove_change_or_delete_used_rolls_but_can_edit_notes(): void
    {
        $this->post(route('cutting.body.store'), $this->data())->assertSessionHasNoErrors();
        $id = $this->roll->dyeing_job_id;
        $data = $this->dyeing;
        $data['notes'] = 'Unrelated note';
        $this->put(route('dyeing-jobs.update', $id), $data)->assertSessionHasNoErrors();
        $this->assertSame($this->roll->id, ReturnedRoll::where('number', 'ROLL-1')->firstOrFail()->id);
        $data['blocks'][0]['rows'][0]['rolls'][0]['weight'] = '11';
        $this->put(route('dyeing-jobs.update', $id), $data)->assertSessionHasErrors('blocks');
        $data = $this->dyeing;
        array_shift($data['blocks'][0]['rows'][0]['rolls']);
        $this->put(route('dyeing-jobs.update', $id), $data)->assertSessionHasErrors('blocks');
        $this->delete(route('dyeing-jobs.destroy', $id))->assertSessionHasErrors('job');
        $this->assertTrue($this->roll->fresh()->is_available);
    }

    public function test_filters_select_matching_jobs_and_guest_cannot_access(): void
    {
        $this->post(route('cutting.body.store'), $this->data())->assertSessionHasNoErrors();
        $this->get(route('cutting.body.index', ['staff_id' => $this->staff->id, 'lot_id' => $this->roll->dyeingJob->lot_id, 'color' => 'Golden', 'from' => '2026-10-01', 'to' => '2026-10-31']))->assertOk()->assertSee($this->staff->name);
        $this->get(route('cutting.body.index', ['color' => 'Missing']))->assertOk()->assertSee('No cutting jobs found.');
        auth()->logout();
        $this->get(route('cutting.body.index'))->assertRedirect(route('login'));
    }

    public function test_existing_json_rolls_are_backfilled_and_keep_their_source(): void
    {
        $migration = require database_path('migrations/2026_10_18_203901_create_cutting_tables.php');
        $migration->down();
        $migration->up();
        (require database_path('migrations/2026_10_18_220248_allow_pending_cutting_results.php'))->up();
        $this->assertDatabaseCount('returned_rolls', 2);
        $roll = ReturnedRoll::where('number', 'ROLL-1')->firstOrFail();
        $this->assertSame($this->roll->dyeing_row_id, $roll->dyeing_row_id);
        $this->assertSame('10.000', $roll->weight_kg);
        $this->assertSame('Golden', $roll->dyeingRow->colorBlock->color->name);
    }

    public function test_multiple_rolls_group_and_preserve_the_lot_pipeline(): void
    {
        $data = $this->data();
        $data['rows'][] = array_replace($data['rows'][0], ['roll_id' => ReturnedRoll::where('number', 'ROLL-2')->firstOrFail()->id]);
        $this->post(route('cutting.body.store'), $data)->assertSessionHasNoErrors();
        $job = CuttingJob::firstOrFail();
        $totals = CuttingTotals::sum($job->rows);
        $this->assertSame('5.000', $totals['dozens']);
        $this->assertSame('60.000', $totals['pieces']);
        $this->assertSame('20.000', $totals['roll_weight_kg']);
        $this->assertSame('10.000', $totals['waste_kg']);
        $this->assertCount(1, CuttingTotals::grouped($job->rows));
        $lot = $job->rows->first()->lot;
        $pipeline = JobTotals::pipeline($lot);
        $this->assertSame('100.000', $pipeline['input']);
        $this->assertSame('20.000', $pipeline['returned']);
        $this->assertSame($pipeline['returned'], Decimal::scale(Decimal::of($totals['cut_weight_kg'])->plus($totals['waste_kg'])));
    }

    public function test_factories_and_sample_seeders_produce_consistent_reports(): void
    {
        $row = CuttingJobRoll::factory()->create();
        $this->assertSame('5.000', $row->fresh()->waste_kg);
        $this->assertSame('10.000', $row->roll->dyeingRow->returned_fabric_kg);
        $this->seed(ReturnedRollSeeder::class);
        $this->assertSame($row->roll_id, ReturnedRoll::where('number', $row->roll->number)->firstOrFail()->id);
    }

    public function test_roll_selection_can_be_saved_before_results_and_completed_later(): void
    {
        $data = $this->data([], ['rows' => [['roll_id' => $this->roll->id]]]);
        $this->post(route('cutting.body.store'), $data)->assertSessionHasNoErrors();
        $job = CuttingJob::firstOrFail();
        $row = $job->rows->first();
        $this->assertNull($row->dozens);
        $this->assertNull($row->cut_weight_kg);
        $this->assertNull($row->waste_kg);
        $this->assertSame('0.000', $row->remaining_fabric_kg);
        $totals = CuttingTotals::sum($job->rows);
        $this->assertSame(1, $totals['pending_count']);
        $this->assertSame('10.000', $totals['pending_weight_kg']);
        $this->assertSame('0.000', $totals['roll_weight_kg']);
        $this->assertSame('0.000', $totals['waste_kg']);
        $this->assertCount(0, app(StaffProductivity::class)->entries($this->staff, '2026-10-01', '2026-10-31'));
        $this->get(route('cutting.body.show', $job))->assertOk()->assertSee('Results pending');
        $this->get(route('cutting.body.index'))->assertOk()->assertSee('1 pending');
        $this->get(route('lots.show', $row->lot_id))->assertOk()->assertSee('20.000 kg still uncut');
        $this->get(route('cutting.body.create'))->assertOk()->assertDontSee('aria-label="Dozens"', false)->assertSee('Add dozens, weights and remaining fabric later through Edit.');
        $this->get(route('cutting.body.edit', $job))->assertOk()->assertSee('aria-label="Dozens"', false);
        $this->post(route('cutting.body.store'), $data)->assertSessionHasErrors('rows.0.roll_id');
        $this->put(route('cutting.body.update', $job), $this->data())->assertSessionHasNoErrors();
        $row = $job->fresh('rows')->rows->first();
        $this->assertSame('5.000', $row->cut_weight_kg);
        $this->assertSame('5.000', $row->waste_kg);
        $this->assertSame(0, CuttingTotals::sum([$row])['pending_count']);
        $this->assertCount(1, app(StaffProductivity::class)->entries($this->staff, '2026-10-01', '2026-10-31'));
    }

    public function test_edit_can_record_some_rolls_while_other_results_remain_pending(): void
    {
        $second = ReturnedRoll::where('number', 'ROLL-2')->firstOrFail();
        $data = $this->data([], ['rows' => [['roll_id' => $this->roll->id], ['roll_id' => $second->id]]]);
        $this->post(route('cutting.body.store'), $data)->assertSessionHasNoErrors();
        $job = CuttingJob::firstOrFail();
        $data = $this->data();
        $data['rows'][] = ['roll_id' => $second->id, 'dozens' => '', 'weight_per_dozen' => '', 'remaining_fabric' => ''];
        $this->put(route('cutting.body.update', $job), $data)->assertSessionHasNoErrors();
        $totals = CuttingTotals::sum($job->fresh('rows')->rows);
        $this->assertSame(1, $totals['pending_count']);
        $this->assertSame('2.500', $totals['dozens']);
        $this->assertSame('10.000', $totals['roll_weight_kg']);
        $this->assertSame('5.000', $totals['waste_kg']);
        $this->assertCount(1, app(StaffProductivity::class)->entries($this->staff, '2026-10-01', '2026-10-31'));
        $this->get(route('lots.show', $job->rows->first()->lot_id))->assertOk()->assertSee('10.000 kg still uncut');
    }

    public function test_partial_result_pairs_or_remaining_fabric_without_results_are_rejected(): void
    {
        $data = $this->data([], ['rows' => [['roll_id' => $this->roll->id, 'dozens' => '2']]]);
        $this->post(route('cutting.body.store'), $data)->assertSessionHasErrors('rows.0.weight_per_dozen');
        $data['rows'][0] = ['roll_id' => $this->roll->id, 'weight_per_dozen' => '2'];
        $this->post(route('cutting.body.store'), $data)->assertSessionHasErrors('rows.0.dozens');
        $data['rows'][0] = ['roll_id' => $this->roll->id, 'remaining_fabric' => '1'];
        $this->post(route('cutting.body.store'), $data)->assertSessionHasErrors('rows.0.remaining_fabric');
        $this->assertDatabaseCount('cutting_jobs', 0);
    }
}
