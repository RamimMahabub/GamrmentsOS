<?php

namespace App\Rules;

use App\Support\Decimal;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class DecimalInput implements ValidationRule
{
    public function __construct(private int $scale = 3, private bool $positive = false, private string $maximum = '99999999999.999') {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $normalized = Decimal::normalize($value);
            $number = Decimal::of($normalized);
            $places = strlen(explode('.', $normalized)[1] ?? '');
            if ($number->isGreaterThan($this->maximum) || ($this->positive && ! $number->isPositive()) || $places > $this->scale) {
                $fail('The :attribute must be '.($this->positive ? 'greater than zero' : 'zero or positive').', no more than '.$this->maximum.', with at most '.$this->scale.' decimal places.');
            }
        } catch (\InvalidArgumentException $exception) {
            $fail($exception->getMessage());
        }
    }
}
