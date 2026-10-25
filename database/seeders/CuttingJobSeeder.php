<?php

namespace Database\Seeders;

use App\Models\CuttingJob;
use App\Models\ReturnedRoll;
use App\Models\Staff;
use App\Support\CuttingService;
use App\Support\Decimal;
use Brick\Math\RoundingMode;
use Illuminate\Database\Seeder;

class CuttingJobSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }
        $staff = Staff::where('is_active', true)->first();
        $roll = ReturnedRoll::where('is_available', true)->whereDoesntHave('cuttingRows', fn ($q) => $q->where('cutting_type', 'body'))->first();
        if (! $staff || ! $roll || CuttingJob::exists()) {
            return;
        }
        app(CuttingService::class)->save(['staff_id' => $staff->id, 'cutting_date' => now()->toDateString(), 'notes' => 'Sample cutting entry', 'rows' => [['roll_id' => $roll->id, 'dozens' => '1', 'weight_per_dozen' => (string) Decimal::of($roll->weight_kg)->dividedBy(2, 3, RoundingMode::Down), 'weight_unit' => 'kg', 'remaining_fabric' => '0']]], 'body');
    }
}
