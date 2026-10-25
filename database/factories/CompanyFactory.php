<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\CompanyContact;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return ['name' => $name, 'name_key' => mb_strtolower($name), 'address' => fake()->address(), 'is_knitting' => true, 'is_dyeing' => true, 'is_active' => true];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Company $company): void {
            foreach (['manager', 'operator', 'dyeing_master'] as $role) {
                if (($role === 'dyeing_master' && $company->is_dyeing) || ($role !== 'dyeing_master' && $company->is_knitting)) {
                    CompanyContact::create(['company_id' => $company->id, 'role' => $role, 'name' => fake()->name(), 'phone' => '+8801700000001']);
                }
            }
        });
    }
}
