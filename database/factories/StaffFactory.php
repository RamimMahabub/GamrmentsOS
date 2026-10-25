<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StaffFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => fake()->name(), 'phone' => '01712345678', 'role' => 'cutting_man', 'is_active' => true];
    }
}
