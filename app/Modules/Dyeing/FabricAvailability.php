<?php

namespace App\Modules\Dyeing;

use App\Models\DyeingRow;
use App\Models\Lot;
use App\Support\Decimal as D;
use Brick\Math\RoundingMode;
use Illuminate\Database\Eloquent\Builder;

final class FabricAvailability
{
    public function selectable(?int $includeLotId = null): Builder
    {
        return Lot::where(function ($query) use ($includeLotId): void {
            $query->whereHas('knittingJob.diaRows', fn ($query) => $query->whereRaw('knitting_dia_rows.output_fabric_kg > COALESCE((SELECT ROUND(SUM(dr.given_fabric_kg), 3) FROM dyeing_rows dr JOIN dyeing_color_blocks cb ON cb.id = dr.color_block_id JOIN dyeing_jobs dj ON dj.id = cb.dyeing_job_id WHERE dj.lot_id = knitting_jobs.lot_id AND dr.dia = knitting_dia_rows.dia AND dr.deleted_at IS NULL AND cb.deleted_at IS NULL AND dj.deleted_at IS NULL), 0)'));
            if ($includeLotId) {
                $query->orWhere('id', $includeLotId);
            }
        });
    }

    public function consumed(Lot $lot, ?int $exceptJobId = null): array
    {
        $rows = DyeingRow::whereHas('colorBlock.dyeingJob', function ($query) use ($lot, $exceptJobId): void {
            $query->where('lot_id', $lot->id)->when($exceptJobId, fn ($query) => $query->where('id', '!=', $exceptJobId));
        })->get();
        $used = [];
        foreach ($rows as $row) {
            $used[$row->dia] = (string) D::of($used[$row->dia] ?? '0')->plus($row->given_fabric_kg);
        }

        return $used;
    }

    public function forLot(Lot $lot, ?int $exceptJobId = null): array
    {
        $used = $this->consumed($lot, $exceptJobId);

        return $lot->knittingJob->diaRows->map(fn ($row): array => ['dia' => $row->dia, 'output_kg' => $row->output_fabric_kg, 'consumed_kg' => D::scale($used[$row->dia] ?? '0'), 'available_kg' => D::scale(D::of($row->output_fabric_kg)->minus($used[$row->dia] ?? '0'))])->all();
    }

    public function split(string $availableKg, int $count): array
    {
        if ($count < 1) {
            return [];
        }
        $portion = D::of($availableKg)->dividedBy((string) $count, 3, RoundingMode::Down);
        $result = array_fill(0, $count, (string) $portion);
        $result[$count - 1] = D::scale(D::of($availableKg)->minus($portion->multipliedBy((string) ($count - 1))));

        return $result;
    }
}
