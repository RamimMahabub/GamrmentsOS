<?php

namespace App\Http\Requests;

use App\Rules\DecimalInput;
use App\Support\Decimal;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'rows.*.dozens' => ['nullable', ...($isNeck ? ['required_without:rows.*.quantity_mode'] : ['required_with:rows.*.weight_per_dozen']), new DecimalInput(3, true, '999999999.999')],
            'rows.*.quantity_mode' => $isNeck ? ['nullable', Rule::in(['dozen', 'pieces', 'dozen_pieces'])] : ['exclude'],
            'rows.*.quantity_dozens' => $isNeck ? ['nullable', 'required_if:rows.*.quantity_mode,dozen,dozen_pieces', new DecimalInput(0, false, '999999999')] : ['exclude'],
            'rows.*.quantity_pieces' => $isNeck ? ['nullable', 'required_if:rows.*.quantity_mode,pieces,dozen_pieces', new DecimalInput(0, false, '11999999999')] : ['exclude'],
            'rows.*.weight_per_dozen' => ['nullable', new DecimalInput(6, true, '999999999.999999')],
            'rows.*.weight_unit' => ['required', Rule::in(['kg', 'lb'])], 'rows.*.remaining_fabric' => ['required', new DecimalInput(3, false, '999999999.999')],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            foreach ((array) $this->input('rows', []) as $index => $row) {
                if (is_array($row) && ($row['quantity_mode'] ?? null) === 'dozen_pieces' && ! $validator->errors()->has('rows.'.$index.'.quantity_pieces') && isset($row['quantity_pieces']) && Decimal::of(Decimal::normalize($row['quantity_pieces']))->isGreaterThan(11)) {
                    $validator->errors()->add('rows.'.$index.'.quantity_pieces', 'Enter 0 to 11 extra pieces, or choose Pieces to enter the total piece count.');
                }
            }
        }];
    }
}
