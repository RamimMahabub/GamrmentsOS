<?php

namespace App\Modules\Dyeing;

use App\Models\DyeingRow;
use App\Models\Lot;
use App\Support\Decimal as D;

final class DyedFabricBalance
{
    public function forLot(Lot $lot): array
    {
        if ($lot->fabric_source === 'market_purchase') {
            $rolls = $lot->returnedRolls()->with('color')->get();
            $balances = [];
            foreach ($rolls as $roll) {
                $colorId = $roll->color_id;
                $colorName = $roll->color?->name ?? 'Unknown';
                $dia = $roll->dia;
                $key = $colorId.':'.$dia;
                $balance = $balances[$key] ?? ['color_id' => $colorId, 'color' => $colorName, 'dia' => $dia, 'quantity_kg' => '0.000'];
                $balance['quantity_kg'] = D::scale(D::of($balance['quantity_kg'])->plus($roll->weight_kg ?? '0'));
                $balances[$key] = $balance;
            }

            return array_values($balances);
        }

        $rows = DyeingRow::with('colorBlock.color')->whereHas('colorBlock.dyeingJob', fn ($query) => $query->where('lot_id', $lot->id))->whereNotNull('returned_fabric_kg')->get();
        $balances = [];
        foreach ($rows as $row) {
            $key = $row->colorBlock->color_id.':'.$row->dia;
            $balance = $balances[$key] ?? ['color_id' => $row->colorBlock->color_id, 'color' => $row->colorBlock->color->name, 'dia' => $row->dia, 'quantity_kg' => '0.000'];
            $balance['quantity_kg'] = D::scale(D::of($balance['quantity_kg'])->plus($row->returned_fabric_kg));
            $balances[$key] = $balance;
        }

        return array_values($balances);
    }
}
