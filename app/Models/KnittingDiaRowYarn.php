<?php

namespace App\Models;

use App\Support\AuditedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnittingDiaRowYarn extends AuditedModel
{
    protected function casts(): array
    {
        return ['quantity_kg' => 'decimal:3'];
    }

    public function yarn(): BelongsTo
    {
        return $this->belongsTo(Yarn::class)->withTrashed();
    }
}
