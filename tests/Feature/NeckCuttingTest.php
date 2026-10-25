<?php

namespace Tests\Feature;

use App\Models\CuttingJob;
use App\Models\CuttingJobRoll;
use App\Models\Staff;
use App\Models\User;
use App\Support\CuttingTotals;
use App\Support\StaffProductivity;
use Illuminate\Database\QueryException;
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
        return ['staff_id' => $this->staff->id, 'cutting_date' => '2026-10-23', 'rows' => [array_replace(['source_body_row_id' => $this->body->id, 'dozens' => $this->body->dozens, 'weight_per_dozen' => '1.6'], $row)]];
    }

    private function bodyData(array $row = []): array
    {
        return ['staff_id' => $this->staff->id, 'cutting_date' => '2026-10-20', 'notes' => 'Body metadata updated', 'rows' => [array_replace($this->body->only(['roll_id', 'dozens', 'weight_per_dozen', 'weight_unit', 'remaining_fabric']), $row)]];
    }

    public function test_neck_preserves_body_origin_and_ignores_forged_source_metadata(): void
    {
        $this->post(route('cutting.neck.store'), $this->neckData(['roll_id' => 999999, 'roll_weight_kg' => '999']))->assertSessionHasNoErrors();
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

    public function test_blank_neck_weight_defaults_to_zero_waste_and_quantities_are_released_on_delete(): void
    {
        $data = $this->neckData(['weight_per_dozen' => '']);
        $this->post(route('cutting.neck.store'), $data)->assertSessionHasNoErrors();
        $neck = CuttingJobRoll::where('cutting_type', 'neck')->firstOrFail();
        $this->assertSame('2.500', $neck->dozens);
        $this->assertSame($neck->roll_weight_kg, $neck->cut_weight_kg);
        $this->assertTrue($neck->weight_is_automatic);
        $this->assertSame('0.00', $neck->waste_percent);
        $this->assertSame('0.000', CuttingTotals::sum([$neck])['pending_weight_kg']);
        $this->post(route('cutting.neck.store'), $data)->assertSessionHasErrors('rows.0.dozens');
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
        $this->get(route('cutting.neck.create'))->assertViewHas('form', fn (array $form): bool => $form['available']->isEmpty());
    }

    public function test_partial_body_quantities_can_supply_multiple_neck_jobs(): void
    {
        $this->post(route('cutting.neck.store'), $this->neckData(['dozens' => '1']))->assertSessionHasNoErrors();
        $first = CuttingJobRoll::where('cutting_type', 'neck')->firstOrFail();
        $this->assertSame('1.000', $first->dozens);
        $this->assertSame('2.000', $first->roll_weight_kg);
        $this->get(route('lots.show', $this->body->lot_id))->assertOk()
            ->assertSee('1.500 dozen ready for neck cutting')
            ->assertSee('3.000 kg awaiting neck cutting');
        $this->assertSame('1.600', $first->cut_weight_kg);
        $this->assertSame('0.400', $first->waste_kg);
        $this->get(route('cutting.neck.create'))->assertOk()->assertSee('Quantity for neck')->assertViewHas('form', function (array $form): bool {
            $source = $form['available']->firstWhere('source_body_row_id', $this->body->id);

            return $source['dozens'] === '1.500' && $source['weight_kg'] === '3.000' && $source['body_dozens'] === '2.500';
        });
        $this->get(route('cutting.neck.edit', $first->cutting_job_id))->assertViewHas('form', fn (array $form): bool => $form['rows'][0]['dozens'] === '1.000');
        $this->post(route('cutting.neck.store'), $this->neckData(['dozens' => '1.5']))->assertSessionHasNoErrors();
        $totals = CuttingTotals::sum(CuttingJobRoll::where('cutting_type', 'neck')->get());
        $this->assertSame('2.500', $totals['dozens']);
        $this->assertSame('5.000', $totals['roll_weight_kg']);
        $this->assertSame('4.000', $totals['cut_weight_kg']);
        $this->assertSame('1.000', $totals['waste_kg']);
        $this->get(route('cutting.neck.create'))->assertViewHas('form', fn (array $form): bool => $form['available']->isEmpty());
    }

    public function test_partial_automatic_quantities_are_released_on_edit_and_delete(): void
    {
        $this->post(route('cutting.neck.store'), $this->neckData(['dozens' => '1', 'weight_per_dozen' => '']))->assertSessionHasNoErrors();
        $first = CuttingJobRoll::where('cutting_type', 'neck')->firstOrFail();
        $this->assertSame('2.000', $first->cut_weight_kg);
        $this->assertSame('0.00', $first->waste_percent);
        $this->assertSame('2.000', $first->roll_weight_kg);
        $this->get(route('lots.show', $this->body->lot_id))->assertOk()
            ->assertSee('1.500 dozen ready for neck cutting')
            ->assertSee('3.000 kg awaiting neck cutting')
            ->assertSee('2.000 kg cut');
        $this->put(route('cutting.neck.update', $first->cutting_job_id), $this->neckData(['dozens' => '0.5', 'weight_per_dozen' => '']))->assertSessionHasNoErrors();
        $this->assertSame('1.000', $first->fresh()->roll_weight_kg);
        $this->post(route('cutting.neck.store'), $this->neckData(['dozens' => '1', 'weight_per_dozen' => '']))->assertSessionHasNoErrors();
        $second = CuttingJobRoll::where('cutting_type', 'neck')->where('id', '!=', $first->id)->firstOrFail();
        $this->get(route('cutting.neck.edit', $first->cutting_job_id))->assertViewHas('form', fn (array $form): bool => $form['available']->first()['dozens'] === '1.500');
        $this->put(route('cutting.neck.update', $first->cutting_job_id), $this->neckData(['dozens' => '2', 'weight_per_dozen' => '']))->assertSessionHasErrors('rows.0.dozens');
        $this->assertSame('0.500', $first->fresh()->dozens);
        $this->delete(route('cutting.neck.destroy', $second->cutting_job_id))->assertSessionHasNoErrors();
        $this->get(route('cutting.neck.create'))->assertViewHas('form', fn (array $form): bool => $form['available']->first()['dozens'] === '2.000');
        $this->put(route('cutting.neck.update', $first->cutting_job_id), $this->neckData())->assertSessionHasNoErrors();
        $this->assertSame('2.500', $first->fresh()->dozens);
        $this->assertSame('5.000', $first->fresh()->roll_weight_kg);
    }

    public function test_partial_neck_quantities_must_be_positive_available_and_fit_their_allocated_weight(): void
    {
        foreach (['0', '-1', '0.0001', '2.501', '999'] as $dozens) {
            $this->post(route('cutting.neck.store'), $this->neckData(['dozens' => $dozens]))->assertSessionHasErrors('rows.0.dozens');
        }
        $data = $this->neckData();
        unset($data['rows'][0]['dozens']);
        $this->post(route('cutting.neck.store'), $data)->assertSessionHasErrors('rows.0.dozens');
        $this->post(route('cutting.neck.store'), $this->neckData(['dozens' => '1', 'weight_per_dozen' => '2.001']))->assertSessionHasErrors('rows.0.weight_per_dozen');
        $this->assertDatabaseCount('cutting_jobs', 1);
    }

    public function test_last_partial_allocation_receives_the_weight_rounding_remainder(): void
    {
        $this->body->update(['dozens' => '3', 'cut_weight_kg' => '1']);
        for ($part = 0; $part < 3; $part++) {
            $this->post(route('cutting.neck.store'), $this->neckData(['dozens' => '1', 'weight_per_dozen' => '0.2']))->assertSessionHasNoErrors();
        }
        $rows = CuttingJobRoll::where('cutting_type', 'neck')->orderBy('id')->get();
        $this->assertSame(['0.333', '0.333', '0.334'], $rows->pluck('roll_weight_kg')->all());
        $this->assertSame('1.000', CuttingTotals::sum($rows)['roll_weight_kg']);
        $this->assertSame('0.400', CuttingTotals::sum($rows)['waste_kg']);
    }

    public function test_tiny_partial_quantities_do_not_divide_by_zero_or_lose_body_weight(): void
    {
        $this->body->update(['dozens' => '3', 'cut_weight_kg' => '0.001']);
        for ($part = 0; $part < 3; $part++) {
            $this->post(route('cutting.neck.store'), $this->neckData(['dozens' => '1', 'weight_per_dozen' => '0.000001']))->assertSessionHasNoErrors();
        }
        $rows = CuttingJobRoll::where('cutting_type', 'neck')->orderBy('id')->get();
        $this->assertSame(['0.000', '0.000', '0.001'], $rows->pluck('roll_weight_kg')->all());
        $this->assertSame('0.001', CuttingTotals::sum($rows)['roll_weight_kg']);
        $this->assertSame('0.00', $rows->first()->waste_percent);
    }

    public function test_body_date_must_precede_every_partial_neck_assignment(): void
    {
        $later = $this->neckData(['dozens' => '1']);
        $later['cutting_date'] = '2026-10-25';
        $this->post(route('cutting.neck.store'), $later)->assertSessionHasNoErrors();
        $this->post(route('cutting.neck.store'), $this->neckData(['dozens' => '1']))->assertSessionHasNoErrors();
        $bodyData = $this->bodyData();
        $bodyData['cutting_date'] = '2026-10-24';
        $this->put(route('cutting.body.update', $this->body->cutting_job_id), $bodyData)->assertSessionHasErrors('job');
    }

    public function test_database_still_prevents_duplicate_body_assignments(): void
    {
        $this->expectException(QueryException::class);

        CuttingJobRoll::factory()->create(['roll_id' => $this->body->roll_id, 'cutting_type' => 'body']);
    }

    public function test_database_still_prevents_duplicate_rib_assignments(): void
    {
        $first = CuttingJob::factory()->create(['cutting_type' => 'rib']);
        CuttingJobRoll::factory()->create(['cutting_job_id' => $first->id, 'roll_id' => $this->body->roll_id]);
        $second = CuttingJob::factory()->create(['cutting_type' => 'rib']);
        $this->expectException(QueryException::class);

        CuttingJobRoll::factory()->create(['cutting_job_id' => $second->id, 'roll_id' => $this->body->roll_id]);
    }

    public function test_combined_quantity_stores_exact_pieces_and_preserves_the_remaining_balance(): void
    {
        $this->body->update(['dozens' => '15', 'cut_weight_kg' => '0.900']);
        $data = $this->neckData(['quantity_mode' => 'dozen_pieces', 'quantity_dozens' => '10', 'quantity_pieces' => '4', 'weight_per_dozen' => '0.05']);
        unset($data['rows'][0]['dozens']);
        $this->post(route('cutting.neck.store'), $data)->assertSessionHasNoErrors();
        $neck = CuttingJobRoll::where('cutting_type', 'neck')->firstOrFail();
        $this->assertSame('124.000', $neck->pieces);
        $this->assertSame('10.333', $neck->dozens);
        $this->assertSame('0.620', $neck->roll_weight_kg);
        $this->assertSame('0.517', $neck->cut_weight_kg);
        $this->assertSame('124.000', CuttingTotals::sum([$neck])['pieces']);
        $this->get(route('cutting.neck.show', $neck->cutting_job_id))->assertOk()->assertSee('10 dozen 4 pc');
        $this->get(route('lots.show', $this->body->lot_id))->assertOk()->assertSee('4 dozen 8 pc ready for neck cutting');
        $this->get(route('cutting.neck.create'))->assertViewHas('form', fn (array $form): bool => $form['available']->first()['pieces'] === '56.000');
        $this->get(route('cutting.neck.edit', $neck->cutting_job_id))->assertOk()->assertViewHas('form', fn (array $form): bool => $form['rows'][0]['pieces'] === '124.000');
        $this->put(route('cutting.neck.update', $neck->cutting_job_id), $data)->assertSessionHasNoErrors();
        $this->assertSame('124.000', $neck->fresh()->pieces);
        $second = $this->neckData(['quantity_mode' => 'pieces', 'quantity_pieces' => '57', 'weight_per_dozen' => '']);
        unset($second['rows'][0]['dozens']);
        $this->post(route('cutting.neck.store'), $second)->assertSessionHasErrors('rows.0.dozens');
        $second['rows'][0]['quantity_pieces'] = '56';
        $this->post(route('cutting.neck.store'), $second)->assertSessionHasNoErrors();
        $this->get(route('cutting.neck.create'))->assertViewHas('form', fn (array $form): bool => $form['available']->isEmpty());
        $this->delete(route('cutting.neck.destroy', $neck->cutting_job_id))->assertSessionHasNoErrors();
        $this->get(route('cutting.neck.create'))->assertViewHas('form', fn (array $form): bool => $form['available']->first()['pieces'] === '124.000');
    }

    public function test_single_piece_allocations_exhaust_one_dozen_without_rounding_drift(): void
    {
        $this->body->update(['dozens' => '1', 'cut_weight_kg' => '1']);
        $data = $this->neckData(['quantity_mode' => 'pieces', 'quantity_pieces' => '1', 'weight_per_dozen' => '0.12']);
        unset($data['rows'][0]['dozens']);
        for ($piece = 0; $piece < 12; $piece++) {
            $this->post(route('cutting.neck.store'), $data)->assertSessionHasNoErrors();
            if ($piece === 0) {
                $this->get(route('staffs.index'))->assertOk()->assertViewHas('lifetimePieces', fn (array $pieces): bool => $pieces[$this->staff->id] === '13.000');
            }
        }
        $rows = CuttingJobRoll::where('cutting_type', 'neck')->get();
        $totals = CuttingTotals::sum($rows);
        $this->assertSame('12.000', $totals['pieces']);
        $this->assertSame('1.000', $totals['dozens']);
        $this->assertSame('1.000', $totals['roll_weight_kg']);
        $this->assertSame('0.120', $totals['cut_weight_kg']);
        $this->post(route('cutting.neck.store'), $data)->assertSessionHasErrors('rows.0.dozens');
        $this->get(route('cutting.neck.create'))->assertViewHas('form', fn (array $form): bool => $form['available']->isEmpty());
        $productivity = app(StaffProductivity::class);
        $entries = $productivity->entries($this->staff, '2026-10-22', '2026-10-24');
        $this->assertSame(['dozens' => '1.000', 'pieces' => '12.000'], $productivity->totals($entries)['cutting:neck']);
        $this->get(route('lots.show', $this->body->lot_id))->assertOk()->assertSee('0 pc ready for neck cutting');
    }

    public function test_quantity_units_validate_whole_counts_and_ignore_unused_or_forged_fields(): void
    {
        foreach ([
            ['quantity_mode' => 'wrong'],
            ['quantity_mode' => 'dozen', 'quantity_dozens' => '0.5'],
            ['quantity_mode' => 'dozen'],
            ['quantity_mode' => 'pieces', 'quantity_pieces' => '0.5'],
            ['quantity_mode' => 'pieces', 'quantity_pieces' => '-1'],
            ['quantity_mode' => 'pieces'],
            ['quantity_mode' => 'dozen_pieces', 'quantity_dozens' => '1', 'quantity_pieces' => '12'],
            ['quantity_mode' => 'dozen_pieces', 'quantity_dozens' => '1'],
            ['quantity_mode' => 'dozen_pieces', 'quantity_dozens' => '0', 'quantity_pieces' => '0'],
        ] as $input) {
            $data = $this->neckData($input);
            unset($data['rows'][0]['dozens']);
            $this->post(route('cutting.neck.store'), $data)->assertSessionHasErrors();
        }
        $data = $this->neckData(['quantity_mode' => 'dozen', 'quantity_dozens' => '1', 'quantity_pieces' => '999', 'pieces' => '999', 'weight_per_dozen' => '']);
        unset($data['rows'][0]['dozens']);
        $this->post(route('cutting.neck.store'), $data)->assertSessionHasNoErrors();
        $neck = CuttingJobRoll::where('cutting_type', 'neck')->firstOrFail();
        $this->assertSame('12.000', $neck->pieces);
        $this->assertSame($neck->roll_weight_kg, $neck->cut_weight_kg);
        $this->assertTrue($neck->weight_is_automatic);
        $this->assertSame('0.00', $neck->waste_percent);
    }

    public function test_automatic_neck_weight_uses_the_exact_selected_piece_allocation(): void
    {
        $this->body->update(['dozens' => '15', 'cut_weight_kg' => '0.900']);
        $data = $this->neckData(['quantity_mode' => 'dozen_pieces', 'quantity_dozens' => '10', 'quantity_pieces' => '4', 'weight_per_dozen' => '']);
        unset($data['rows'][0]['dozens']);
        $this->post(route('cutting.neck.store'), $data)->assertSessionHasNoErrors();
        $neck = CuttingJobRoll::where('cutting_type', 'neck')->firstOrFail();
        $this->assertSame('124.000', $neck->pieces);
        $this->assertSame('0.620', $neck->cut_weight_kg);
        $this->assertSame('0.060000', $neck->weight_per_dozen);
        $this->assertSame('0.00', $neck->waste_percent);
        $this->get(route('cutting.neck.edit', $neck->cutting_job_id))->assertViewHas('form', fn (array $form): bool => $form['rows'][0]['weight_per_dozen'] === '');
        $data['rows'][0]['weight_unit'] = 'lb';
        $this->put(route('cutting.neck.update', $neck->cutting_job_id), $data)->assertSessionHasNoErrors();
        $this->assertSame('0.132277', $neck->fresh()->weight_per_dozen);
        $this->assertSame('0.620', $neck->fresh()->cut_weight_kg);
        $data['rows'][0]['quantity_mode'] = 'pieces';
        $data['rows'][0]['quantity_pieces'] = '1';
        $this->put(route('cutting.neck.update', $neck->cutting_job_id), $data)->assertSessionHasNoErrors();
        $this->assertSame('0.005', $neck->fresh()->cut_weight_kg);
        $this->assertSame('0.00', $neck->fresh()->waste_percent);
    }

    public function test_tiny_automatic_piece_allocations_preserve_the_final_weight_remainder(): void
    {
        $this->body->update(['dozens' => '1', 'cut_weight_kg' => '0.001']);
        $data = $this->neckData(['quantity_mode' => 'pieces', 'quantity_pieces' => '1', 'weight_per_dozen' => '']);
        unset($data['rows'][0]['dozens']);
        for ($piece = 0; $piece < 12; $piece++) {
            $this->post(route('cutting.neck.store'), $data)->assertSessionHasNoErrors();
        }
        $rows = CuttingJobRoll::where('cutting_type', 'neck')->get();
        $totals = CuttingTotals::sum($rows);
        $this->assertSame('0.001', $totals['cut_weight_kg']);
        $this->assertSame('0.001', $totals['roll_weight_kg']);
        $this->assertSame('0.000', $totals['waste_kg']);
        $this->assertSame('12.000', $totals['pieces']);
        $this->assertTrue($rows->every(fn (CuttingJobRoll $row): bool => $row->weight_is_automatic && $row->waste_percent === '0.00'));
    }
}
