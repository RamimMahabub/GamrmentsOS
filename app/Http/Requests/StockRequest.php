<?php

namespace App\Http\Requests;

use App\Rules\DecimalInput;

class StockRequest extends ManufacturingRequest
{
    public function rules(): array
    {
        return ['movement_date' => ['required', 'date_format:Y-m-d'], 'quantity_kg' => ['required', new DecimalInput(3, true)],
            'price_per_kg' => ['nullable', new DecimalInput(6, false, '999999999999.999999')], 'note' => ['nullable', 'string', 'max:10000']];
    }
}
