<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\Lot;
use App\Models\LotSequence;
use App\Models\ReturnedRoll;
use App\Modules\Dyeing\ColorService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class MarketRollService
{
    public function __construct(
        private ColorService $colorService
    ) {}

    public function generateLotNumber(string $productType, string $date): string
    {
        $prefix = match ($productType) {
            'boxer' => 'BX',
            'underwear' => 'UW',
            default => 'UP',
        };

        $base = $prefix.'-'.CarbonImmutable::parse($date)->format('dmy');

        return DB::transaction(function () use ($base): string {
            DB::table('lot_sequences')->insertOrIgnore([
                'base' => $base,
                'next_suffix' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $sequence = LotSequence::where('base', $base)->lockForUpdate()->firstOrFail();
            do {
                $suffix = $sequence->next_suffix++;
                $number = $base.($suffix === 1 ? '' : '-'.$suffix);
            } while (Lot::withTrashed()->where('lot_number', $number)->exists());

            $sequence->save();

            return $number;
        }, 5);
    }

    public function createLotWithRolls(array $data): Lot
    {
        return DB::transaction(function () use ($data): Lot {
            $lotNumber = ! empty($data['lot_number'])
                ? trim($data['lot_number'])
                : $this->generateLotNumber($data['product_type'], $data['lot_date']);

            $lot = Lot::create([
                'lot_number' => $lotNumber,
                'product_type' => $data['product_type'],
                'fabric_source' => 'market_purchase',
                'has_rib' => ! empty($data['has_rib']),
                'lot_date' => $data['lot_date'],
                'current_stage' => 'cutting',
            ]);

            $factorLbToKg = '0.45359237';

            foreach ($data['rolls'] as $rollData) {
                $color = $this->colorService->findOrCreate($rollData['color']);
                $unit = $rollData['unit'] ?? 'kg';
                $weight = Decimal::scale(Decimal::normalize($rollData['weight']));
                $weightKg = $unit === 'lb'
                    ? Decimal::scale(Decimal::of($weight)->multipliedBy($factorLbToKg))
                    : $weight;

                $pricePerKg = isset($rollData['purchase_price_per_kg']) && trim((string) $rollData['purchase_price_per_kg']) !== ''
                    ? Decimal::scale(Decimal::normalize($rollData['purchase_price_per_kg']), 2)
                    : (isset($data['default_purchase_price_per_kg']) && trim((string) $data['default_purchase_price_per_kg']) !== ''
                        ? Decimal::scale(Decimal::normalize($data['default_purchase_price_per_kg']), 2)
                        : null);

                $number = trim($rollData['number']);
                $key = mb_strtolower($number);

                ReturnedRoll::create([
                    'lot_id' => $lot->id,
                    'color_id' => $color->id,
                    'dia' => (int) $rollData['dia'],
                    'supplier_name' => $data['supplier_name'] ?? null,
                    'purchase_price_per_kg' => $pricePerKg,
                    'number' => $number,
                    'number_key' => $key,
                    'weight' => $weight,
                    'unit' => $unit,
                    'weight_kg' => $weightKg,
                    'is_available' => true,
                ]);
            }

            ActivityLog::create([
                'event' => 'created',
                'subject_type' => $lot->getMorphClass(),
                'subject_id' => $lot->id,
                'properties' => [
                    'source' => 'market_purchase',
                    'rolls_count' => count($data['rolls']),
                    'product_type' => $lot->product_type,
                ],
            ]);

            return $lot;
        }, 5);
    }
}
