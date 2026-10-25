<?php

namespace App\Models;

use App\Support\AuditedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnittingJob extends AuditedModel
{
    protected function casts(): array
    {
        return ['entry_date' => 'date', 'delivery_date' => 'date'];
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function yarnLinks(): HasMany
    {
        return $this->hasMany(KnittingJobYarn::class);
    }

    public function diaRows(): HasMany
    {
        return $this->hasMany(KnittingDiaRow::class)->orderBy('sort_order');
    }
}
