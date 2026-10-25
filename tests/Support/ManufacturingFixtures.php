<?php

namespace Tests\Support;

use App\Models\Company;
use App\Models\Yarn;

trait ManufacturingFixtures
{
    protected function knittingData(Yarn $yarn, Company $company, string $quantity = '226.796', array $dias = [32, 34, 36, 40]): array
    {
        return ['company_id' => $company->id, 'yarn_ids' => [$yarn->id], 'entry_date' => '2026-05-10', 'delivery_date' => null, 'input_unit' => 'lb',
            'rows' => array_map(fn ($dia): array => ['dia' => $dia, 'waste_percent' => '0', 'quantities' => [$yarn->id => $quantity]], $dias)];
    }

    protected function dyeingData(int $lotId, Company $company, string $given = '37.799', int $count = 6, ?string $returned = '35.150', array $dias = [32, 34, 36, 40]): array
    {
        $blocks = [];
        for ($i = 0; $i < $count; $i++) {
            $blocks[] = ['color' => ['Golden', 'Olive', 'Navy', 'Maroon', 'Black', 'Ivory'][$i] ?? 'Color '.$i,
                'rows' => array_map(fn ($dia): array => ['dia' => $dia, 'given_fabric_kg' => $given, 'returned_fabric_kg' => $returned], $dias)];
        }

        return ['lot_id' => $lotId, 'company_id' => $company->id, 'entry_date' => '2026-05-16', 'return_date' => null, 'blocks' => $blocks];
    }
}
