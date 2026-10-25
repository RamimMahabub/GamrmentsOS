<?php

namespace App\Http\Requests;

class StaffRequest extends ManufacturingRequest
{
    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'phone' => ['required', 'string', 'max:32', 'regex:/^\+?[\d\s().-]{6,31}$/'], 'address' => ['nullable', 'string', 'max:5000'], 'role' => ['required', 'string', 'max:64', 'regex:/^[a-z][a-z0-9_]*$/'], 'is_active' => ['required', 'boolean']];
    }
}
