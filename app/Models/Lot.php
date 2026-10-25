<?php

namespace App\Models;

use App\Support\AuditedModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lot extends AuditedModel
{
    protected function casts(): array
    {
        return ['lot_date' => 'date'];
    }

    public function knittingJob(): HasOne
    {
        return $this->hasOne(KnittingJob::class);
    }

    public function cuttingRows(): HasMany
    {
        return $this->hasMany(CuttingJobRoll::class);
    }

    public function dyeingJobs(): HasMany
    {
        return $this->hasMany(DyeingJob::class);
    }

    protected static function booted(): void
    {
        parent::booted();
        static::updating(function (Lot $lot): void {
            if ($lot->isDirty(['lot_number', 'lot_date'])) {
                throw new \LogicException('Lot number and originating date are immutable.');
            }
        });
    }
}
