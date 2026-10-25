<?php

namespace App\Support;

use Brick\Math\RoundingMode;
use InvalidArgumentException;

final class WasteCalculator
{
    public static function output(string $inputKg, string $percent): string
    {
        $waste = Decimal::of(Decimal::normalize($percent));
        if ($waste->isGreaterThan('100')) {
            throw new InvalidArgumentException('Waste must be between 0 and 100.');
        }

        return Decimal::scale(Decimal::of($inputKg)->multipliedBy(Decimal::of('1')->minus($waste->dividedByExact('100'))));
    }

    public static function percent(string $given, ?string $returned): ?string
    {
        if ($returned === null || Decimal::of($given)->isZero()) {
            return null;
        }

        return (string) Decimal::of($given)->minus($returned)->multipliedBy('100')->dividedBy($given, 2, RoundingMode::HalfUp);
    }
}
