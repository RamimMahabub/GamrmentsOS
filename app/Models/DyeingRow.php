<?php

namespace App\Models;

use App\Support\AuditedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DyeingRow extends AuditedModel
{
    protected function casts(): array
    {
        return ['given_fabric_kg' => 'decimal:3', 'returned_fabric_kg' => 'decimal:3', 'waste_percent' => 'decimal:2', 'rolls' => 'array'];
    }

    public function colorBlock(): BelongsTo
    {
        return $this->belongsTo(DyeingColorBlock::class, 'color_block_id');
    }
}
