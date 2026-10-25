<?php

namespace Database\Factories;

use App\Models\Color;
use Illuminate\Database\Eloquent\Factories\Factory;

class ColorFactory extends Factory
{
    protected $model = Color::class;

    public function definition(): array
    {
        $name = fake()->unique()->safeColorName();

        return ['name' => $name, 'name_key' => mb_strtolower($name), 'hex' => fake()->hexColor()];
    }
}
