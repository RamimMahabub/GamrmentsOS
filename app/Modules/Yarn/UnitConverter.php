<?php

namespace App\Modules\Yarn;

use App\Support\Decimal as D;
use Brick\Math\RoundingMode;
use InvalidArgumentException;

final class UnitConverter
{
    public const KG_PER_LB = '0.45359237';

    public static function factor(string $unit): string
    {
        return match ($unit) {
            'kg' => '1', 'lb' => self::KG_PER_LB, default => throw new InvalidArgumentException('Unknown unit.')
        };
    }

    public static function toKg(string $value, string $unit): string
    {
        return D::scale(D::of(D::normalize($value))->multipliedBy(self::factor($unit)));
    }

    public static function fromKg(string $value, string $unit, int $scale = 3): string
    {
        $converted = D::of($value)->dividedBy(self::factor($unit), $scale, RoundingMode::HalfUp);
        $exact = D::of($value)->dividedBy(self::factor($unit), 12, RoundingMode::HalfUp);
        $nearestWhole = $exact->toScale(0, RoundingMode::HalfUp);

        if ($exact->minus($nearestWhole)->abs()->isLessThanOrEqualTo('0.001')) {
            return (string) $nearestWhole->toScale($scale);
        }

        $lower = $converted->minus(D::of('0.001'));

        if ($lower->minus($exact)->abs()->isLessThan($converted->minus($exact)->abs())) {
            $converted = $lower;
        }

        return (string) $converted;
    }

    public static function priceToKg(string $value, string $unit): string
    {
        return (string) D::of(D::normalize($value))->dividedBy(self::factor($unit), 6, RoundingMode::HalfUp);
    }

    public static function priceFromKg(string $value, string $unit): string
    {
        return (string) D::of($value)->multipliedBy(self::factor($unit));
    }

    public static function bagsToKg(string $bags, string $bagSizeKg): string
    {
        return D::scale(D::of(D::normalize($bags))->multipliedBy($bagSizeKg));
    }

    public static function bags(string $kg, ?string $bagSizeKg): ?string
    {
        return $bagSizeKg && D::of($bagSizeKg)->isPositive() ? (string) D::of($kg)->dividedBy($bagSizeKg, 2, RoundingMode::HalfUp) : null;
    }
}
