<?php

namespace App\Models;

use App\Support\AuditedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DyeingJob extends AuditedModel
{
    protected function casts(): array
    {
        return ['entry_date' => 'date', 'return_date' => 'date'];
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function colorBlocks(): HasMany
    {
        return $this->hasMany(DyeingColorBlock::class)->orderBy('sort_order');
    }
}
