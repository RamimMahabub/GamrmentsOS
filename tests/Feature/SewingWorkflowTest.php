<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\ReturnedRoll;
use App\Models\Staff;
use App\Models\User;
use App\Models\Yarn;
use App\Modules\Knitting\KnittingService;
use App\Support\CuttingService;
use App\Support\SewingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Support\ManufacturingFixtures;
use Tests\TestCase;

class SewingWorkflowTest extends TestCase
{
    use ManufacturingFixtures, RefreshDatabase;

    private User $user;

    private Staff $staff;

    private string $groupKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->staff = Staff::factory()->create(['is_active' => true]);

        $company = Company::factory()->create();
        $yarn = Yarn::factory()->withStock()->create();

        // 1. Knitting
        $knitting = app(KnittingService::class)->save($this->knittingData($yarn, $company, '100', [32]));
        $lot = $knitting->lot->fresh();

        // 2. Dyeing
        $dyeingData = $this->dyeingData($lot->id, $company, '100', 1, null, [32]);
        $dyeingData['blocks'][0]['rows'][0]['rolls'] = [
            ['number' => 'R1', 'weight' => '50', 'unit' => 'kg'],
            ['number' => 'R2', 'weight' => '50', 'unit' => 'kg'],
        ];
        $this->post(route('dyeing-jobs.store'), $dyeingData)->assertSessionHasNoErrors();

        // 3. Cutting (Body)
        $roll = ReturnedRoll::where('number', 'R1')->firstOrFail();
        $cuttingService = app(CuttingService::class);
        $cuttingJob = $cuttingService->save([
            'cutting_date' => '2026-10-18',
            'staff_id' => $this->staff->id,
            'rows' => [
                [
                    'roll_id' => $roll->id,
                    'dozens' => '20',
                    'pieces' => '240',
                    'weight_per_dozen' => '2',
                    'weight_unit' => 'kg',
                    'remaining_fabric' => '0',
                ],
            ],
        ], 'body');

        // 4. Cutting (Neck)
        $bodyRow = $cuttingJob->rows()->firstOrFail();
        $neckRoll = ReturnedRoll::where('number', 'R2')->firstOrFail();
        $cuttingService->save([
            'cutting_date' => '2026-10-19',
            'staff_id' => $this->staff->id,
            'rows' => [
                [
                    'source_body_row_id' => $bodyRow->id,
                    'roll_id' => $neckRoll->id,
                    'dozens' => '20',
                    'pieces' => '240',
                    'weight_per_dozen' => '0.5',
                    'weight_unit' => 'kg',
                    'remaining_fabric' => '0',
                ],
            ],
        ], 'neck');

        $this->groupKey = "{$lot->id}:{$bodyRow->color}:{$bodyRow->dia}";
    }

    public function test_full_sewing_pipeline_including_qc_phases_and_iron(): void
    {
        $service = app(SewingService::class);

        // 1. FOLD (10 dozens = 120 pcs)
        $foldJob = $service->save([
            'sewing_date' => '2026-11-01',
            'staff_id' => $this->staff->id,
            'current_step' => 'fold',
            'rows' => [
                ['group_key' => $this->groupKey, 'fold_dozens' => 10, 'fold_pieces' => 0],
            ],
        ]);
        $this->assertDatabaseHas('sewing_jobs', ['id' => $foldJob->id, 'current_step' => 'fold']);

        // 2. LOCK (10 dozens = 120 pcs)
        $service->save([
            'sewing_date' => '2026-11-01',
            'staff_id' => $this->staff->id,
            'current_step' => 'lock',
            'rows' => [
                ['group_key' => $this->groupKey, 'lock_dozens' => 10, 'lock_pieces' => 0],
            ],
        ]);

        // 3. DOWN (10 dozens = 120 pcs)
        $service->save([
            'sewing_date' => '2026-11-01',
            'staff_id' => $this->staff->id,
            'current_step' => 'down',
            'rows' => [
                ['group_key' => $this->groupKey, 'down_dozens' => 10, 'down_pieces' => 0],
            ],
        ]);

        // 4. NECK JOIN (10 dozens = 120 pcs)
        $service->save([
            'sewing_date' => '2026-11-01',
            'staff_id' => $this->staff->id,
            'current_step' => 'neck_join',
            'rows' => [
                ['group_key' => $this->groupKey, 'neck_join_dozens' => 10, 'neck_join_pieces' => 0],
            ],
        ]);

        // 5. NECK FINISHING (10 dozens = 120 pcs)
        $service->save([
            'sewing_date' => '2026-11-01',
            'staff_id' => $this->staff->id,
            'current_step' => 'neck_finishing',
            'rows' => [
                ['group_key' => $this->groupKey, 'neck_finishing_dozens' => 10, 'neck_finishing_pieces' => 0, 'rejected_pieces' => 0],
            ],
        ]);

        // 6. QC PHASE 1:
        // Out of 120 incoming pieces:
        // 9 dozens + 2 pcs = 110 passed
        // 6 pcs altered (defects flagged for alteration)
        // 4 pcs rejected
        $qc1Job = $service->save([
            'sewing_date' => '2026-11-02',
            'staff_id' => $this->staff->id,
            'current_step' => 'qc_phase_1',
            'rows' => [
                [
                    'group_key' => $this->groupKey,
                    'qc_phase_1_dozens' => 9,
                    'qc_phase_1_pieces' => 2,
                    'qc_phase_1_altered_pieces' => 6,
                    'qc_phase_1_rejected_pieces' => 4,
                ],
            ],
        ]);

        $this->assertDatabaseHas('sewing_job_rows', [
            'sewing_job_id' => $qc1Job->id,
            'qc_phase_1_dozens' => '9.000',
            'qc_phase_1_pieces' => 2,
            'qc_phase_1_altered_pieces' => 6,
            'qc_phase_1_rejected_pieces' => 4,
            'rejected_pieces' => 4,
        ]);

        // When altered pieces are fixed, the operator simply edits the job to update the quantities.
        // 4 of the 6 altered pieces are repaired and now pass, bringing passed to 114 pcs (9 doz + 6 pcs).
        $service->save([
            'sewing_date' => '2026-11-02',
            'staff_id' => $this->staff->id,
            'current_step' => 'qc_phase_1',
            'rows' => [
                [
                    'group_key' => $this->groupKey,
                    'qc_phase_1_dozens' => 9,
                    'qc_phase_1_pieces' => 6,
                    'qc_phase_1_altered_pieces' => 6,
                    'qc_phase_1_rejected_pieces' => 6,
                ],
            ],
        ], $qc1Job);

        $this->assertDatabaseHas('sewing_job_rows', [
            'sewing_job_id' => $qc1Job->id,
            'qc_phase_1_dozens' => '9.000',
            'qc_phase_1_pieces' => 6,
            'qc_phase_1_altered_pieces' => 6,
            'qc_phase_1_rejected_pieces' => 6,
        ]);

        // 7. IRON: Iron the 114 passed pieces (9 dozens + 6 pcs = 114 pcs)
        $ironJob = $service->save([
            'sewing_date' => '2026-11-03',
            'staff_id' => $this->staff->id,
            'current_step' => 'iron',
            'rows' => [
                [
                    'group_key' => $this->groupKey,
                    'iron_dozens' => 9,
                    'iron_pieces' => 6,
                ],
            ],
        ]);
        $this->assertDatabaseHas('sewing_job_rows', [
            'sewing_job_id' => $ironJob->id,
            'iron_dozens' => '9.000',
            'iron_pieces' => 6,
        ]);

        // 8. QC PHASE 2:
        // Out of 114 ironed pieces:
        // 9 dozens + 2 pcs = 110 passed final inspection
        // 2 pcs altered
        // 2 pcs rejected
        $qc2Job = $service->save([
            'sewing_date' => '2026-11-04',
            'staff_id' => $this->staff->id,
            'current_step' => 'qc_phase_2',
            'rows' => [
                [
                    'group_key' => $this->groupKey,
                    'qc_phase_2_dozens' => 9,
                    'qc_phase_2_pieces' => 2,
                    'qc_phase_2_altered_pieces' => 2,
                    'qc_phase_2_rejected_pieces' => 2,
                ],
            ],
        ]);

        $this->assertDatabaseHas('sewing_job_rows', [
            'sewing_job_id' => $qc2Job->id,
            'qc_phase_2_dozens' => '9.000',
            'qc_phase_2_pieces' => 2,
            'qc_phase_2_altered_pieces' => 2,
            'qc_phase_2_rejected_pieces' => 2,
        ]);

        // Verify web UI renders without errors with step=qc_phase_1 query
        $this->get(route('sewing-jobs.index', ['step' => 'qc_phase_1']))->assertOk()->assertSee('QC PHASE 1');

        $response = $this->get(route('sewing-jobs.index'));
        $response->assertOk();
        $response->assertSee('QC PHASE 1');
        $response->assertSee('IRON');
        $response->assertSee('QC PHASE 2');
        $response->assertSee('Final QC passed');
        $response->assertSee('Total altered');
        $response->assertSee('Total rejected');

        // Verify show page
        $showResponse = $this->get(route('sewing-jobs.show', $qc1Job));
        $showResponse->assertOk();
        $showResponse->assertSee('QC PHASE 1 handoff');
        $showResponse->assertSee('Passed');
        $showResponse->assertSee('Altered');
        $showResponse->assertSee('Rejected');
    }

    public function test_qc_capacity_cannot_exceed_incoming_batches(): void
    {
        $service = app(SewingService::class);

        // Fold 10 dozens = 120 pcs
        $service->save([
            'sewing_date' => '2026-11-01',
            'staff_id' => $this->staff->id,
            'current_step' => 'fold',
            'rows' => [['group_key' => $this->groupKey, 'fold_dozens' => 10, 'fold_pieces' => 0]],
        ]);
        $service->save([
            'sewing_date' => '2026-11-01',
            'staff_id' => $this->staff->id,
            'current_step' => 'lock',
            'rows' => [['group_key' => $this->groupKey, 'lock_dozens' => 10, 'lock_pieces' => 0]],
        ]);
        $service->save([
            'sewing_date' => '2026-11-01',
            'staff_id' => $this->staff->id,
            'current_step' => 'down',
            'rows' => [['group_key' => $this->groupKey, 'down_dozens' => 10, 'down_pieces' => 0]],
        ]);
        $service->save([
            'sewing_date' => '2026-11-01',
            'staff_id' => $this->staff->id,
            'current_step' => 'neck_join',
            'rows' => [['group_key' => $this->groupKey, 'neck_join_dozens' => 10, 'neck_join_pieces' => 0]],
        ]);
        $service->save([
            'sewing_date' => '2026-11-01',
            'staff_id' => $this->staff->id,
            'current_step' => 'neck_finishing',
            'rows' => [['group_key' => $this->groupKey, 'neck_finishing_dozens' => 10, 'neck_finishing_pieces' => 0, 'rejected_pieces' => 0]],
        ]);

        // Attempting to inspect 130 pcs (120 passed + 10 rejected) when only 120 came from Neck Finishing must fail validation
        $this->expectException(ValidationException::class);
        $service->save([
            'sewing_date' => '2026-11-02',
            'staff_id' => $this->staff->id,
            'current_step' => 'qc_phase_1',
            'rows' => [
                [
                    'group_key' => $this->groupKey,
                    'qc_phase_1_dozens' => 10,
                    'qc_phase_1_pieces' => 0, // 120 pcs
                    'qc_phase_1_altered_pieces' => 0,
                    'qc_phase_1_rejected_pieces' => 10, // 10 pcs rejected -> 120 + 10 = 130 pcs > 120 pcs
                ],
            ],
        ]);
    }

    public function test_non_multiple_of_twelve_remaining_at_qc_does_not_throw_rounding_exception(): void
    {
        $service = app(SewingService::class);
        $service->save([
            'sewing_date' => '2026-11-01',
            'staff_id' => $this->staff->id,
            'current_step' => 'fold',
            'rows' => [['group_key' => $this->groupKey, 'fold_dozens' => 10, 'fold_pieces' => 0]],
        ]);
        $service->save([
            'sewing_date' => '2026-11-01',
            'staff_id' => $this->staff->id,
            'current_step' => 'lock',
            'rows' => [['group_key' => $this->groupKey, 'lock_dozens' => 10, 'lock_pieces' => 0]],
        ]);
        $service->save([
            'sewing_date' => '2026-11-01',
            'staff_id' => $this->staff->id,
            'current_step' => 'down',
            'rows' => [['group_key' => $this->groupKey, 'down_dozens' => 10, 'down_pieces' => 0]],
        ]);
        $service->save([
            'sewing_date' => '2026-11-01',
            'staff_id' => $this->staff->id,
            'current_step' => 'neck_join',
            'rows' => [['group_key' => $this->groupKey, 'neck_join_dozens' => 10, 'neck_join_pieces' => 0]],
        ]);
        $service->save([
            'sewing_date' => '2026-11-01',
            'staff_id' => $this->staff->id,
            'current_step' => 'neck_finishing',
            'rows' => [['group_key' => $this->groupKey, 'neck_finishing_dozens' => 10, 'neck_finishing_pieces' => 0, 'rejected_pieces' => 0]],
        ]);

        // QC 1 inspects only 115 pieces, leaving 5 pieces (5 / 12 = 0.416666... non-terminating decimal)
        $service->save([
            'sewing_date' => '2026-11-02',
            'staff_id' => $this->staff->id,
            'current_step' => 'qc_phase_1',
            'rows' => [
                [
                    'group_key' => $this->groupKey,
                    'qc_phase_1_dozens' => 9,
                    'qc_phase_1_pieces' => 7, // 115 pcs
                    'qc_phase_1_alter_pieces' => 0,
                    'qc_phase_1_altered_pieces' => 0,
                    'qc_phase_1_rejected_pieces' => 0,
                ],
            ],
        ]);

        // Accessing the page with 5 pieces remaining must not throw RoundingNecessaryException
        $response = $this->get(route('sewing-jobs.index', ['step' => 'qc_phase_1']));
        $response->assertOk();
        $response->assertSee('\u0022total_pieces\u0022:5', false);
        $response->assertSee('\u0022pieces\u0022:5', false);
        $response->assertSee('\u0022dozens\u0022:0', false);
    }

    public function test_sando_and_underpants_are_strictly_separated_in_sewing_overview_and_stations(): void
    {
        $response = $this->get(route('sewing-jobs.index'));
        $response->assertOk();
        // Verifies view receives separated data
        $response->assertViewHas('sandoOverview');
        $response->assertViewHas('underpantsOverview');
        $response->assertViewHas('sandoKpis');
        $response->assertViewHas('underpantsKpis');
        $response->assertViewHas('sandoStationPanels');
        $response->assertViewHas('underpantsStationPanels');
        // Both workflows have their progress grids and KPI sections separated
        $response->assertSee('CROTCH OVERLOCK');
        $response->assertSee('FOLD');
        $response->assertSee('active: null', false);
    }
}
