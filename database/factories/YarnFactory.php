<?php

namespace Database\Factories;

use App\Models\Yarn;
use App\Modules\Yarn\StockLedger;
use Illuminate\Database\Eloquent\Factories\Factory;

class YarnFactory extends Factory
{
    protected $model = Yarn::class;

    public function definition(): array
    {
        return ['name' => fake()->unique()->word(), 'yarn_type' => '34 Single', 'country_of_origin' => 'India', 'price_per_kg' => '4.409245', 'price_unit' => 'lb', 'bag_size_kg' => '45.35923700', 'bag_size_unit' => 'lb'];
    }

    public function withStock(string $kg = '1000.000'): static
    {
        return $this->afterCreating(fn (Yarn $yarn) => app(StockLedger::class)->write($yarn, 'purchase', $kg, '2026-05-01'));
    }
}
