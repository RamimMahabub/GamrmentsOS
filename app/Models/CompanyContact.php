<?php

namespace App\Models;

use App\Support\AuditedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyContact extends AuditedModel
{
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
