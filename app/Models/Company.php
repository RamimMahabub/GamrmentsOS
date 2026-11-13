<?php

namespace App\Models;

use App\Support\AuditedModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends AuditedModel
{
    protected function casts(): array
    {
        return [
            'is_knitting' => 'boolean',
            'is_dyeing' => 'boolean',
            'is_active' => 'boolean',
            'default_knitting_rate_per_kg' => 'decimal:6',
            'default_dyeing_rate_per_kg' => 'decimal:6',
        ];
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CompanyContact::class);
    }

    public function knittingJobs(): HasMany
    {
        return $this->hasMany(KnittingJob::class);
    }

    public function dyeingJobs(): HasMany
    {
        return $this->hasMany(DyeingJob::class);
    }
}
