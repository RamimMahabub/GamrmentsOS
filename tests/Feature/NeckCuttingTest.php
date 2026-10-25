<?php

namespace Tests\Feature;

use App\Models\CuttingJob;
use App\Models\CuttingJobRoll;
use App\Models\Staff;
use App\Models\User;
use App\Support\CuttingTotals;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NeckCuttingTest extends TestCase
{
    use RefreshDatabase;

    private CuttingJobRoll $body;

    private Staff $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
        $this->staff = Staff::factory()->create();
        $job = CuttingJob::factory()->create(['cutting_type' => 'body', 'cutting_date' => '2026-10-20', 'staff_id' => $this->staff->id]);
        $this->body = CuttingJobRoll::factory()->create(['cutting_job_id' => $job->id]);
    }

    private function neckData(array $row = []): array
    {
        return ['staff_id' => $this->staff->id, 'cutting_date' => '2026-10-23', 'rows' => [array_replace(['source_body_row_id' => $this->body->id, 'weight_per_dozen' => '1.6'], $row)]];
    }

    private function bodyData(array $row = []): array
    {
        return ['staff_id' => $this->staff->id, 'cutting_date' => '2026-10-20', 'notes' => 'Body metadata updated', 'rows' => [array_replace($this->body->only(['roll_id', 'dozens', 'weight_per_dozen', 'weight_unit', 'remaining_fabric']), $row)]];
    }

    public function test_neck_inherits_body_dozens_origin_and_weight_and_ignores_forged_quantities(): void
    {
        $this->post(route('cutting.neck.store'), $this->neckData(['dozens' => '999', 'roll_id' => 999999, 'roll_weight_kg' => '999']))->assertSessionHasNoErrors();
        $neck = CuttingJobRoll::where('cutting_type', 'neck')->firstOrFail();
        foreach (['roll_id', 'lot_id', 'color', 'dia', 'dozens'] as $field) {
            $this->assertSame($this->body->$field, $neck->$field);
        }
        $this->assertSame($this->body->id, $neck->source_body_row_id);
        $this->assertSame('5.000', $neck->roll_weight_kg);
        $this->assertSame('4.000', $neck->cut_weight_kg);
        $this->assertSame('1.000', $neck->cutting_scrap_kg);
        $this->assertSame('0.000', $neck->remaining_fabric_kg);
        $this->assertSame('1.000', $neck->waste_kg);
        $this->assertSame('20.00', $neck->waste_percent);
        $this->get(route('cutting.neck.show', $neck->cutting_job_id))->assertOk()->assertSee('Body input weight')->assertSee('Body #'.$this->body->cutting_job_id);
    }

    public function test_neck_can_wait_for_results_and_reservations_are_exclusive_and_released_on_delete(): void
    {
        $data = $this->neckData(['weight_per_dozen' => '']);
        $this->post(route('cutting.neck.store'), $data)->assertSessionHasNoErrors();
        $neck = CuttingJobRoll::where('cutting_type', 'neck')->firstOrFail();
        $this->assertSame('2.500', $neck->dozens);
        $this->assertNull($neck->cut_weight_kg);
        $this->assertSame('5.000', CuttingTotals::sum([$neck])['pending_weight_kg']);
        $this->post(route('cutting.neck.store'), $data)->assertSessionHasErrors('rows.0.source_body_row_id');
        $this->get(route('cutting.neck.create'))->assertViewHas('form', fn (array $form): bool => $form['available']->isEmpty());
        $this->get(route('cutting.neck.edit', $neck->cutting_job_id))->assertOk()->assertViewHas('form', fn (array $form): bool => $form['available']->count() === 1);
        $this->put(route('cutting.neck.update', $neck->cutting_job_id), $this->neckData())->assertSessionHasNoErrors();
        $this->assertSame('4.000', $neck->fresh()->cut_weight_kg);
        $this->delete(route('cutting.neck.destroy', $neck->cutting_job_id))->assertSessionHasNoErrors();
        $this->post(route('cutting.neck.store'), $data)->assertSessionHasNoErrors();
    }

    public function test_raw_rolls_pending_body_and_other_sections_cannot_supply_neck_cutting(): void
    {
        $data = $this->neckData();
        unset($data['rows'][0]['source_body_row_id']);
        $data['rows'][0]['roll_id'] = $this->body->roll_id;
        $this->post(route('cutting.neck.store'), $data)->assertSessionHasErrors('rows.0.source_body_row_id');
        $this->body->update(['cut_weight_kg' => null]);
        $this->post(route('cutting.neck.store'), $this->neckData())->assertSessionHasErrors('rows.0.source_body_row_id');
        $this->get(route('cutting.neck.create'))->assertViewHas('form', fn (array $form): bool => $form['available']->isEmpty());
        $this->body->update(['cut_weight_kg' => '5', 'cutting_type' => 'rib']);
        $this->post(route('cutting.neck.store'), $this->neckData())->assertSessionHasErrors('rows.0.source_body_row_id');
        $this->assertDatabaseCount('cutting_jobs', 1);
    }

    public function test_neck_cannot_exceed_body_weight_precede_body_or_record_remaining_roll_fabric(): void
    {
        $this->post(route('cutting.neck.store'), $this->neckData(['weight_per_dozen' => '2.001']))->assertSessionHasErrors('rows.0.weight_per_dozen');
        $this->post(route('cutting.neck.store'), $this->neckData(['remaining_fabric' => '0.001']))->assertSessionHasErrors('rows.0.remaining_fabric');
        $data = $this->neckData();
        $data['cutting_date'] = '2026-10-19';
        $this->post(route('cutting.neck.store'), $data)->assertSessionHasErrors('cutting_date');
        $this->assertDatabaseCount('cutting_jobs', 1);
    }

    public function test_body_identity_survives_metadata_edits_and_used_results_cannot_change_or_be_deleted(): void
    {
        $this->post(route('cutting.neck.store'), $this->neckData())->assertSessionHasNoErrors();
        $this->put(route('cutting.body.update', $this->body->cutting_job_id), $this->bodyData())->assertSessionHasNoErrors();
        $this->assertDatabaseHas('cutting_job_rolls', ['id' => $this->body->id, 'dozens' => '2.500']);
        $this->put(route('cutting.body.update', $this->body->cutting_job_id), $this->bodyData(['dozens' => '3']))->assertSessionHasErrors('job');
        $this->delete(route('cutting.body.destroy', $this->body->cutting_job_id))->assertSessionHasErrors('job');
        $data = $this->bodyData();
        $data['cutting_date'] = '2026-10-24';
        $this->put(route('cutting.body.update', $this->body->cutting_job_id), $data)->assertSessionHasErrors('job');
        $this->assertSame('2.500', $this->body->fresh()->dozens);
        $this->assertDatabaseCount('cutting_job_rolls', 2);
    }

    public function test_picker_and_reports_preserve_dozens_by_lot_color_and_dia(): void
    {
        $otherLot = CuttingJobRoll::factory()->create(['color' => $this->body->color, 'dia' => $this->body->dia]);
        $this->assertCount(2, CuttingTotals::grouped(collect([$this->body, $otherLot])));
        $this->get(route('cutting.neck.create'))->assertOk()->assertViewHas('form', function (array $form): bool {
            $source = $form['available']->firstWhere('source_body_row_id', $this->body->id);

            return $form['isNeck'] && $source['dozens'] === '2.500' && $source['weight_kg'] === '5.000' && $source['lot_id'] === $this->body->lot_id;
        });
        $this->post(route('cutting.neck.store'), $this->neckData())->assertSessionHasNoErrors();
        $this->get(route('lots.show', $this->body->lot_id))->assertOk()->assertSee('0.000 dozen ready for neck cutting')->assertSee('5.000 kg body-cut input')->assertSee('0.000 kg awaiting neck cutting');
        $this->get(route('staffs.show', [$this->staff, 'from' => '2026-10-01', 'to' => '2026-10-31']))->assertOk()->assertSee('Fabric totals by cutting stage')->assertSee('From body #'.$this->body->cutting_job_id);
    }

    public function test_neck_weight_in_pounds_uses_body_weight_as_input(): void
    {
        $this->post(route('cutting.neck.store'), $this->neckData(['weight_unit' => 'lb', 'weight_per_dozen' => '3']))->assertSessionHasNoErrors();
        $neck = CuttingJobRoll::where('cutting_type', 'neck')->firstOrFail();
        $this->assertSame('3.402', $neck->cut_weight_kg);
        $this->assertSame('1.598', $neck->waste_kg);
    }

    public function test_legacy_neck_rows_are_preserved_and_flagged_for_source_review(): void
    {
        $neckJob = CuttingJob::factory()->create(['cutting_type' => 'neck']);
        $legacy = $this->body->replicate();
        $legacy->fill(['cutting_job_id' => $neckJob->id, 'cutting_type' => 'neck'])->save();
        $this->get(route('cutting.neck.show', $neckJob))->assertOk()->assertSee('Body source needs review');
        $this->get(route('lots.show', $this->body->lot_id))->assertOk()->assertSee('Older neck rows need their body source reviewed');
        $this->assertSame('5.000', $legacy->fresh()->cut_weight_kg);
    }
}
