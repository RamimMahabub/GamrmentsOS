<?php

namespace App\Http\Requests;

use App\Rules\DecimalInput;
use Illuminate\Validation\Rule;

class KnittingRequest extends ManufacturingRequest
{
    public function rules(): array
    {
        return ['company_id' => ['required', 'integer'], 'yarn_ids' => ['required', 'array', 'min:1', 'max:30'],
            'yarn_ids.*' => ['required', 'integer', 'distinct'], 'entry_date' => ['required', 'date_format:Y-m-d'],
            'delivery_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:entry_date'], 'input_unit' => ['required', Rule::in(['lb', 'kg'])],
            'notes' => ['nullable', 'string', 'max:10000'], 'rows' => ['required', 'array', 'min:1', 'max:100'],
            'rows.*.dia' => ['required', 'integer', 'min:1', 'max:1000', 'distinct'], 'rows.*.waste_percent' => ['required', new DecimalInput(2, false, '100')],
            'rows.*.quantities' => ['required', 'array'], 'rows.*.quantities.*' => ['required', new DecimalInput]];
    }

    public function after(): array
    {
        return [function ($validator): void {
            if (! is_array($this->input('yarn_ids')) || ! is_array($this->input('rows'))) {
                return;
            }
            $ids = array_map('strval', $this->input('yarn_ids', []));
            foreach ($this->input('rows', []) as $i => $row) {
                if (! is_array($row) || ! is_array($row['quantities'] ?? null)) {
                    continue;
                }
                $keys = array_map('strval', array_keys($row['quantities'] ?? []));
                if (array_diff($keys, $ids) || array_diff($ids, $keys)) {
                    $validator->errors()->add('rows.'.$i.'.quantities', 'Provide a quantity for every selected yarn, and only those yarns.');
                }
            }
        }];
    }
}
