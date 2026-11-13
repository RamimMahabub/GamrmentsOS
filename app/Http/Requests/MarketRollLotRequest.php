<?php

namespace App\Http\Requests;

use App\Rules\DecimalInput;
use Illuminate\Validation\Rule;

class MarketRollLotRequest extends ManufacturingRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('blocks') && is_array($this->input('blocks'))) {
            $rolls = [];
            foreach ($this->input('blocks') as $block) {
                $color = trim((string) ($block['color'] ?? ''));
                foreach ($block['rows'] ?? [] as $row) {
                    $dia = (int) ($row['dia'] ?? 0);
                    foreach ($row['rolls'] ?? [] as $roll) {
                        $weight = trim((string) ($roll['weight'] ?? ''));
                        if ($weight === '') {
                            continue;
                        }
                        $number = trim((string) ($roll['number'] ?? ''));
                        $rolls[] = [
                            'color' => $color,
                            'dia' => $dia,
                            'number' => $number,
                            'weight' => $weight,
                            'unit' => $roll['unit'] ?? 'kg',
                            'purchase_price_per_kg' => isset($roll['purchase_price_per_kg']) && trim((string) $roll['purchase_price_per_kg']) !== '' ? $roll['purchase_price_per_kg'] : null,
                        ];
                    }
                }
            }
            if (! empty($rolls)) {
                $this->merge(['rolls' => $rolls]);
            }
        }

        parent::prepareForValidation();
    }

    public function rules(): array
    {
        return [
            'product_type' => ['required', Rule::in(['boxer', 'underwear'])],
            'lot_number' => ['nullable', 'string', 'max:50', 'unique:lots,lot_number'],
            'lot_date' => ['required', 'date'],
            'has_rib' => ['nullable', 'boolean'],
            'supplier_name' => ['nullable', 'string', 'max:150'],
            'default_purchase_price_per_kg' => ['nullable', new DecimalInput(2, false)],
            'notes' => ['nullable', 'string', 'max:1000'],
            'rolls' => ['required', 'array', 'min:1'],
            'rolls.*.color' => ['required', 'string', 'max:50'],
            'rolls.*.dia' => ['required', 'integer', 'min:1', 'max:1000'],
            'rolls.*.number' => ['required', 'string', 'max:100', 'distinct'],
            'rolls.*.weight' => ['required', new DecimalInput(3, false)],
            'rolls.*.unit' => ['required', Rule::in(['kg', 'lb'])],
            'rolls.*.purchase_price_per_kg' => ['nullable', new DecimalInput(2, false)],
        ];
    }

    public function messages(): array
    {
        return [
            'rolls.required' => 'Please enter at least one fabric roll with a weight.',
            'rolls.*.color.required' => 'Color name is required for all entered rolls.',
            'rolls.*.dia.required' => 'DIA is required for all entered rolls.',
            'rolls.*.number.required' => 'Roll number is required for all entered rolls.',
            'rolls.*.number.distinct' => 'Each roll number must be unique.',
            'rolls.*.weight.required' => 'Weight is required for all entered rolls.',
        ];
    }
}
