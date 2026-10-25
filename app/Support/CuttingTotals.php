<?php

namespace App\Support;

use Brick\Math\RoundingMode;
use Illuminate\Support\Collection;

final class CuttingTotals
{
    public static function sum(iterable $rows): array
    {
        $rows = collect($rows);
        $pending = $rows->filter(fn ($row): bool => data_get($row, 'cut_weight_kg') === null);
        $rows = $rows->filter(fn ($row): bool => data_get($row, 'cut_weight_kg') !== null);
        $totals = ['pending_count' => $pending->count(), 'pending_weight_kg' => Decimal::scale(Decimal::sum($pending->pluck('roll_weight_kg'))), 'completed_count' => $rows->count()];
        foreach (['dozens', 'roll_weight_kg', 'cut_weight_kg', 'cutting_scrap_kg', 'remaining_fabric_kg', 'waste_kg'] as $field) {
            $totals[$field] = Decimal::scale(Decimal::sum($rows->pluck($field)));
        }
        $totals['pieces'] = Decimal::scale(Decimal::of($totals['dozens'])->multipliedBy(12));
        $totals['waste_percent'] = Decimal::of($totals['roll_weight_kg'])->isZero() ? '0.00' : (string) Decimal::of($totals['waste_kg'])->multipliedBy(100)->dividedBy($totals['roll_weight_kg'], 2, RoundingMode::HalfUp);

        return $totals;
    }

    public static function grouped(iterable $rows): Collection
    {
        return collect($rows)->groupBy(fn ($row): string => $row->lot->lot_number.' / '.$row->color.' / DIA '.$row->dia)->map(fn ($group): array => self::sum($group));
    }
}
