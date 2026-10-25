<?php

namespace App\Models;

use App\Support\AuditedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class YarnStockMovement extends AuditedModel
{
    protected function casts(): array
    {
        return ['quantity_kg' => 'decimal:3', 'price_per_kg' => 'decimal:6', 'movement_date' => 'date'];
    }

    public function yarn(): BelongsTo
    {
        return $this->belongsTo(Yarn::class);
    }

    public function ref(): MorphTo
    {
        return $this->morphTo()->withTrashed();
    }

    protected static function booted(): void
    {
        parent::booted();
        static::updating(function (): void {
            throw new \LogicException('Ledger entries are immutable; write a reversal.');
        });
        static::deleting(function (): void {
            throw new \LogicException('Ledger entries are immutable; write a reversal.');
        });
    }
}
