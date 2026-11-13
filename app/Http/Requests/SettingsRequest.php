<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class SettingsRequest extends ManufacturingRequest
{
    public function rules(): array
    {
        return [
            'default_weight_unit' => ['required', Rule::in(['lb', 'kg'])],
            'dia_presets' => ['required', 'array', 'min:1', 'max:100'],
            'dia_presets.*' => ['required', 'integer', 'min:1', 'max:1000', 'distinct'],
            'currency_label' => ['required', 'string', 'max:10'],
            'dia_size_mapping' => ['nullable', 'array'],
            'default_knitting_rate_per_kg' => ['nullable', 'numeric', 'min:0'],
            'default_dyeing_rate_per_kg' => ['nullable', 'numeric', 'min:0'],
            'cutting_rates' => ['nullable', 'array'],
            'cutting_rates.*' => ['nullable', 'numeric', 'min:0'],
            'sewing_rates' => ['nullable', 'array'],
            'sewing_rates.*' => ['nullable', 'numeric', 'min:0'],
            'carrying_cost_per_dozen' => ['nullable', 'numeric', 'min:0'],
            'thread_cost_per_dozen' => ['nullable', 'numeric', 'min:0'],
            'underpants_sewing_steps' => ['nullable', 'array'],
        ];
    }
}
