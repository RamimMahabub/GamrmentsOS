<?php

namespace App\Http\Requests;

use App\Rules\DecimalInput;
use Illuminate\Validation\Rule;

class CuttingRequest extends ManufacturingRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        $rows = $this->input('rows', []);
        if (is_array($rows)) {
            foreach ($rows as &$row) {
                if (! is_array($row)) {
                    continue;
                }
                $row['weight_unit'] ??= 'kg';
                if (($row['remaining_fabric'] ?? '') === '') {
                    $row['remaining_fabric'] = '0';
                }
            } $this->merge(['rows' => $rows]);
        }
    }

    public function rules(): array
    {
        $isNeck = $this->route()->defaults['cutting_type'] === 'neck';

        return [
            'cutting_date' => ['required', 'date_format:Y-m-d'], 'staff_id' => ['required', 'integer', Rule::exists('staffs', 'id')->where('is_active', true)], 'notes' => ['nullable', 'string', 'max:10000'],
            'rows' => ['required', 'array', 'min:1', 'max:500'], 'rows.*' => ['required', 'array'],
            'rows.*.roll_id' => $isNeck ? ['exclude'] : ['required', 'integer', 'distinct', 'exists:returned_rolls,id'],
            'rows.*.source_body_row_id' => $isNeck ? ['required', 'integer', 'distinct', 'exists:cutting_job_rolls,id'] : ['exclude'],
            'rows.*.dozens' => $isNeck ? ['exclude'] : ['nullable', 'required_with:rows.*.weight_per_dozen', new DecimalInput(3, true, '999999999.999')],
            'rows.*.weight_per_dozen' => ['nullable', ...($isNeck ? [] : ['required_with:rows.*.dozens']), new DecimalInput(6, true, '999999999.999999')],
            'rows.*.weight_unit' => ['required', Rule::in(['kg', 'lb'])], 'rows.*.remaining_fabric' => ['required', new DecimalInput(3, false, '999999999.999')],
        ];
    }
}
