<?php

namespace Database\Factories;

use App\Models\CuttingJob;
use App\Models\CuttingJobRoll;
use App\Models\ReturnedRoll;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CuttingJobRoll> */
class CuttingJobRollFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cutting_job_id' => CuttingJob::factory(), 'roll_id' => ReturnedRoll::factory(),
            'cutting_type' => fn (array $attributes): string => CuttingJob::findOrFail($attributes['cutting_job_id'])->cutting_type,
            'lot_id' => fn (array $attributes): int => ReturnedRoll::findOrFail($attributes['roll_id'])->dyeingJob->lot_id,
            'color' => 'Navy', 'dia' => 32, 'roll_weight_kg' => '10', 'dozens' => '2.5', 'weight_per_dozen' => '2', 'weight_unit' => 'kg', 'weight_per_dozen_kg' => '2', 'cut_weight_kg' => '5', 'remaining_fabric' => '1', 'remaining_fabric_unit' => 'kg', 'remaining_fabric_kg' => '1', 'cutting_scrap_kg' => '4', 'waste_kg' => '5', 'waste_percent' => '50',
        ];
    }
}
