<?php

namespace App\Http\Requests;

use App\Models\Color;
use Illuminate\Validation\Rule;

class ColorRequest extends ManufacturingRequest
{
    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255', function ($attribute, $value, $fail): void {
            if (! $this->filled('target_id') && Color::withTrashed()->where('name_key', mb_strtolower(trim($value)))->where('id', '!=', $this->route('color')->id)->exists()) {
                $fail('That color name already exists.');
            }
        }], 'hex' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'], 'target_id' => ['nullable', 'integer', Rule::exists('colors', 'id')->whereNull('deleted_at')]];
    }
}
