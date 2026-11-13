<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PackingStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'packing_material_id' => ['required', 'integer', Rule::exists('packing_materials', 'id')],
            'type' => ['required', Rule::in(['purchase', 'opening', 'adjustment'])],
            'quantity' => ['required', 'numeric', 'not_in:0'],
            'movement_date' => ['required', 'date_format:Y-m-d'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
