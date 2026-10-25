<?php

namespace App\Modules\Knitting;

use App\Models\Lot;
use App\Models\LotSequence;
use App\Models\Yarn;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class LotGenerator
{
    public function base(Yarn $yarn, string $date): string
    {
        $letters = preg_replace('/[^\p{L}]/u', '', $yarn->name);

        return mb_strtoupper(mb_substr($letters, 0, 2)).'-'.CarbonImmutable::parse($date)->format('dmy');
    }

    public function preview(Yarn $yarn, string $date): string
    {
        $base = $this->base($yarn, $date);
        $suffix = 1;
        do {
            $number = $base.($suffix === 1 ? '' : '-'.$suffix);
            $suffix++;
        } while (Lot::withTrashed()->where('lot_number', $number)->exists());

        return $number;
    }

    public function create(Yarn $yarn, string $date): Lot
    {
        return DB::transaction(function () use ($yarn, $date): Lot {
            $base = $this->base($yarn, $date);
            DB::table('lot_sequences')->insertOrIgnore(['base' => $base, 'next_suffix' => 1, 'created_at' => now(), 'updated_at' => now(), 'created_by' => auth()->id(), 'updated_by' => auth()->id()]);
            $sequence = LotSequence::where('base', $base)->lockForUpdate()->firstOrFail();
            do {
                $suffix = $sequence->next_suffix++;
                $number = $base.($suffix === 1 ? '' : '-'.$suffix);
            } while (Lot::withTrashed()->where('lot_number', $number)->exists());
            $sequence->save();

            return Lot::create(['lot_number' => $number, 'lot_date' => $date, 'current_stage' => 'knitting']);
        }, 5);
    }
}
