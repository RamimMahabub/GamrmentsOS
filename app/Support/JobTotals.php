<?php

namespace App\Support;

use App\Models\KnittingJob;
use App\Models\Lot;

final class JobTotals
{
    public static function pipeline(Lot $lot): array
    {
        $knitting = self::knitting($lot->knittingJob);
        $rows = $lot->dyeingJobs->flatMap(fn ($job) => $job->colorBlocks->flatMap->rows);
        $given = Decimal::scale(Decimal::sum($rows->pluck('given_fabric_kg')));
        $returned = Decimal::scale(Decimal::sum($rows->pluck('returned_fabric_kg')));
        $settled = Decimal::scale(Decimal::sum($rows->filter(fn ($row): bool => $row->returned_fabric_kg !== null)->pluck('given_fabric_kg')));
        $rolls = $rows->flatMap(fn ($row): array => $row->rolls ?? []);
        $rollWeight = Decimal::scale(Decimal::sum($rolls->pluck('weight_kg')));

        return [
            'input' => $knitting['input'], 'output' => $knitting['output'],
            'knitting_loss' => Decimal::scale(Decimal::of($knitting['input'])->minus($knitting['output'])),
            'given' => $given, 'undyed' => Decimal::scale(Decimal::of($knitting['output'])->minus($given)),
            'pending' => Decimal::scale(Decimal::of($given)->minus($settled)),
            'dyeing_loss' => Decimal::scale(Decimal::of($settled)->minus($returned)),
            'returned' => $returned, 'roll_weight' => $rollWeight, 'roll_count' => $rolls->count(),
            'unitemized' => Decimal::scale(Decimal::of($returned)->minus($rollWeight)),
        ];
    }

    public static function knitting(KnittingJob $job): array
    {
        $input = Decimal::sum($job->diaRows->pluck('input_kg'));
        $output = Decimal::sum($job->diaRows->pluck('output_fabric_kg'));

        return ['input' => Decimal::scale($input), 'output' => Decimal::scale($output), 'waste' => WasteCalculator::percent($input, $output)];
    }

    public static function dyeing(iterable $rows): array
    {
        $rows = collect($rows);
        $given = Decimal::sum($rows->pluck('given_fabric_kg'));
        $returned = Decimal::sum($rows->pluck('returned_fabric_kg'));
        $complete = $rows->isNotEmpty() && $rows->every(fn ($row): bool => $row->returned_fabric_kg !== null);

        return ['given' => Decimal::scale($given), 'returned' => $rows->contains(fn ($row): bool => $row->returned_fabric_kg !== null) ? Decimal::scale($returned) : null,
            'waste' => $complete ? WasteCalculator::percent($given, $returned) : null, 'complete' => $complete];
    }
}
