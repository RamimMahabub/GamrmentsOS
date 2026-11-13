<?php

namespace App\Models;

use App\Support\AuditedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PackingMaterialStockMovement extends AuditedModel
{
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_cost' => 'decimal:6',
            'movement_date' => 'date',
        ];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(PackingMaterial::class, 'packing_material_id');
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
