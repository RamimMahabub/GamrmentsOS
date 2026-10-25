<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use App\Modules\Companies\CompanyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(Request $request): View
    {
        $companies = Company::with('contacts')->when($request->q, fn ($query) => $query->where('name', 'like', '%'.$request->q.'%'))
            ->when($request->type === 'knitting', fn ($query) => $query->where('is_knitting', true))
            ->when($request->type === 'dyeing', fn ($query) => $query->where('is_dyeing', true))
            ->when($request->type === 'both', fn ($query) => $query->where('is_knitting', true)->where('is_dyeing', true))
            ->orderBy(in_array($request->sort, ['name', 'address', 'is_active'], true) ? $request->sort : 'name', $request->direction === 'desc' ? 'desc' : 'asc')->paginate(25)->withQueryString();

        return view('companies.index', compact('companies'));
    }

    public function create(): View
    {
        return view('companies.form', ['company' => new Company]);
    }

    public function edit(Company $company): View
    {
        return view('companies.form', ['company' => $company->load('contacts')]);
    }

    public function show(Company $company): View
    {
        return view('companies.show', ['company' => $company->load('contacts')]);
    }

    public function store(CompanyRequest $request, CompanyService $service): RedirectResponse
    {
        $company = $service->save($request->validated());

        return to_route('companies.show', $company)->with('status', 'Company created.');
    }

    public function update(CompanyRequest $request, Company $company, CompanyService $service): RedirectResponse
    {
        $service->save($request->validated(), $company);

        return to_route('companies.show', $company)->with('status', 'Company updated.');
    }

    public function destroy(Company $company, CompanyService $service): RedirectResponse
    {
        $service->delete($company);

        return to_route('companies.index')->with('status', 'Company deleted.');
    }
}
