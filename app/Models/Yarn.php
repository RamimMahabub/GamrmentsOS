<?php

namespace App\Models;

use App\Support\AuditedModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Yarn extends AuditedModel
{
    protected function casts(): array
    {
        return ['price_per_kg' => 'decimal:6', 'bag_size_kg' => 'decimal:8'];
    }

    public function movements(): HasMany
    {
        return $this->hasMany(YarnStockMovement::class);
    }

    public function jobLinks(): HasMany
    {
        return $this->hasMany(KnittingJobYarn::class);
    }
}
