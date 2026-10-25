<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CuttingJobRoll extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return array_fill_keys(['roll_weight_kg', 'pieces', 'dozens', 'cut_weight_kg', 'remaining_fabric', 'remaining_fabric_kg', 'cutting_scrap_kg', 'waste_kg'], 'decimal:3') + ['weight_is_automatic' => 'boolean', 'weight_per_dozen' => 'decimal:6', 'weight_per_dozen_kg' => 'decimal:8', 'waste_percent' => 'decimal:2'];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(CuttingJob::class, 'cutting_job_id');
    }

    public function roll(): BelongsTo
    {
        return $this->belongsTo(ReturnedRoll::class, 'roll_id');
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class);
    }

    public function sourceBodyRow(): BelongsTo
    {
        return $this->belongsTo(self::class, 'source_body_row_id');
    }

    public function neckRows(): HasMany
    {
        return $this->hasMany(self::class, 'source_body_row_id');
    }
}
