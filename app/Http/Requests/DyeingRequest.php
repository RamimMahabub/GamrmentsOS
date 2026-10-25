<?php

namespace App\Http\Requests;

use App\Rules\DecimalInput;

class DyeingRequest extends ManufacturingRequest
{
    public function rules(): array
    {
        return ['company_id' => ['required', 'integer'], 'lot_id' => ['required', 'integer'], 'entry_date' => ['required', 'date_format:Y-m-d'],
            'return_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:entry_date'], 'notes' => ['nullable', 'string', 'max:10000'],
            'confirm_excess_return' => ['sometimes', 'boolean'], 'blocks' => ['required', 'array', 'min:1', 'max:50'],
            'blocks.*.color' => ['required', 'string', 'max:255'], 'blocks.*.rows' => ['required', 'array', 'min:1', 'max:100'],
            'blocks.*.rows.*.dia' => ['required', 'integer', 'min:1', 'max:1000'], 'blocks.*.rows.*.given_fabric_kg' => ['required', new DecimalInput],
            'blocks.*.rows.*.returned_fabric_kg' => ['nullable', new DecimalInput],
            'blocks.*.rows.*.rolls' => ['sometimes', 'array', 'max:1000'],
            'blocks.*.rows.*.rolls.*.number' => ['required', 'string', 'max:100'],
            'blocks.*.rows.*.rolls.*.weight' => ['required', new DecimalInput(positive: true)],
            'blocks.*.rows.*.rolls.*.unit' => ['required', 'in:kg,lb']];
    }
}
