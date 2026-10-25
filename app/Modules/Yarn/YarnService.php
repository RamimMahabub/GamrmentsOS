<?php

namespace App\Modules\Yarn;

use App\Models\Yarn;
use App\Support\Decimal as D;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class YarnService
{
    public function __construct(private StockLedger $ledger) {}

    public function save(array $data, ?Yarn $yarn = null): Yarn
    {
        return DB::transaction(function () use ($data, $yarn): Yarn {
            $yarn = $yarn ? Yarn::lockForUpdate()->findOrFail($yarn->id) : new Yarn;
            $yarn->fill(collect($data)->only(['name', 'yarn_type', 'country_of_origin', 'price_per_kg', 'price_unit', 'bag_size_kg', 'bag_size_unit'])->all());
            $yarn->save();
            if ($yarn->wasRecentlyCreated && D::of($data['initial_kg'] ?? '0')->isPositive()) {
                $this->ledger->write($yarn, 'purchase', $data['initial_kg'], $data['movement_date'], price: $yarn->price_per_kg, note: 'Opening purchase');
            }

            return $yarn;
        }, 5);
    }

    public function addStock(Yarn $yarn, array $data): void
    {
        DB::transaction(function () use ($yarn, $data): void {
            $yarn = Yarn::lockForUpdate()->findOrFail($yarn->id);
            $this->ledger->write($yarn, 'purchase', $data['quantity_kg'], $data['movement_date'], price: $data['price_per_kg'] ?? null, note: $data['note'] ?? null);
        }, 5);
    }

    public function delete(Yarn $yarn): void
    {
        DB::transaction(function () use ($yarn): void {
            $yarn = Yarn::lockForUpdate()->findOrFail($yarn->id);
            if (! D::of($this->ledger->balance($yarn))->isZero() || $yarn->jobLinks()->exists()) {
                throw ValidationException::withMessages(['yarn' => 'A yarn with stock or knitting history cannot be deleted.']);
            }
            $yarn->delete();
        }, 5);
    }
}
