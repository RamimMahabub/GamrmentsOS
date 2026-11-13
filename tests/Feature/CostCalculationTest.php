<?php

namespace Tests\Feature;

use App\Models\Color;
use App\Models\Company;
use App\Models\CuttingJob;
use App\Models\CuttingJobRoll;
use App\Models\DyeingColorBlock;
use App\Models\DyeingJob;
use App\Models\DyeingRow;
use App\Models\FactoryExpense;
use App\Models\KnittingDiaRow;
use App\Models\KnittingDiaRowYarn;
use App\Models\KnittingJob;
use App\Models\KnittingJobYarn;
use App\Models\Lot;
use App\Models\PackingJob;
use App\Models\PackingJobRow;
use App\Models\PackingMaterial;
use App\Models\ReturnedRoll;
use App\Models\Setting;
use App\Models\SewingJob;
use App\Models\SewingJobRow;
use App\Models\Staff;
use App\Models\User;
use App\Models\Yarn;
use App\Support\Decimal;
use App\Support\LotCostCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CostCalculationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        Setting::putValue('default_weight_unit', 'kg');
        Setting::putValue('currency_label', 'BDT');
        Setting::putValue('default_knitting_rate_per_kg', '25.00');
        Setting::putValue('default_dyeing_rate_per_kg', '60.00');
        Setting::putValue('cutting_rates', ['body' => '15.00', 'neck' => '5.00', 'rib' => '5.00']);
        Setting::putValue('sewing_rates', [
            'fold' => '8.00', 'lock' => '10.00', 'down' => '8.00', 'neck_join' => '12.00',
            'neck_finishing' => '10.00', 'qc_phase_1' => '5.00', 'iron' => '6.00', 'qc_phase_2' => '5.00',
        ]);
        Setting::putValue('thread_cost_per_dozen', '20.00');
        Setting::putValue('carrying_cost_per_dozen', '10.00');
    }

    public function test_lot_cost_sheet_calculates_all_components_accurately(): void
    {
        // 1. Setup Master Entities
        $yarn = Yarn::create([
            'name' => 'Carded Cotton 30s',
            'yarn_type' => 'Cotton',
            'country_of_origin' => 'India',
            'price_per_kg' => '350.000000',
            'price_unit' => 'kg',
        ]);

        $knitCompany = Company::create([
            'name' => 'Knit Master Ltd',
            'name_key' => 'knit-master',
            'address' => 'Dhaka',
            'is_knitting' => true,
            'is_dyeing' => false,
            'is_active' => true,
            'default_knitting_rate_per_kg' => '25.00',
        ]);

        $dyeCompany = Company::create([
            'name' => 'Rainbow Dyeing',
            'name_key' => 'rainbow-dyeing',
            'address' => 'Gazipur',
            'is_knitting' => false,
            'is_dyeing' => true,
            'is_active' => true,
            'default_dyeing_rate_per_kg' => '65.00',
        ]);

        $staff = Staff::create([
            'name' => 'Rahim Mia',
            'phone' => '+8801700000001',
            'role' => 'cutting_man',
            'is_active' => true,
        ]);

        $lot = Lot::create([
            'lot_number' => 'LOT-COST-001',
            'lot_date' => '2026-11-01',
            'current_stage' => 'packing',
        ]);

        // 2. Knitting: 100 kg yarn issued, 95 kg fabric produced
        $knittingJob = KnittingJob::create([
            'lot_id' => $lot->id,
            'company_id' => $knitCompany->id,
            'entry_date' => '2026-11-01',
            'input_unit' => 'kg',
            'rate_per_kg' => '25.00',
        ]);
        KnittingJobYarn::create(['knitting_job_id' => $knittingJob->id, 'yarn_id' => $yarn->id]);
        $diaRow = KnittingDiaRow::create([
            'knitting_job_id' => $knittingJob->id,
            'dia' => 34,
            'input_kg' => '100.000',
            'waste_percent' => '5.00',
            'output_fabric_kg' => '95.000',
            'sort_order' => 1,
        ]);
        KnittingDiaRowYarn::create([
            'dia_row_id' => $diaRow->id,
            'yarn_id' => $yarn->id,
            'quantity_kg' => '100.000',
        ]);

        // 3. Dyeing: 95 kg returned fabric
        $dyeingJob = DyeingJob::create([
            'lot_id' => $lot->id,
            'company_id' => $dyeCompany->id,
            'entry_date' => '2026-11-03',
            'rate_per_kg' => '65.00',
        ]);
        $color = Color::create(['name' => 'Navy', 'name_key' => 'navy']);
        $block = DyeingColorBlock::create(['dyeing_job_id' => $dyeingJob->id, 'color_id' => $color->id, 'sort_order' => 1]);
        $dRow = DyeingRow::create([
            'color_block_id' => $block->id,
            'dia' => 34,
            'given_fabric_kg' => '95.000',
            'returned_fabric_kg' => '95.000',
        ]);
        $roll = ReturnedRoll::create([
            'dyeing_job_id' => $dyeingJob->id,
            'dyeing_row_id' => $dRow->id,
            'number' => 'R-01',
            'number_key' => 'r-01',
            'weight' => '95.000',
            'unit' => 'kg',
            'weight_kg' => '95.000',
        ]);

        // 4. Cutting: 50 dozen cut
        $cuttingJob = CuttingJob::create([
            'cutting_type' => 'body',
            'cutting_date' => '2026-11-05',
            'staff_id' => $staff->id,
            'rate_per_dozen' => '15.00',
        ]);
        $cutRoll = CuttingJobRoll::create([
            'cutting_job_id' => $cuttingJob->id,
            'roll_id' => $roll->id,
            'cutting_type' => 'body',
            'lot_id' => $lot->id,
            'color' => 'Navy',
            'dia' => 34,
            'roll_weight_kg' => '95.000',
            'dozens' => '50.000',
            'pieces' => 600,
            'cut_weight_kg' => '90.000',
            'cutting_scrap_kg' => '5.000',
            'remaining_fabric_kg' => '0.000',
            'remaining_fabric' => '0',
            'remaining_fabric_unit' => 'kg',
            'waste_kg' => '5.000',
            'weight_per_dozen' => '1.800000',
            'weight_per_dozen_kg' => '1.80000000',
            'weight_unit' => 'kg',
            'waste_percent' => '5.26',
        ]);

        // 5. Sewing: 50 dozen passed QC Phase 2
        $sewingJob = SewingJob::create([
            'sewing_date' => '2026-11-08',
            'staff_id' => $staff->id,
            'current_step' => 'qc_phase_2',
            'rate_per_dozen' => '5.00',
        ]);
        SewingJobRow::create([
            'sewing_job_id' => $sewingJob->id,
            'source_body_row_id' => $cutRoll->id,
            'lot_id' => $lot->id,
            'group_key' => $lot->id.':Navy:34',
            'color' => 'Navy',
            'dia' => 34,
            'fold_dozens' => '50.000',
            'lock_dozens' => '50.000',
            'down_dozens' => '50.000',
            'neck_join_dozens' => '50.000',
            'neck_finishing_dozens' => '50.000',
            'qc_phase_1_dozens' => '50.000',
            'iron_dozens' => '50.000',
            'qc_phase_2_dozens' => '50.000',
        ]);

        // 6. Packing: 50 dozen packed (600 pieces, 100 packets)
        $poly = PackingMaterial::create([
            'name' => 'Single Poly',
            'category' => 'poly_bag',
            'unit' => 'pcs',
            'cost_per_unit' => '1.50',
            'is_active' => true,
        ]);
        $packetMat = PackingMaterial::create([
            'name' => 'Master 6pc Packet Bag',
            'category' => 'packet_material',
            'unit' => 'pcs',
            'cost_per_unit' => '3.00',
            'is_active' => true,
        ]);

        $packingJob = PackingJob::create([
            'packing_date' => '2026-11-10',
            'staff_id' => $staff->id,
        ]);
        PackingJobRow::create([
            'packing_job_id' => $packingJob->id,
            'lot_id' => $lot->id,
            'group_key' => $lot->id.':Navy:34',
            'color' => 'Navy',
            'dia' => 34,
            'dozens' => '50.000',
            'packets' => 100,
            'pieces' => 0,
            'total_pieces' => 600,
        ]);

        // 7. Factory Expenses: $5,000 monthly rent shared
        FactoryExpense::create([
            'category' => 'rent',
            'title' => 'Factory Rent November 2026',
            'amount' => '5000.00',
            'expense_date' => '2026-11-01',
            'period_month' => '2026-11',
            'allocation_method' => 'pro_rata_finished_dozens',
        ]);

        // Compute cost sheet
        $cost = LotCostCalculator::forLot($lot);

        // Verification assertions
        $this->assertSame([], $cost['missing_rates']);
        $this->assertTrue($cost['is_complete']);
        $this->assertEquals(600, $cost['finished_pieces']);
        $this->assertEquals('50.000', $cost['finished_dozens']);

        // Expected costs:
        // Yarn: 100 kg * 350 = 35,000.00
        $this->assertEquals('35000.00', $cost['components']['yarn']['cost']);
        // Knitting: 95 kg * 25 = 2,375.00
        $this->assertEquals('2375.00', $cost['components']['knitting']['cost']);
        // Dyeing: 95 kg * 65 = 6,175.00
        $this->assertEquals('6175.00', $cost['components']['dyeing']['cost']);
        // Cutting: 50 doz * 15 = 750.00
        $this->assertEquals('750.00', $cost['components']['cutting']['cost']);
        // Sewing: 50 doz * (8+10+8+12+10+5+6+5 = 64) = 3,200.00
        $this->assertEquals('3200.00', $cost['components']['sewing']['cost']);
        // Thread: 50 doz * 20 = 1,000.00
        $this->assertEquals('1000.00', $cost['components']['thread']['cost']);
        // Packing: (600 poly * 1.50) + (100 packet * 3.00) = 900 + 300 = 1,200.00
        $this->assertEquals('1200.00', $cost['components']['packing']['cost']);
        // Carrying: 50 doz * 10 = 500.00
        $this->assertEquals('500.00', $cost['components']['carrying']['cost']);
        // Overhead: $5,000
        $this->assertEquals('5000.00', $cost['components']['overhead']['cost']);

        // Grand Total: 35000 + 2375 + 6175 + 750 + 3200 + 1000 + 1200 + 500 + 5000 = 55,200.00
        $this->assertEquals('55200.00', $cost['total_cost']);

        // Cost per finished dozen: 55,200 / 50 = 1,104.00
        $this->assertEquals('1104.00', $cost['cost_per_dozen']);
        // Cost per finished piece: 1,104 / 12 = 92.00
        $this->assertEquals('92.00', $cost['cost_per_piece']);
    }

    public function test_rejects_and_waste_raise_cost_per_finished_dozen(): void
    {
        // Two lots with identical total cost of $12,000
        // Lot A produces 100 good dozen -> Cost per doz = $120.00
        // Lot B loses 20 dozen to rejects (only 80 good dozen finished) -> Cost per doz = $150.00

        $lotGood = Lot::create(['lot_number' => 'LOT-GOOD', 'lot_date' => '2026-11-01', 'current_stage' => 'packing']);
        $lotWaste = Lot::create(['lot_number' => 'LOT-WASTE', 'lot_date' => '2026-11-01', 'current_stage' => 'packing']);

        $staff = Staff::create(['name' => 'S1', 'phone' => '+8801700000002', 'role' => 'cutting_man', 'is_active' => true]);

        // Direct expense of $12,000 assigned to each lot
        FactoryExpense::create([
            'category' => 'other',
            'title' => 'Production Cost Lot Good',
            'amount' => '12000.00',
            'expense_date' => '2026-11-01',
            'period_month' => '2026-11',
            'allocation_method' => 'lot_specific',
            'lot_id' => $lotGood->id,
        ]);

        FactoryExpense::create([
            'category' => 'other',
            'title' => 'Production Cost Lot Waste',
            'amount' => '12000.00',
            'expense_date' => '2026-11-01',
            'period_month' => '2026-11',
            'allocation_method' => 'lot_specific',
            'lot_id' => $lotWaste->id,
        ]);

        // Lot Good: 100 dozen packed
        $jobA = PackingJob::create(['packing_date' => '2026-11-10', 'staff_id' => $staff->id]);
        PackingJobRow::create([
            'packing_job_id' => $jobA->id,
            'lot_id' => $lotGood->id,
            'group_key' => $lotGood->id.':White:34',
            'color' => 'White',
            'dia' => 34,
            'dozens' => '100.000',
            'packets' => 200,
            'pieces' => 0,
            'total_pieces' => 1200,
        ]);

        // Lot Waste: only 80 dozen packed
        $jobB = PackingJob::create(['packing_date' => '2026-11-10', 'staff_id' => $staff->id]);
        PackingJobRow::create([
            'packing_job_id' => $jobB->id,
            'lot_id' => $lotWaste->id,
            'group_key' => $lotWaste->id.':White:34',
            'color' => 'White',
            'dia' => 34,
            'dozens' => '80.000',
            'packets' => 160,
            'pieces' => 0,
            'total_pieces' => 960,
            'rejected_pieces' => 240, // 20 dozen ruined
        ]);

        // Turn off carrying and thread defaults for pure overhead comparison
        Setting::putValue('thread_cost_per_dozen', '0');
        Setting::putValue('carrying_cost_per_dozen', '0');

        $costGood = LotCostCalculator::forLot($lotGood);
        $costWaste = LotCostCalculator::forLot($lotWaste);

        $this->assertEquals('120.00', $costGood['cost_per_dozen']);
        $this->assertEquals('150.00', $costWaste['cost_per_dozen']);
        $this->assertTrue(Decimal::of($costWaste['cost_per_dozen'])->isGreaterThan($costGood['cost_per_dozen']));
    }

    public function test_missing_rates_flagged_as_incomplete(): void
    {
        $lot = Lot::create(['lot_number' => 'LOT-MISSING', 'lot_date' => '2026-11-01', 'current_stage' => 'knitting']);
        $company = Company::create([
            'name' => 'Zero Rate Knitters',
            'name_key' => 'zero-rate',
            'address' => 'Dhaka',
            'is_knitting' => true,
            'is_active' => true,
            'default_knitting_rate_per_kg' => '0',
        ]);
        $knittingJob = KnittingJob::create([
            'lot_id' => $lot->id,
            'company_id' => $company->id,
            'entry_date' => '2026-11-01',
            'input_unit' => 'kg',
            'rate_per_kg' => null,
        ]);
        KnittingDiaRow::create([
            'knitting_job_id' => $knittingJob->id,
            'dia' => 36,
            'input_kg' => '50.000',
            'waste_percent' => '5.00',
            'output_fabric_kg' => '47.500',
            'sort_order' => 1,
        ]);

        Setting::putValue('default_knitting_rate_per_kg', '0');

        $cost = LotCostCalculator::forLot($lot);

        $this->assertFalse($cost['is_complete']);
        $this->assertNotEmpty($cost['missing_rates']);
        $this->assertContains('Knitting rate per kg (Zero Rate Knitters)', $cost['missing_rates']);
    }

    public function test_factory_expense_crud_and_lot_cost_views_render(): void
    {
        $company = Company::create([
            'name' => 'Knit One',
            'name_key' => 'knit-one',
            'address' => 'Dhaka',
            'is_knitting' => true,
            'is_active' => true,
        ]);
        $lot = Lot::create(['lot_number' => 'LOT-VIEW-01', 'lot_date' => '2026-11-01', 'current_stage' => 'knitting']);
        $knittingJob = KnittingJob::create([
            'lot_id' => $lot->id,
            'company_id' => $company->id,
            'entry_date' => '2026-11-01',
            'input_unit' => 'kg',
        ]);
        KnittingDiaRow::create([
            'knitting_job_id' => $knittingJob->id,
            'dia' => 36,
            'input_kg' => '50.000',
            'waste_percent' => '5.00',
            'output_fabric_kg' => '47.500',
            'sort_order' => 1,
        ]);

        // Create expense via HTTP POST
        $response = $this->post(route('expenses.store'), [
            'category' => 'electric_bill',
            'title' => 'November Power Bill',
            'amount' => '12500.50',
            'expense_date' => '2026-11-05',
            'allocation_method' => 'pro_rata_finished_dozens',
            'notes' => 'Voucher #9910',
        ]);

        $response->assertRedirect(route('expenses.index', ['month' => '2026-11']));
        $this->assertDatabaseHas('factory_expenses', [
            'title' => 'November Power Bill',
            'period_month' => '2026-11',
            'category' => 'electric_bill',
        ]);

        // Expenses Index Page
        $this->get(route('expenses.index', ['month' => '2026-11']))
            ->assertOk()
            ->assertSee('November Power Bill')
            ->assertSee('12,500.50');

        // Lot Cost Sheet Page
        $this->get(route('lots.cost-sheet', $lot))
            ->assertOk()
            ->assertSee('LOT-VIEW-01')
            ->assertSee('Component Cost Breakdown');

        // Lot Journey Page includes Cost Summary
        $this->get(route('lots.show', $lot))
            ->assertOk()
            ->assertSee('Cost Per Finished Dozen')
            ->assertSee('View Full Cost Sheet');
    }
}
