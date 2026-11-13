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
        $step = (string) $this->input('current_step', '');

        $rules = [
            'sewing_date' => ['required', 'date_format:Y-m-d'],
            'staff_id' => ['required', 'integer', Rule::exists('staffs', 'id')->where('is_active', true)],
            'current_step' => ['required', 'string', 'max:50'],
            'rate_per_dozen' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'rows' => ['required', 'array', 'min:1', 'max:500'],
            'rows.*' => ['required', 'array'],
            'rows.*.group_key' => ['required', 'string', 'max:100', 'distinct'],
            'rows.*.dozens' => ['nullable', new DecimalInput(3, false, '999999999.999')],
            'rows.*.pieces' => ['nullable', 'integer', 'between:0,11'],
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
            'rows.*.qc_phase_1_dozens' => ['nullable', new DecimalInput(3, false, '999999999.999')],
            'rows.*.qc_phase_1_pieces' => ['nullable', 'integer', 'between:0,11'],
            'rows.*.qc_phase_1_alter_pieces' => ['nullable', 'integer', 'min:0', 'max:999999999'],
            'rows.*.qc_phase_1_altered_pieces' => ['nullable', 'integer', 'min:0', 'max:999999999'],
            'rows.*.qc_phase_1_rejected_pieces' => ['nullable', 'integer', 'min:0', 'max:999999999'],
            'rows.*.iron_dozens' => ['nullable', new DecimalInput(3, false, '999999999.999')],
            'rows.*.iron_pieces' => ['nullable', 'integer', 'between:0,11'],
            'rows.*.qc_phase_2_dozens' => ['nullable', new DecimalInput(3, false, '999999999.999')],
            'rows.*.qc_phase_2_pieces' => ['nullable', 'integer', 'between:0,11'],
            'rows.*.qc_phase_2_alter_pieces' => ['nullable', 'integer', 'min:0', 'max:999999999'],
            'rows.*.qc_phase_2_altered_pieces' => ['nullable', 'integer', 'min:0', 'max:999999999'],
            'rows.*.qc_phase_2_rejected_pieces' => ['nullable', 'integer', 'min:0', 'max:999999999'],
            'rows.*.rejected_pieces' => ['nullable', 'integer', 'min:0', 'max:999999999'],
        ];

        if ($step && ! in_array($step, ['fold', 'lock', 'down', 'neck_join', 'neck_finishing', 'qc_phase_1', 'iron', 'qc_phase_2'], true)) {
            $rules["rows.*.{$step}_dozens"] = ['nullable', new DecimalInput(3, false, '999999999.999')];
            $rules["rows.*.{$step}_pieces"] = ['nullable', 'integer', 'between:0,11'];
        }

        return $rules;
    }
}
