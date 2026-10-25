<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class SettingsRequest extends ManufacturingRequest
{
    public function rules(): array
    {
        return ['default_weight_unit' => ['required', Rule::in(['lb', 'kg'])], 'dia_presets' => ['required', 'array', 'min:1', 'max:100'],
            'dia_presets.*' => ['required', 'integer', 'min:1', 'max:1000', 'distinct'], 'currency_label' => ['required', 'string', 'max:10']];
    }
}
