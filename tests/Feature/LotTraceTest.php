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
use Tests\Support\ManufacturingFixtures;
use Tests\TestCase;

class LotTraceTest extends TestCase
{
    use ManufacturingFixtures, RefreshDatabase;

    private Staff $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
        $this->staff = Staff::factory()->create();
    }

    public function test_lot_stage_advances_and_renders_full_pipeline_on_lot_trace(): void
    {
        $company = Company::factory()->create();
        $yarn = Yarn::factory()->withStock()->create();

        // 1. Knitting
        $knitting = app(KnittingService::class)->save($this->knittingData($yarn, $company, '100', [32]));
        $lot = $knitting->lot->fresh();
        $this->assertSame('knitting', $lot->current_stage);

        // 2. Dyeing
        $dyeingData = $this->dyeingData($lot->id, $company, '100', 1, null, [32]);
        $dyeingData['blocks'][0]['rows'][0]['rolls'] = [
            ['number' => 'R1', 'weight' => '50', 'unit' => 'kg'],
            ['number' => 'R2', 'weight' => '50', 'unit' => 'kg'],
        ];
        $this->post(route('dyeing-jobs.store'), $dyeingData)->assertSessionHasNoErrors();
        $this->assertSame('dyeing', $lot->fresh()->current_stage);

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
        $this->assertSame('cutting', $lot->fresh()->current_stage);

        // 4. Cutting (Neck)
        $bodyRow = $cuttingJob->rows()->firstOrFail();
        $neckRoll = ReturnedRoll::where('number', 'R2')->firstOrFail();
        $neckJob = $cuttingService->save([
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

        // 5. Sewing (Fold)
        $sewingService = app(SewingService::class);
        $groupKey = "{$lot->id}:{$bodyRow->color}:{$bodyRow->dia}";
        $sewingJob = $sewingService->save([
            'sewing_date' => '2026-11-01',
            'staff_id' => $this->staff->id,
            'current_step' => 'fold',
            'rows' => [
                [
                    'group_key' => $groupKey,
                    'fold_dozens' => '10',
                    'fold_pieces' => '0',
                ],
            ],
        ]);
        $this->assertSame('sewing', $lot->fresh()->current_stage);

        // Verify Lot Trace page renders Sewing badge, journey, and Sewing section
        $response = $this->get(route('lots.show', $lot));
        $response->assertOk();
        $response->assertSee('Sewing');
        $response->assertSee('Open Sewing Module');
        $response->assertSee('#sewing-section');
        $response->assertSee($this->staff->name);
        $response->assertSee('120 pcs');
    }
}
