<?php

namespace App\Models;

use App\Support\AuditedModel;
use App\Support\Decimal;
use App\Support\PackingService;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
            'is_dispatched' => 'boolean',
            'dispatched_at' => 'datetime',
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

    public function packingMaterials(): BelongsToMany
    {
        return $this->belongsToMany(PackingMaterial::class, 'lot_packing_materials');
    }

    public function syncCurrentStage(): string
    {
        if ($this->is_dispatched) {
            $stage = 'dispatched';
        } elseif ($this->packingRows()->exists()) {
            $stage = 'packing';
        } elseif ($this->sewingRows()->exists()) {
            $stage = 'sewing';
        } elseif ($this->cuttingRows()->exists()) {
            $stage = 'cutting';
        } elseif ($this->dyeingJobs()->exists()) {
            $stage = 'dyeing';
        } else {
            $stage = $this->fabric_source === 'market_purchase' ? 'cutting' : 'knitting';
        }

        if ($this->current_stage !== $stage) {
            $this->current_stage = $stage;
            $this->saveQuietly();
        }

        return $stage;
    }

    /**
     * Check if this lot has any rolls with remaining uncut fabric weight for body cutting.
     */
    public function hasRemainingBodyRolls(): bool
    {
        $rolls = ReturnedRoll::with(['cuttingRows' => fn ($q) => $q->whereIn('cutting_type', ['body', 'cutting', 'rib'])])
            ->where('is_available', true)
            ->where(function ($q): void {
                $q->where('lot_id', $this->id)
                    ->orWhereHas('dyeingJob', fn ($dj) => $dj->where('lot_id', $this->id));
            })
            ->get();

        foreach ($rolls as $roll) {
            $used = Decimal::sum($roll->cuttingRows->pluck('roll_weight_kg'));
            if (Decimal::of($roll->weight_kg)->minus($used)->isPositive()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if this lot has finished goods in sewing that are not yet packed.
     */
    public function hasUnpackedFinishedPieces(): bool
    {
        $availableBatches = app(PackingService::class)->getAvailableBatches();

        return $availableBatches->contains(fn (array $batch): bool => (int) ($batch['lot_id'] ?? 0) === (int) $this->id && ($batch['total_pieces'] ?? 0) > 0);
    }

    /**
     * Determine if this lot qualifies for automatic dispatch:
     * 1. Has at least some packed goods recorded (production took place)
     * 2. No remaining uncut fabric rolls for body cutting
     * 3. All finished products from sewing are packed
     */
    public function isEligibleForAutoDispatch(): bool
    {
        if ($this->is_dispatched) {
            return false;
        }

        // Must have reached packing stage
        if (! $this->packingRows()->exists()) {
            return false;
        }

        // Must NOT have any remaining uncut rolls for cutting
        if ($this->hasRemainingBodyRolls()) {
            return false;
        }

        // Must NOT have any unpacked finished pieces waiting in sewing
        if ($this->hasUnpackedFinishedPieces()) {
            return false;
        }

        return true;
    }

    /**
     * Mark this lot as dispatched.
     */
    public function dispatch(?string $notes = null): void
    {
        $this->is_dispatched = true;
        $this->dispatched_at = now();
        if ($notes !== null) {
            $this->dispatch_notes = $notes;
        }
        $this->current_stage = 'dispatched';
        $this->saveQuietly();
    }

    /**
     * Undispatch this lot (revert back to production stage).
     */
    public function undispatch(): void
    {
        $this->is_dispatched = false;
        $this->dispatched_at = null;
        $this->dispatch_notes = null;
        $this->saveQuietly();
        $this->syncCurrentStage();
    }

    /**
     * Check and automatically dispatch eligible lots.
     *
     * @param  iterable<int|string>|int|string  $lotIds
     */
    public static function checkAutoDispatch(iterable|int|string $lotIds): void
    {
        $ids = collect(is_iterable($lotIds) ? $lotIds : [$lotIds])->filter()->unique()->all();

        if (empty($ids)) {
            return;
        }

        foreach (static::whereIn('id', $ids)->get() as $lot) {
            if ($lot->isEligibleForAutoDispatch()) {
                $lot->dispatch('Automatically dispatched: all products packed and all rolls cut.');
            }
        }
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
