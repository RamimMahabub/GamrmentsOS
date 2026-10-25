<?php

namespace App\Modules\Companies;

use App\Models\Company;
use App\Models\CompanyContact;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CompanyService
{
    public function save(array $data, ?Company $company = null): Company
    {
        return DB::transaction(function () use ($data, $company): Company {
            $company = $company ? Company::lockForUpdate()->findOrFail($company->id) : new Company;
            if ($company->exists && (($company->is_knitting && empty($data['is_knitting']) && $company->knittingJobs()->withTrashed()->exists()) ||
                ($company->is_dyeing && empty($data['is_dyeing']) && $company->dyeingJobs()->withTrashed()->exists()))) {
                throw ValidationException::withMessages(['company' => 'A company type used by a job cannot be removed.']);
            }
            $company->fill(collect($data)->only(['name', 'address', 'is_knitting', 'is_dyeing', 'is_active', 'notes'])->all());
            $company->name_key = mb_strtolower(trim($data['name']));
            $company->save();
            foreach (['manager', 'operator', 'dyeing_master'] as $role) {
                $required = $role === 'dyeing_master' ? $company->is_dyeing : $company->is_knitting;
                $contact = CompanyContact::withTrashed()->firstOrNew(['company_id' => $company->id, 'role' => $role]);
                if ($required) {
                    $contact->fill(['name' => trim($data['contacts'][$role]['name']), 'phone' => preg_replace('/[^\d+]/', '', $data['contacts'][$role]['phone']), 'deleted_at' => null]);
                    $contact->save();
                } elseif ($contact->exists && ! $contact->trashed()) {
                    $contact->delete();
                }
            }

            return $company;
        }, 5);
    }

    public function select(int $id, string $type, ?int $existingId = null): Company
    {
        $company = Company::lockForUpdate()->find($id);
        if (! $company || ! $company->getAttribute('is_'.$type) || (! $company->is_active && $id !== $existingId)) {
            throw ValidationException::withMessages(['company_id' => 'Select an active '.$type.' company.']);
        }

        return $company;
    }

    public function delete(Company $company): void
    {
        DB::transaction(function () use ($company): void {
            $company = Company::lockForUpdate()->findOrFail($company->id);
            if ($company->knittingJobs()->withTrashed()->exists() || $company->dyeingJobs()->withTrashed()->exists()) {
                throw ValidationException::withMessages(['company' => 'This company has job history. Deactivate it instead.']);
            }
            $company->contacts()->get()->each->delete();
            $company->delete();
        }, 5);
    }
}
