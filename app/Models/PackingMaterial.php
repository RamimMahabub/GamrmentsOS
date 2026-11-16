<?php

namespace App\Models;

use App\Support\AuditedModel;
use App\Support\Decimal;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackingMaterial extends AuditedModel
{
    protected function casts(): array
    {
        return [
            'cost_per_unit' => 'decimal:6',
            'min_stock_alert' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function movements(): HasMany
    {
        return $this->hasMany(PackingMaterialStockMovement::class);
    }

    public function lots(): BelongsToMany
    {
        return $this->belongsToMany(Lot::class, 'lot_packing_materials');
    }

    public function getAvailableStockAttribute(): string
    {
        return Decimal::scale(Decimal::sum($this->movements->pluck('quantity')->map(fn ($v): string => (string) $v)));
    }
}
