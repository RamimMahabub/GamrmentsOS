<?php

namespace App\Support;

use App\Models\PackingJob;
use App\Models\PackingMaterial;
use App\Models\PackingMaterialStockMovement;

final class PackingStockLedger
{
    public function balance(PackingMaterial $material): string
    {
        return Decimal::scale(Decimal::sum($material->movements()->pluck('quantity')->map(fn ($v): string => (string) $v)));
    }

    public function write(
        PackingMaterial $material,
        string $type,
        string $quantity,
        string $date,
        ?PackingJob $job = null,
        ?string $unitCost = null,
        ?string $note = null
    ): PackingMaterialStockMovement {
        return PackingMaterialStockMovement::create([
            'packing_material_id' => $material->id,
            'type' => $type,
            'quantity' => $quantity,
            'movement_date' => $date,
            'ref_type' => $job?->getMorphClass(),
            'ref_id' => $job?->id,
            'unit_cost' => $unitCost,
            'note' => $note,
        ]);
    }

    public function reverseJob(PackingJob $job, string $date): void
    {
        $movements = PackingMaterialStockMovement::where('ref_type', $job->getMorphClass())
            ->where('ref_id', $job->id)
            ->get();

        $balances = [];
        foreach ($movements as $movement) {
            $balances[$movement->packing_material_id][] = $movement->quantity;
        }

        foreach ($balances as $id => $values) {
            $balance = Decimal::sum($values);
            if (! Decimal::of($balance)->isZero()) {
                $material = PackingMaterial::withTrashed()->find($id);
                if ($material) {
                    $this->write(
                        $material,
                        'reversal',
                        (string) Decimal::of($balance)->negated(),
                        $date,
                        $job,
                        note: 'Reconciled previous packing allocation'
                    );
                }
            }
        }
    }
}
