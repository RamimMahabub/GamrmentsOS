<?php

namespace App\Support;

use App\Models\CuttingJobRoll;
use Brick\Math\RoundingMode;

final class NeckAvailability
{
    /**
     * @param  iterable<CuttingJobRoll>  $neckRows
     * @return array{pieces: string, body_pieces: string, dozens: string, weight_kg: string, body_dozens: string, body_weight_kg: string}
     */
    public function remaining(CuttingJobRoll $body, iterable $neckRows): array
    {
        $remaining = [
            'pieces' => CuttingQuantity::pieces($body),
            'body_pieces' => CuttingQuantity::pieces($body),
            'dozens' => Decimal::scale($body->dozens ?? '0'),
            'weight_kg' => Decimal::scale($body->cut_weight_kg ?? '0'),
            'body_dozens' => Decimal::scale($body->dozens ?? '0'),
            'body_weight_kg' => Decimal::scale($body->cut_weight_kg ?? '0'),
        ];

        foreach ($neckRows as $neck) {
            if ((string) $neck->source_body_row_id !== (string) $body->id || $neck->dozens === null) {
                return array_replace($remaining, ['pieces' => '0.000', 'dozens' => '0.000', 'weight_kg' => '0.000']);
            }

            $remaining['pieces'] = Decimal::scale(Decimal::of($remaining['pieces'])->minus(CuttingQuantity::pieces($neck)));
            $remaining['dozens'] = CuttingQuantity::dozens($remaining['pieces']);
            $remaining['weight_kg'] = Decimal::scale(Decimal::of($remaining['weight_kg'])->minus($neck->roll_weight_kg));
        }

        foreach (['pieces', 'dozens', 'weight_kg'] as $key) {
            if (Decimal::of($remaining[$key])->isNegative()) {
                $remaining[$key] = '0.000';
            }
        }

        return $remaining;
    }

    /** @param array{pieces: string, body_pieces: string, dozens: string, weight_kg: string, body_dozens: string, body_weight_kg: string} $available */
    public function inputWeight(array $available, string $pieces): string
    {
        if (Decimal::of($pieces)->isEqualTo($available['pieces'])) {
            return $available['weight_kg'];
        }

        $weight = Decimal::of($available['body_weight_kg'])->multipliedBy($pieces)
            ->dividedBy($available['body_pieces'], 3, RoundingMode::HalfUp);

        return Decimal::of($available['weight_kg'])->isLessThan($weight) ? $available['weight_kg'] : (string) $weight;
    }
}
