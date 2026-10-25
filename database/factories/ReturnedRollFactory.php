<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\DyeingJob;
use App\Models\ReturnedRoll;
use App\Models\Yarn;
use App\Modules\Dyeing\DyeingService;
use App\Modules\Knitting\KnittingService;
use App\Support\Decimal;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ReturnedRoll> */
class ReturnedRollFactory extends Factory
{
    public function definition(): array
    {
        return [
            'dyeing_job_id' => function (): int {
                $company = Company::factory()->create();
                $yarn = Yarn::factory()->withStock()->create();
                $knitting = app(KnittingService::class)->save(['company_id' => $company->id, 'yarn_ids' => [$yarn->id], 'entry_date' => now()->toDateString(), 'input_unit' => 'kg', 'rows' => [['dia' => 32, 'waste_percent' => '0', 'quantities' => [$yarn->id => '100']]]]);

                return app(DyeingService::class)->save(['lot_id' => $knitting->lot_id, 'company_id' => $company->id, 'entry_date' => now()->toDateString(), 'blocks' => [['color' => 'Navy', 'rows' => [['dia' => 32, 'given_fabric_kg' => '100', 'returned_fabric_kg' => '10']]]]])->id;
            },
            'dyeing_row_id' => fn (array $attributes): int => DyeingJob::findOrFail($attributes['dyeing_job_id'])->colorBlocks->first()->rows->first()->id,
            'number' => fake()->unique()->bothify('ROLL-####-????'), 'number_key' => fn (array $attributes): string => mb_strtolower($attributes['number']), 'weight' => '10.000', 'unit' => 'kg', 'weight_kg' => '10.000', 'is_available' => true,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (ReturnedRoll $roll): void {
            $row = $roll->dyeingRow;
            $rolls = $row->rolls ?? [];
            $rolls[] = $roll->only(['number', 'weight', 'unit', 'weight_kg']);
            $row->update(['rolls' => $rolls, 'returned_fabric_kg' => Decimal::scale(Decimal::sum(array_column($rolls, 'weight_kg')))]);
        });
    }
}
