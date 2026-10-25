<?php

namespace App\Http\Requests;

use App\Support\Decimal;
use Illuminate\Foundation\Http\FormRequest;

abstract class ManufacturingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $data = $this->all();
        array_walk_recursive($data, function (&$value, $key): void {
            if (in_array($key, ['price_per_kg', 'bag_size_kg', 'initial_kg', 'quantity_kg', 'given_fabric_kg', 'returned_fabric_kg', 'waste_percent'], true)) {
                try {
                    $value = Decimal::normalize($value);
                } catch (\InvalidArgumentException) {
                }
            }
        });
        if (isset($data['rows']) && is_array($data['rows'])) {
            foreach ($data['rows'] as &$row) {
                if (! is_array($row) || ! is_array($row['quantities'] ?? null)) {
                    continue;
                }
                foreach ($row['quantities'] ?? [] as $id => $value) {
                    try {
                        $row['quantities'][$id] = Decimal::normalize($value);
                    } catch (\InvalidArgumentException) {
                    }
                }
            }
        }
        $this->merge($data);
    }
}
