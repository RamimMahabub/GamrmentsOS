<?php

namespace App\Http\Requests;

use App\Rules\DecimalInput;
use Illuminate\Validation\Rule;

class YarnRequest extends ManufacturingRequest
{
    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'yarn_type' => ['required', 'string', 'max:255'], 'country_of_origin' => ['required', 'string', 'max:255'],
            'price_per_kg' => ['required', new DecimalInput(6, false, '999999999999.999999')], 'price_unit' => ['required', Rule::in(['lb', 'kg'])],
            'bag_size_kg' => ['nullable', new DecimalInput(8, true, '9999999999.99999999')], 'bag_size_unit' => ['nullable', 'required_with:bag_size_kg', Rule::in(['lb', 'kg'])],
            'initial_kg' => [$this->route('yarn') ? 'prohibited' : 'required', new DecimalInput], 'movement_date' => [$this->route('yarn') ? 'nullable' : 'required', 'date_format:Y-m-d']];
    }
}
