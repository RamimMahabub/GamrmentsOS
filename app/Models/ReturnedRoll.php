<?php

namespace App\Models;

use App\Support\Decimal;
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
        return [
            'weight' => 'decimal:3',
            'weight_kg' => 'decimal:3',
            'is_available' => 'boolean',
            'purchase_price_per_kg' => 'decimal:2',
        ];
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class)->withTrashed();
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
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

    public function getUsedWeightKg(): string
    {
        return Decimal::scale(Decimal::sum(
            $this->cuttingRows
                ->whereIn('cutting_type', ['body', 'cutting', 'rib'])
                ->pluck('roll_weight_kg')
        ));
    }

    public function getRemainingWeightKg(): string
    {
        $remaining = Decimal::of($this->weight_kg)->minus($this->getUsedWeightKg());

        return $remaining->isNegative() ? '0.000' : Decimal::scale($remaining);
    }

    public function isCut(): bool
    {
        return ! Decimal::of($this->getRemainingWeightKg())->isPositive();
    }

    public static function countAvailableForCutting(): int
    {
        return self::with(['cuttingRows' => fn ($q) => $q->whereIn('cutting_type', ['body', 'cutting', 'rib'])])
            ->where('is_available', true)
            ->where(function ($q): void {
                $q->whereHas('dyeingJob')->orWhereNotNull('lot_id');
            })
            ->get()
            ->filter(function (self $roll): bool {
                $used = Decimal::sum($roll->cuttingRows->pluck('roll_weight_kg'));

                return Decimal::of($roll->weight_kg)->minus($used)->isPositive();
            })
            ->count();
    }

    public function getEffectiveLot(): ?Lot
    {
        return $this->lot ?? $this->dyeingJob?->lot;
    }

    public function getEffectiveLotId(): ?int
    {
        return $this->lot_id ?? $this->dyeingJob?->lot_id;
    }

    public function getEffectiveColor(): ?Color
    {
        return $this->color ?? $this->dyeingRow?->colorBlock?->color;
    }

    public function getEffectiveDia(): ?int
    {
        return $this->dia ?? $this->dyeingRow?->dia;
    }
}
