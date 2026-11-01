<?php

namespace App\Http\Requests;

use App\Rules\DecimalInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SewingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'sewing_date' => ['required', 'date_format:Y-m-d'],
            'staff_id' => ['required', 'integer', Rule::exists('staffs', 'id')->where('is_active', true)],
            'current_step' => ['required', Rule::in(['fold', 'lock', 'down', 'neck_join', 'neck_finishing'])],
            'notes' => ['nullable', 'string', 'max:10000'],
            'rows' => ['required', 'array', 'min:1', 'max:500'],
            'rows.*' => ['required', 'array'],
            'rows.*.group_key' => ['required', 'string', 'max:100', 'distinct'],
            'rows.*.fold_dozens' => ['nullable', new DecimalInput(3, false, '999999999.999')],
            'rows.*.fold_pieces' => ['nullable', 'integer', 'between:0,11'],
            'rows.*.lock_dozens' => ['nullable', new DecimalInput(3, false, '999999999.999')],
            'rows.*.lock_pieces' => ['nullable', 'integer', 'between:0,11'],
            'rows.*.down_dozens' => ['nullable', new DecimalInput(3, false, '999999999.999')],
            'rows.*.down_pieces' => ['nullable', 'integer', 'between:0,11'],
            'rows.*.neck_join_dozens' => ['nullable', new DecimalInput(3, false, '999999999.999')],
            'rows.*.neck_join_pieces' => ['nullable', 'integer', 'between:0,11'],
            'rows.*.neck_finishing_dozens' => ['nullable', new DecimalInput(3, false, '999999999.999')],
            'rows.*.neck_finishing_pieces' => ['nullable', 'integer', 'between:0,11'],
            'rows.*.rejected_pieces' => ['nullable', 'integer', 'min:0', 'max:999999999'],
        ];
    }
}
