<?php

namespace App\Support;

use App\Models\CuttingJobRoll;
use Brick\Math\RoundingMode;

final class NeckAvailability
{
    /**
     * @param  iterable<CuttingJobRoll>  $neckRows
     * @return array{dozens: string, weight_kg: string, body_dozens: string, body_weight_kg: string}
     */
    public function remaining(CuttingJobRoll $body, iterable $neckRows): array
    {
        $remaining = [
            'dozens' => Decimal::scale($body->dozens ?? '0'),
            'weight_kg' => Decimal::scale($body->cut_weight_kg ?? '0'),
            'body_dozens' => Decimal::scale($body->dozens ?? '0'),
            'body_weight_kg' => Decimal::scale($body->cut_weight_kg ?? '0'),
        ];

        foreach ($neckRows as $neck) {
            if ((string) $neck->source_body_row_id !== (string) $body->id || $neck->dozens === null) {
                return array_replace($remaining, ['dozens' => '0.000', 'weight_kg' => '0.000']);
            }

            $remaining['dozens'] = Decimal::scale(Decimal::of($remaining['dozens'])->minus($neck->dozens));
            $remaining['weight_kg'] = Decimal::scale(Decimal::of($remaining['weight_kg'])->minus($neck->roll_weight_kg));
        }

        foreach (['dozens', 'weight_kg'] as $key) {
            if (Decimal::of($remaining[$key])->isNegative()) {
                $remaining[$key] = '0.000';
            }
        }

        return $remaining;
    }

    /** @param array{dozens: string, weight_kg: string, body_dozens: string, body_weight_kg: string} $available */
    public function inputWeight(array $available, string $dozens): string
    {
        if (Decimal::of($dozens)->isEqualTo($available['dozens'])) {
            return $available['weight_kg'];
        }

        $weight = Decimal::of($available['body_weight_kg'])->multipliedBy($dozens)
            ->dividedBy($available['body_dozens'], 3, RoundingMode::HalfUp);

        return Decimal::of($available['weight_kg'])->isLessThan($weight) ? $available['weight_kg'] : (string) $weight;
    }
}
