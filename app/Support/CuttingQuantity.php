<?php

namespace App\Support;

use Brick\Math\RoundingMode;

final class CuttingQuantity
{
    public static function pieces(mixed $row): string
    {
        return Decimal::scale(data_get($row, 'pieces') ?? Decimal::of(data_get($row, 'dozens') ?? '0')->multipliedBy(12));
    }

    /** @param array<string, mixed> $row */
    public static function fromInput(array $row): string
    {
        if (! isset($row['quantity_mode'])) {
            return Decimal::scale(Decimal::of(Decimal::normalize($row['dozens'] ?? '0'))->multipliedBy(12));
        }

        $dozens = $row['quantity_mode'] === 'pieces' ? '0' : Decimal::normalize($row['quantity_dozens'] ?? '0');
        $pieces = $row['quantity_mode'] === 'dozen' ? '0' : Decimal::normalize($row['quantity_pieces'] ?? '0');

        return Decimal::scale(Decimal::of($dozens)->multipliedBy(12)->plus($pieces));
    }

    public static function dozens(string $pieces): string
    {
        return (string) Decimal::of($pieces)->dividedBy(12, 3, RoundingMode::HalfUp);
    }

    public static function display(string $pieces): string
    {
        $dozens = Decimal::of($pieces)->dividedBy(12, 0, RoundingMode::Down);
        $remainder = Decimal::of($pieces)->minus($dozens->multipliedBy(12));

        return ($dozens->isPositive() ? $dozens.' dozen' : '').($remainder->isPositive() ? ($dozens->isPositive() ? ' ' : '').Decimal::display((string) $remainder, 3, true).' pc' : ($dozens->isZero() ? '0 pc' : ''));
    }
}
