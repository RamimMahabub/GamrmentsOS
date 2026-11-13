<?php

namespace App\Http\Requests;

use App\Rules\DecimalInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PackingJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'packing_date' => ['required', 'date_format:Y-m-d'],
            'staff_id' => ['required', 'integer', Rule::exists('staffs', 'id')->where('is_active', true)],
            'notes' => ['nullable', 'string', 'max:10000'],
            'rows' => ['required', 'array', 'min:1', 'max:500'],
            'rows.*' => ['required', 'array'],
            'rows.*.group_key' => ['required', 'string', 'max:100', 'distinct'],
            'rows.*.size' => ['nullable', 'string', 'max:50'],
            'rows.*.dozens' => ['nullable', new DecimalInput(3, false, '999999999.999')],
            'rows.*.pieces' => ['nullable', 'integer', 'between:0,11'],
            'rows.*.rejected_pieces' => ['nullable', 'integer', 'min:0', 'max:999999999'],
        ];
    }
}
