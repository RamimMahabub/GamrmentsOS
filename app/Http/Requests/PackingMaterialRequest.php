<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PackingMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(['poly_bag', 'size_sticker', 'other_sticker', 'packet_material'])],
            'size_code' => ['nullable', 'string', 'max:50'],
            'unit' => ['required', 'string', 'max:20'],
            'cost_per_unit' => ['nullable', 'numeric', 'min:0'],
            'min_stock_alert' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
