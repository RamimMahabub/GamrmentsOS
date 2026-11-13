<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class FactoryExpenseRequest extends ManufacturingRequest
{
    public function rules(): array
    {
        return [
            'category' => ['required', Rule::in(['rent', 'electric_bill', 'gas_bill', 'water_bill', 'salary', 'maintenance', 'other'])],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
            'allocation_method' => ['required', Rule::in(['pro_rata_finished_dozens', 'lot_specific', 'unallocated'])],
            'lot_id' => ['nullable', 'exists:lots,id', Rule::requiredIf($this->input('allocation_method') === 'lot_specific')],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
