<?php

namespace App\Models;

use App\Support\AuditedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PackingJob extends AuditedModel
{
    protected function casts(): array
    {
        return [
            'packing_date' => 'date',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function rows(): HasMany
    {
        return $this->hasMany(PackingJobRow::class);
    }

    public function materialMovements(): MorphMany
    {
        return $this->morphMany(PackingMaterialStockMovement::class, 'ref');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
