<?php

namespace App\Http\Requests;

use App\Models\Company;
use Illuminate\Validation\Rule;

class CompanyRequest extends ManufacturingRequest
{
    public function rules(): array
    {
        $id = $this->route('company')?->id;
        $rules = ['name' => ['required', 'string', 'max:255', function ($attribute, $value, $fail) use ($id): void {
            if (Company::withTrashed()->where('name_key', mb_strtolower(trim($value)))->when($id, fn ($query) => $query->where('id', '!=', $id))->exists()) {
                $fail('A company with this name already exists.');
            }
        }], 'address' => ['required', 'string', 'max:5000'], 'is_knitting' => ['required', 'boolean'], 'is_dyeing' => ['required', 'boolean'],
            'is_active' => ['sometimes', 'boolean'], 'notes' => ['nullable', 'string', 'max:10000'], 'contacts' => ['required', 'array']];
        foreach (['manager', 'operator', 'dyeing_master'] as $role) {
            $required = $role === 'dyeing_master' ? $this->boolean('is_dyeing') : $this->boolean('is_knitting');
            $rules['contacts.'.$role.'.name'] = [Rule::requiredIf($required), 'nullable', 'string', 'max:255'];
            $rules['contacts.'.$role.'.phone'] = [Rule::requiredIf($required), 'nullable', 'string', 'max:32', 'regex:/^\+?[\d\s().-]{6,31}$/', function ($attribute, $value, $fail): void {
                if (is_string($value) && strlen(preg_replace('/\D/', '', $value)) < 6) {
                    $fail('Enter a phone number with at least six digits.');
                }
            }];
        }

        return $rules;
    }

    public function after(): array
    {
        return [function ($validator): void {
            if (! $this->boolean('is_knitting') && ! $this->boolean('is_dyeing')) {
                $validator->errors()->add('is_knitting', 'Select at least one company type.');
            }
        }];
    }
}
