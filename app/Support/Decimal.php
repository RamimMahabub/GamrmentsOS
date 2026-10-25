<?php

namespace App\Support;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use InvalidArgumentException;

final class Decimal
{
    public static function normalize(mixed $value): string
    {
        if (! is_string($value) && ! is_int($value)) {
            throw new InvalidArgumentException('Enter a decimal number as text.');
        }
        $value = trim((string) $value);
        if (! preg_match('/^(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d+)?$/D', $value)) {
            throw new InvalidArgumentException('Use a positive number, for example 2,500.125.');
        }

        return str_replace(',', '', $value);
    }

    public static function of(string|int $value): BigDecimal
    {
        return BigDecimal::of($value);
    }

    public static function scale(string|BigDecimal $value, int $scale = 3): string
    {
        return (string) BigDecimal::of($value)->toScale($scale, RoundingMode::HalfUp);
    }

    public static function sum(iterable $values): string
    {
        $sum = self::of('0');
        foreach ($values as $value) {
            $sum = $sum->plus($value ?? '0');
        }

        return (string) $sum;
    }

    public static function display(string|int|null $value, int $scale = 3, bool $trim = false): string
    {
        if ($value === null) {
            return '—';
        }
        $formatted = self::scale((string) $value, $scale);

        return $trim && $scale > 0 ? rtrim(rtrim($formatted, '0'), '.') : $formatted;
    }
}
