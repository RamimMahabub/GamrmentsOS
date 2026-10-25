<?php

namespace Database\Factories;

use App\Models\CuttingJob;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CuttingJob> */
class CuttingJobFactory extends Factory
{
    public function definition(): array
    {
        return ['cutting_type' => 'body', 'cutting_date' => now()->toDateString(), 'staff_id' => Staff::factory(), 'notes' => null];
    }
}
