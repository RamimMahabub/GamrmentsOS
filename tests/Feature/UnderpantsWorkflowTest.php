<?php

namespace Tests\Feature;

use App\Models\Color;
use App\Models\Company;
use App\Models\Lot;
use App\Models\ReturnedRoll;
use App\Models\Staff;
use App\Models\User;
use App\Support\LotCostCalculator;
use App\Support\PackingService;
use App\Support\SewingService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnderpantsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Color $color;

    protected Staff $staff;

    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->user = User::first() ?? User::factory()->create();
        $this->color = Color::firstOrCreate(['name' => 'Black']);
        $this->staff = Staff::firstOrCreate([
            'name' => 'Test Staff',
        ], [
            'phone' => '01700000000',
            'role' => 'cutter',
            'is_active' => true,
        ]);
        $this->company = Company::first() ?? Company::create([
            'name' => 'Test Partner',
            'name_key' => 'test-partner',
            'company_type' => 'knitting',
        ]);
    }

    public function test_can_render_market_roll_lot_creation_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('fabric.market-lot.create'));

        $response->assertOk()
            ->assertSee('New Underpants Lot from Market Rolls')
            ->assertSee('Boxer')
            ->assertSee('Underwear')
            ->assertSee('Fabric Supplier / Market Source');
    }

    public function test_can_create_underpants_lot_from_market_rolls(): void
    {
        $response = $this->actingAs($this->user)->post(route('fabric.market-lot.store'), [
            'product_type' => 'boxer',
            'lot_date' => now()->toDateString(),
            'supplier_name' => 'Alam Ready Fabrics Ltd',
            'default_purchase_price_per_kg' => '420.50',
            'has_rib' => '1',
            'rolls' => [
                [
                    'number' => 'R-01',
                    'color' => 'Black',
                    'dia' => 32,
                    'weight' => '25.500',
                    'unit' => 'kg',
                    'purchase_price_per_kg' => '420.50',
                ],
                [
                    'number' => 'R-02',
                    'color' => 'Black',
                    'dia' => 34,
                    'weight' => '24.500',
                    'unit' => 'kg',
                    'purchase_price_per_kg' => '420.50',
                ],
            ],
        ]);

        $lot = Lot::where('product_type', 'boxer')->latest('id')->firstOrFail();
        $response->assertRedirect(route('fabric.show', $lot));

        $this->assertEquals('market_purchase', $lot->fabric_source);
        $this->assertTrue((bool) $lot->has_rib);
        $this->assertEquals('cutting', $lot->current_stage);
        $this->assertCount(2, $lot->returnedRolls);

        $roll1 = $lot->returnedRolls->first();
        $this->assertEquals('25.500', $roll1->weight_kg);
        $this->assertEquals('Alam Ready Fabrics Ltd', $roll1->supplier_name);
        $this->assertEquals('420.50', $roll1->purchase_price_per_kg);
        $this->assertTrue((bool) $roll1->is_available);
    }

    public function test_can_create_underpants_lot_from_dyeing_style_blocks(): void
    {
        $response = $this->actingAs($this->user)->post(route('fabric.market-lot.store'), [
            'product_type' => 'underwear',
            'lot_date' => now()->toDateString(),
            'supplier_name' => 'City Fabric Market',
            'default_purchase_price_per_kg' => '450.00',
            'has_rib' => '0',
            'blocks' => [
                [
                    'color' => 'Navy',
                    'rows' => [
                        [
                            'dia' => 32,
                            'rolls' => [
                                ['number' => '32NVY-01', 'weight' => '20.000', 'unit' => 'kg', 'purchase_price_per_kg' => '450.00'],
                                ['number' => '32NVY-02', 'weight' => '21.500', 'unit' => 'kg', 'purchase_price_per_kg' => ''],
                            ],
                        ],
                        [
                            'dia' => 34,
                            'rolls' => [
                                ['number' => '34NVY-01', 'weight' => '22.000', 'unit' => 'kg', 'purchase_price_per_kg' => '460.00'],
                            ],
                        ],
                    ],
                ],
                [
                    'color' => 'White',
                    'rows' => [
                        [
                            'dia' => 32,
                            'rolls' => [
                                ['number' => '32WHT-01', 'weight' => '19.500', 'unit' => 'kg', 'purchase_price_per_kg' => ''],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $lot = Lot::where('product_type', 'underwear')->latest('id')->firstOrFail();
        $response->assertRedirect(route('fabric.show', $lot));

        $this->assertEquals('market_purchase', $lot->fabric_source);
        $this->assertCount(4, $lot->returnedRolls);
        $this->assertEquals(
            ['32NVY-01', '32NVY-02', '34NVY-01', '32WHT-01'],
            $lot->returnedRolls->pluck('number')->all()
        );

        $roll2 = $lot->returnedRolls->firstWhere('number', '32NVY-02');
        $this->assertEquals('450.00', $roll2->purchase_price_per_kg); // Inherited default price
        $roll3 = $lot->returnedRolls->firstWhere('number', '34NVY-01');
        $this->assertEquals('460.00', $roll3->purchase_price_per_kg); // Explicit price
    }

    public function test_underpants_rolls_appear_in_fabric_inventory(): void
    {
        $lot = Lot::create([
            'lot_number' => 'BX-121126',
            'product_type' => 'boxer',
            'fabric_source' => 'market_purchase',
            'lot_date' => now()->toDateString(),
            'current_stage' => 'cutting',
        ]);

        ReturnedRoll::create([
            'lot_id' => $lot->id,
            'number' => '1',
            'number_key' => '1',
            'weight' => '30.000',
            'unit' => 'kg',
            'color_id' => $this->color->id,
            'dia' => 32,
            'weight_kg' => '30.000',
            'supplier_name' => 'Market Vendor',
            'purchase_price_per_kg' => '450.00',
            'is_available' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('fabric.index'));
        $response->assertOk()
            ->assertSee('BX-121126')
            ->assertSee('Boxer')
            ->assertSee('Cut (Boxer)');

        $showResponse = $this->actingAs($this->user)->get(route('fabric.show', $lot));
        $showResponse->assertOk()
            ->assertSee('BX-121126')
            ->assertSee('Itemized Rolls');
    }

    public function test_underpants_cutting_and_sewing_workflow(): void
    {
        $lot = Lot::create([
            'lot_number' => 'UW-121126',
            'product_type' => 'underwear',
            'fabric_source' => 'market_purchase',
            'lot_date' => now()->toDateString(),
            'current_stage' => 'cutting',
        ]);

        $roll = ReturnedRoll::create([
            'lot_id' => $lot->id,
            'number' => '1',
            'number_key' => '1',
            'weight' => '20.000',
            'unit' => 'kg',
            'color_id' => $this->color->id,
            'dia' => 32,
            'weight_kg' => '20.000',
            'supplier_name' => 'Market Vendor',
            'purchase_price_per_kg' => '450.00',
            'is_available' => true,
        ]);

        // 1. Cut the roll using underpants cutting ('cutting' type) via controller
        $cuttingResponse = $this->actingAs($this->user)->post(route('cutting.cutting.store'), [
            'cutting_date' => now()->toDateString(),
            'staff_id' => $this->staff->id,
            'rate_per_dozen' => '25.00',
            'rows' => [
                [
                    'roll_id' => $roll->id,
                    'dozens' => '10',
                    'weight_per_dozen' => '1.8',
                    'weight_unit' => 'kg',
                    'remaining_fabric' => '1',
                    'cutting_scrap' => '1',
                ],
            ],
        ]);
        $cuttingResponse->assertSessionHasNoErrors();

        $this->assertEquals('cutting', $lot->fresh()->current_stage);
        $this->assertDatabaseHas('cutting_job_rolls', [
            'roll_id' => $roll->id,
            'lot_id' => $lot->id,
            'cutting_type' => 'cutting',
        ]);

        // 2. Perform underpants sewing sub-step 1: ol_crotch (Crotch Overlock)
        $groupKey = "{$lot->id}:{$this->color->name}:32";
        $sewingService = app(SewingService::class);

        $sewingJob1 = $sewingService->save([
            'sewing_date' => now()->toDateString(),
            'staff_id' => $this->staff->id,
            'current_step' => 'ol_crotch',
            'rate_per_dozen' => '15.00',
            'rows' => [
                [
                    'group_key' => $groupKey,
                    'ol_crotch_dozens' => 10,
                    'ol_crotch_pieces' => 0,
                    'dozens' => 10,
                    'pieces' => 0,
                ],
            ],
        ]);

        $this->assertEquals('ol_crotch', $sewingJob1->current_step);
        $this->assertCount(1, $sewingJob1->rows);
        $this->assertEquals(120, $sewingJob1->rows->first()->stage_data['total_pieces']);

        // 3. Perform underpants sewing sub-step 2: ol_side (Side Overlock)
        $sewingJob2 = $sewingService->save([
            'sewing_date' => now()->toDateString(),
            'staff_id' => $this->staff->id,
            'current_step' => 'ol_side',
            'rate_per_dozen' => '15.00',
            'rows' => [
                [
                    'group_key' => $groupKey,
                    'ol_side_dozens' => 10,
                    'ol_side_pieces' => 0,
                    'dozens' => 10,
                    'pieces' => 0,
                ],
            ],
        ]);
        $this->assertEquals('ol_side', $sewingJob2->current_step);

        // 4. Perform underpants sewing sub-step 3: lk_front
        $sewingJob3 = $sewingService->save([
            'sewing_date' => now()->toDateString(),
            'staff_id' => $this->staff->id,
            'current_step' => 'lk_front',
            'rate_per_dozen' => '12.00',
            'rows' => [
                [
                    'group_key' => $groupKey,
                    'lk_front_dozens' => 10,
                    'lk_front_pieces' => 0,
                    'dozens' => 10,
                    'pieces' => 0,
                ],
            ],
        ]);
        $this->assertEquals('lk_front', $sewingJob3->current_step);

        // 5. Perform underpants sewing sub-step 4: lk_hem
        $sewingJob4 = $sewingService->save([
            'sewing_date' => now()->toDateString(),
            'staff_id' => $this->staff->id,
            'current_step' => 'lk_hem',
            'rate_per_dozen' => '12.00',
            'rows' => [
                [
                    'group_key' => $groupKey,
                    'lk_hem_dozens' => 10,
                    'lk_hem_pieces' => 0,
                    'dozens' => 10,
                    'pieces' => 0,
                ],
            ],
        ]);
        $this->assertEquals('lk_hem', $sewingJob4->current_step);

        // 6. Perform underpants sewing sub-step 5: el_waist (Waist Elastic Join)
        $sewingJob5 = $sewingService->save([
            'sewing_date' => now()->toDateString(),
            'staff_id' => $this->staff->id,
            'current_step' => 'el_waist',
            'rate_per_dozen' => '18.00',
            'rows' => [
                [
                    'group_key' => $groupKey,
                    'el_waist_dozens' => 10,
                    'el_waist_pieces' => 0,
                    'dozens' => 10,
                    'pieces' => 0,
                ],
            ],
        ]);
        $this->assertEquals('el_waist', $sewingJob5->current_step);

        // 7. Perform QC Phase 1 (118 passed, 2 rejected)
        $sewingQc1 = $sewingService->save([
            'sewing_date' => now()->toDateString(),
            'staff_id' => $this->staff->id,
            'current_step' => 'qc_phase_1',
            'rate_per_dozen' => '8.00',
            'rows' => [
                [
                    'group_key' => $groupKey,
                    'qc_phase_1_dozens' => 9,
                    'qc_phase_1_pieces' => 10, // 9 * 12 + 10 = 118
                    'qc_phase_1_altered_pieces' => 0,
                    'qc_phase_1_rejected_pieces' => 2,
                ],
            ],
        ]);
        $this->assertEquals('qc_phase_1', $sewingQc1->current_step);

        // 8. Perform Iron (118 pieces)
        $sewingIron = $sewingService->save([
            'sewing_date' => now()->toDateString(),
            'staff_id' => $this->staff->id,
            'current_step' => 'iron',
            'rate_per_dozen' => '6.00',
            'rows' => [
                [
                    'group_key' => $groupKey,
                    'iron_dozens' => 9,
                    'iron_pieces' => 10, // 118
                ],
            ],
        ]);
        $this->assertEquals('iron', $sewingIron->current_step);

        // 9. Perform QC Phase 2 (118 passed)
        $sewingQc2 = $sewingService->save([
            'sewing_date' => now()->toDateString(),
            'staff_id' => $this->staff->id,
            'current_step' => 'qc_phase_2',
            'rate_per_dozen' => '8.00',
            'rows' => [
                [
                    'group_key' => $groupKey,
                    'qc_phase_2_dozens' => 9,
                    'qc_phase_2_pieces' => 10,
                    'qc_phase_2_altered_pieces' => 0,
                    'qc_phase_2_rejected_pieces' => 0,
                ],
            ],
        ]);
        $this->assertEquals('qc_phase_2', $sewingQc2->current_step);

        // 10. Packing Service available batches check
        $packingService = app(PackingService::class);
        $availableBatches = $packingService->getAvailableBatches();
        $this->assertTrue($availableBatches->contains('group_key', $groupKey));
        $batch = $availableBatches->firstWhere('group_key', $groupKey);
        $this->assertEquals(118, $batch['total_pieces']);

        // 11. Cost Calculation check
        $cost = LotCostCalculator::forLot($lot);
        $this->assertArrayHasKey('market_fabric', $cost['components']);
        // 20 kg * 450 = 9000
        $this->assertEquals('9000.00', $cost['components']['market_fabric']['cost']);
        $this->assertArrayHasKey('cutting', $cost['components']);
        $this->assertArrayHasKey('sewing', $cost['components']);
        $this->assertGreaterThan(0, (float) $cost['total_cost']);

        // 12. Check lots.show and lots.cost-sheet views render cleanly
        $showResponse = $this->actingAs($this->user)->get(route('lots.show', $lot));
        $showResponse->assertOk()
            ->assertSee('Market Ready Rolls')
            ->assertSee('Underpants cutting step');

        $costSheetResponse = $this->actingAs($this->user)->get(route('lots.cost-sheet', $lot));
        $costSheetResponse->assertOk()
            ->assertSee('Market Fabric · Ready Rolls');
    }
}
