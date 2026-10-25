<?php

namespace App\Models;

use App\Support\AuditedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DyeingColorBlock extends AuditedModel
{
    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class)->withTrashed();
    }

    public function rows(): HasMany
    {
        return $this->hasMany(DyeingRow::class, 'color_block_id')->orderBy('dia');
    }

    public function dyeingJob(): BelongsTo
    {
        return $this->belongsTo(DyeingJob::class);
    }
}
