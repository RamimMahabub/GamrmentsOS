<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnedRoll extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['weight' => 'decimal:3', 'weight_kg' => 'decimal:3', 'is_available' => 'boolean'];
    }

    public function dyeingJob(): BelongsTo
    {
        return $this->belongsTo(DyeingJob::class)->withTrashed();
    }

    public function dyeingRow(): BelongsTo
    {
        return $this->belongsTo(DyeingRow::class)->withTrashed();
    }

    public function cuttingRows(): HasMany
    {
        return $this->hasMany(CuttingJobRoll::class, 'roll_id');
    }
}
