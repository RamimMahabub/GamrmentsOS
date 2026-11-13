<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Lot;
use App\Models\PackingMaterial;
use App\Models\ReturnedRoll;
use App\Models\Staff;
use App\Models\User;
use App\Models\Yarn;
use App\Modules\Knitting\KnittingService;
use App\Support\CuttingService;
use App\Support\PackingService;
use App\Support\PackingStockLedger;
use App\Support\SewingService;
use App\Support\StaffProductivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Support\ManufacturingFixtures;
use Tests\TestCase;

class PackingWorkflowTest extends TestCase
{
    use ManufacturingFixtures, RefreshDatabase;

    private User $user;

    private Staff $staff;

    private string $groupKey;

    private int $lotId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->staff = Staff::factory()->create(['is_active' => true]);

        $company = Company::factory()->create();
        $yarn = Yarn::factory()->withStock()->create();

        // 1. Knitting (DIA 32)
        $knitting = app(KnittingService::class)->save($this->knittingData($yarn, $company, '100', [32]));
        $lot = $knitting->lot->fresh();
        $this->lotId = $lot->id;

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

        // 5. Sewing all stations up through Iron and QC Phase 2
        $sewingService = app(SewingService::class);

        // Fold
        $sewingService->save([
            'sewing_date' => '2026-11-01',
            'staff_id' => $this->staff->id,
            'current_step' => 'fold',
            'rows' => [['group_key' => $this->groupKey, 'fold_dozens' => 10, 'fold_pieces' => 0]],
        ]);
        // Lock
        $sewingService->save([
            'sewing_date' => '2026-11-01',
            'staff_id' => $this->staff->id,
            'current_step' => 'lock',
            'rows' => [['group_key' => $this->groupKey, 'lock_dozens' => 10, 'lock_pieces' => 0]],
        ]);
        // Down
        $sewingService->save([
            'sewing_date' => '2026-11-01',
            'staff_id' => $this->staff->id,
            'current_step' => 'down',
            'rows' => [['group_key' => $this->groupKey, 'down_dozens' => 10, 'down_pieces' => 0]],
        ]);
        // Neck Join
        $sewingService->save([
            'sewing_date' => '2026-11-01',
            'staff_id' => $this->staff->id,
            'current_step' => 'neck_join',
            'rows' => [['group_key' => $this->groupKey, 'neck_join_dozens' => 10, 'neck_join_pieces' => 0]],
        ]);
        // Neck Finishing
        $sewingService->save([
            'sewing_date' => '2026-11-01',
            'staff_id' => $this->staff->id,
            'current_step' => 'neck_finishing',
            'rows' => [['group_key' => $this->groupKey, 'neck_finishing_dozens' => 10, 'neck_finishing_pieces' => 0, 'rejected_pieces' => 0]],
        ]);
        // QC Phase 1 (114 pcs passed)
        $sewingService->save([
            'sewing_date' => '2026-11-02',
            'staff_id' => $this->staff->id,
            'current_step' => 'qc_phase_1',
            'rows' => [['group_key' => $this->groupKey, 'qc_phase_1_dozens' => 9, 'qc_phase_1_pieces' => 6, 'qc_phase_1_altered_pieces' => 0, 'qc_phase_1_rejected_pieces' => 6]],
        ]);
        // Iron (114 pcs ironed)
        $sewingService->save([
            'sewing_date' => '2026-11-03',
            'staff_id' => $this->staff->id,
            'current_step' => 'iron',
            'rows' => [['group_key' => $this->groupKey, 'iron_dozens' => 9, 'iron_pieces' => 6]],
        ]);
        // QC Phase 2 (110 pcs passed final inspection, 2 rejected, 2 altered)
        $sewingService->save([
            'sewing_date' => '2026-11-04',
            'staff_id' => $this->staff->id,
            'current_step' => 'qc_phase_2',
            'rows' => [['group_key' => $this->groupKey, 'qc_phase_2_dozens' => 9, 'qc_phase_2_pieces' => 2, 'qc_phase_2_altered_pieces' => 2, 'qc_phase_2_rejected_pieces' => 2]],
        ]);
    }

    public function test_packing_workflow_end_to_end(): void
    {
        $ledger = app(PackingStockLedger::class);
        $service = app(PackingService::class);

        // 1. Create packing materials and stock in
        $polyBag = PackingMaterial::create([
            'name' => 'Clear Poly Bag 10x12',
            'category' => 'poly_bag',
            'unit' => 'pcs',
            'min_stock_alert' => 50,
        ]);
        $ledger->write($polyBag, 'purchase', '500', '2026-11-01', note: 'Purchase batch 1');

        $sizeSticker = PackingMaterial::create([
            'name' => 'Size Sticker 5XL',
            'category' => 'size_sticker',
            'size_code' => '5XL',
            'unit' => 'pcs',
            'min_stock_alert' => 50,
        ]);
        $ledger->write($sizeSticker, 'purchase', '500', '2026-11-01', note: 'Purchase batch 1');

        $otherSticker = PackingMaterial::create([
            'name' => 'Barcode Sticker',
            'category' => 'other_sticker',
            'unit' => 'pcs',
            'min_stock_alert' => 50,
        ]);
        $ledger->write($otherSticker, 'purchase', '500', '2026-11-01', note: 'Purchase batch 1');

        $packetBag = PackingMaterial::create([
            'name' => 'Master Packet Bag 6-Piece',
            'category' => 'packet_material',
            'unit' => 'pcs',
            'min_stock_alert' => 20,
        ]);
        $ledger->write($packetBag, 'purchase', '100', '2026-11-01', note: 'Purchase batch 1');

        $this->assertEquals('500.000', $ledger->balance($polyBag));
        $this->assertEquals('500.000', $ledger->balance($sizeSticker));
        $this->assertEquals('500.000', $ledger->balance($otherSticker));
        $this->assertEquals('100.000', $ledger->balance($packetBag));

        // 2. Verify available batches for packing
        $available = $service->getAvailableBatches();
        $this->assertCount(1, $available);
        $batch = $available->first();
        // 9 dozens + 2 pieces = 110 pieces from QC Phase 2
        $this->assertEquals(110, $batch['total_pieces']);
        $this->assertEquals('5XL', $batch['suggested_size']); // DIA 32 is mapped to 5XL

        // 3. Record Packing Run: pack 110 pieces (9 dozens + 2 pieces)
        // 110 pieces = 18 full packets of 6 (108 pcs) + 2 loose pieces = 19 packet bags consumed
        $job = $service->save([
            'packing_date' => '2026-11-05',
            'staff_id' => $this->staff->id,
            'notes' => 'First finished packing batch',
            'rows' => [
                [
                    'group_key' => $this->groupKey,
                    'size' => '5XL',
                    'dozens' => 9,
                    'pieces' => 2,
                    'rejected_pieces' => 0,
                ],
            ],
        ]);

        $this->assertDatabaseHas('packing_jobs', [
            'id' => $job->id,
            'staff_id' => $this->staff->id,
        ]);

        $this->assertDatabaseHas('packing_job_rows', [
            'packing_job_id' => $job->id,
            'group_key' => $this->groupKey,
            'size' => '5XL',
            'dozens' => '9.000',
            'packets' => 18,
            'pieces' => 2,
            'total_pieces' => 110,
        ]);

        // 4. Verify Stock Movements:
        // Poly bags: 500 - 110 = 390
        $this->assertEquals('390.000', $ledger->balance($polyBag));
        // Size stickers: 500 - 110 = 390
        $this->assertEquals('390.000', $ledger->balance($sizeSticker));
        // Other stickers: 500 - 110 = 390
        $this->assertEquals('390.000', $ledger->balance($otherSticker));
        // Packet bags: 100 - 19 = 81
        $this->assertEquals('81.000', $ledger->balance($packetBag));

        // 5. Verify Lot stage updated to 'packing'
        $lot = Lot::find($this->lotId);
        $this->assertEquals('packing', $lot->syncCurrentStage());

        // 6. Verify Staff Productivity
        $productivity = app(StaffProductivity::class);
        $entries = $productivity->entries($this->staff, '2026-11-01', '2026-11-30');
        $packingEntry = $entries->firstWhere('step', 'packing');
        $this->assertNotNull($packingEntry);
        $this->assertEquals('110', $packingEntry['pieces']);
        $this->assertEquals(18, $packingEntry['packets']);

        // 7. Verify Web endpoints render without errors
        $this->get(route('packing-jobs.index'))->assertOk()->assertSee('Packing jobs')->assertSee('110 pcs');
        $this->get(route('packing-jobs.show', $job))->assertOk()->assertSee('18 pkts')->assertSee('Clear Poly Bag');
        $this->get(route('packing-jobs.create'))->assertOk()->assertSee('Batches ready for packing');
        $this->get(route('lots.show', $lot))->assertOk()->assertSee('Packing & Finished Goods', false)->assertSee('18 pkts');
        $this->get(route('packing-materials.index'))->assertOk()->assertSee('Clear Poly Bag 10x12');
    }

    public function test_packing_cannot_exceed_available_capacity(): void
    {
        $service = app(PackingService::class);

        // Only 110 pieces are passed from QC Phase 2
        // Trying to pack 120 pieces should throw ValidationException
        $this->expectException(ValidationException::class);

        $service->save([
            'packing_date' => '2026-11-05',
            'staff_id' => $this->staff->id,
            'rows' => [
                [
                    'group_key' => $this->groupKey,
                    'size' => '5XL',
                    'dozens' => 10,
                    'pieces' => 0,
                ],
            ],
        ]);
    }

    public function test_packing_reversal_on_job_edit_and_delete(): void
    {
        $ledger = app(PackingStockLedger::class);
        $service = app(PackingService::class);

        $polyBag = PackingMaterial::create([
            'name' => 'Clear Poly Bag',
            'category' => 'poly_bag',
            'unit' => 'pcs',
        ]);
        $ledger->write($polyBag, 'purchase', '200', '2026-11-01');

        // Pack 60 pieces (5 dozens = 10 packets)
        $job = $service->save([
            'packing_date' => '2026-11-05',
            'staff_id' => $this->staff->id,
            'rows' => [
                [
                    'group_key' => $this->groupKey,
                    'size' => '5XL',
                    'dozens' => 5,
                    'pieces' => 0,
                ],
            ],
        ]);

        $this->assertEquals('140.000', $ledger->balance($polyBag));

        // Edit job to 48 pieces (4 dozens)
        $service->save([
            'packing_date' => '2026-11-05',
            'staff_id' => $this->staff->id,
            'rows' => [
                [
                    'group_key' => $this->groupKey,
                    'size' => '5XL',
                    'dozens' => 4,
                    'pieces' => 0,
                ],
            ],
        ], $job);

        // 200 - 48 = 152
        $this->assertEquals('152.000', $ledger->balance($polyBag));

        // Delete job: restores full 200 balance
        $service->delete($job);
        $this->assertEquals('200.000', $ledger->balance($polyBag));
        $this->assertSoftDeleted('packing_jobs', ['id' => $job->id]);
    }

    public function test_packing_materials_crud_and_stock_in(): void
    {
        // 1. Create packing material via web route
        $response = $this->post(route('packing-materials.store'), [
            'name' => 'Premium Shirt Box',
            'category' => 'packet_material',
            'unit' => 'pcs',
            'cost_per_unit' => '1.5000',
            'min_stock_alert' => 25,
            'is_active' => true,
            'initial_stock' => 100,
        ]);
        $response->assertRedirect(route('packing-materials.index'));

        $box = PackingMaterial::where('name', 'Premium Shirt Box')->firstOrFail();
        $this->assertEquals('100.000', app(PackingStockLedger::class)->balance($box));

        // 2. Record stock movement via web route
        $this->post(route('packing-materials.stock'), [
            'packing_material_id' => $box->id,
            'type' => 'purchase',
            'quantity' => 150,
            'movement_date' => '2026-11-06',
            'note' => 'Second order delivery',
        ])->assertSessionHasNoErrors();

        $this->assertEquals('250.000', app(PackingStockLedger::class)->balance($box));
    }
}
