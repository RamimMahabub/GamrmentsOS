<?php

namespace App\Livewire;

use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use App\Modules\Companies\CompanyService;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CompanyPicker extends Component
{
    #[Locked]
    public string $type = 'knitting';

    public string $selected = '';

    public string $search = '';

    public bool $open = false;

    public string $name = '';

    public string $address = '';

    public array $contacts = ['manager' => ['name' => '', 'phone' => ''], 'operator' => ['name' => '', 'phone' => ''], 'dyeing_master' => ['name' => '', 'phone' => '']];

    public function mount(string $type, string $selected = ''): void
    {
        abort_unless(in_array($type, ['knitting', 'dyeing'], true), 422);
        $this->type = $type;
        $this->selected = $selected;
    }

    public function createCompany(CompanyService $service): void
    {
        abort_unless(auth()->check(), 403);
        $data = ['name' => $this->name, 'address' => $this->address, 'is_knitting' => $this->type === 'knitting', 'is_dyeing' => $this->type === 'dyeing', 'is_active' => true, 'contacts' => $this->contacts];
        $request = CompanyRequest::create('/', 'POST', $data);
        $validated = Validator::make($data, $request->rules())->validate();
        $company = $service->save($validated);
        $this->selected = (string) $company->id;
        $this->search = '';
        $this->open = false;
        $this->name = '';
        $this->address = '';
        $this->resetValidation();
    }

    public function render(): View
    {
        $companies = Company::where('is_'.$this->type, true)->where(function ($query): void {
            $query->where('is_active', true)->orWhere('id', $this->selected);
        })->where(function ($query): void {
            $query->where('name', 'like', '%'.$this->search.'%')->orWhere('id', $this->selected);
        })->orderBy('name')->get();

        return view('livewire.company-picker', compact('companies'));
    }
}
