<?php

namespace App\Models;

use App\Support\AuditedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnittingDiaRow extends AuditedModel
{
    protected function casts(): array
    {
        return ['input_kg' => 'decimal:3', 'output_fabric_kg' => 'decimal:3', 'waste_percent' => 'decimal:2'];
    }

    public function yarnLines(): HasMany
    {
        return $this->hasMany(KnittingDiaRowYarn::class, 'dia_row_id');
    }

    public function knittingJob(): BelongsTo
    {
        return $this->belongsTo(KnittingJob::class);
    }
}
