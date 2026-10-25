<?php

namespace App\Modules\Yarn;

use App\Models\KnittingJob;
use App\Models\Yarn;
use App\Models\YarnStockMovement;
use App\Support\Decimal as D;

final class StockLedger
{
    public function balance(Yarn $yarn): string
    {
        return D::scale(D::sum($yarn->movements()->pluck('quantity_kg')->map(fn ($value): string => (string) $value)));
    }

    public function write(Yarn $yarn, string $type, string $quantityKg, string $date, ?KnittingJob $job = null, ?string $price = null, ?string $note = null): YarnStockMovement
    {
        return YarnStockMovement::create(['yarn_id' => $yarn->id, 'type' => $type, 'quantity_kg' => $quantityKg, 'movement_date' => $date,
            'ref_type' => $job?->getMorphClass(), 'ref_id' => $job?->id, 'price_per_kg' => $price, 'note' => $note]);
    }

    public function reverseJob(KnittingJob $job, string $date): void
    {
        $balances = [];
        foreach (YarnStockMovement::where('ref_type', $job->getMorphClass())->where('ref_id', $job->id)->get() as $movement) {
            $balances[$movement->yarn_id][] = $movement->quantity_kg;
        }
        foreach ($balances as $id => $values) {
            $balance = D::sum($values);
            if (! D::of($balance)->isZero()) {
                $this->write(Yarn::withTrashed()->findOrFail($id), 'reversal', (string) D::of($balance)->negated(), $date, $job, note: 'Reconciled previous knitting allocation');
            }
        }
    }
}
