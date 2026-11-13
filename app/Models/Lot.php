<?php

namespace App\Models;

use App\Support\AuditedModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lot extends AuditedModel
{
    protected $attributes = [
        'product_type' => 'sando',
        'fabric_source' => 'internal_manufacturing',
    ];

    public function isUnderpants(): bool
    {
        return in_array($this->product_type, ['boxer', 'underwear'], true);
    }

    protected function casts(): array
    {
        return [
            'lot_date' => 'date',
            'carrying_cost_per_dozen' => 'decimal:6',
            'thread_cost_per_dozen' => 'decimal:6',
        ];
    }

    public function knittingJob(): HasOne
    {
        return $this->hasOne(KnittingJob::class);
    }

    public function cuttingRows(): HasMany
    {
        return $this->hasMany(CuttingJobRoll::class);
    }

    public function sewingRows(): HasMany
    {
        return $this->hasMany(SewingJobRow::class);
    }

    public function packingRows(): HasMany
    {
        return $this->hasMany(PackingJobRow::class);
    }

    public function dyeingJobs(): HasMany
    {
        return $this->hasMany(DyeingJob::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(FactoryExpense::class);
    }

    public function returnedRolls(): HasMany
    {
        return $this->hasMany(ReturnedRoll::class);
    }

    public function syncCurrentStage(): string
    {
        $stage = $this->fabric_source === 'market_purchase' ? 'cutting' : 'knitting';

        if ($this->packingRows()->exists()) {
            $stage = 'packing';
        } elseif ($this->sewingRows()->exists()) {
            $stage = 'sewing';
        } elseif ($this->cuttingRows()->exists()) {
            $stage = 'cutting';
        } elseif ($this->dyeingJobs()->exists()) {
            $stage = 'dyeing';
        }

        if ($this->current_stage !== $stage) {
            $this->current_stage = $stage;
            $this->saveQuietly();
        }

        return $stage;
    }

    /**
     * @param  iterable<int|string>|int|string  $lotIds
     */
    public static function syncCurrentStageFor(iterable|int|string $lotIds): void
    {
        $ids = collect(is_iterable($lotIds) ? $lotIds : [$lotIds])->filter()->unique()->all();

        if (empty($ids)) {
            return;
        }

        foreach (static::whereIn('id', $ids)->get() as $lot) {
            $lot->syncCurrentStage();
        }
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
