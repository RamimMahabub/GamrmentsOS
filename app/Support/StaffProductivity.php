<?php

namespace App\Support;

use App\Models\CuttingJobRoll;
use App\Models\PackingJobRow;
use App\Models\Staff;
use Illuminate\Support\Collection;

final class StaffProductivity
{
    public function entries(Staff $staff, string $from, string $to): Collection
    {
        $cutting = CuttingJobRoll::whereNotNull('cut_weight_kg')
            ->with(['job', 'lot', 'sourceBodyRow'])
            ->whereHas('job', fn ($q) => $q->where('staff_id', $staff->id)->whereBetween('cutting_date', [$from, $to]))
            ->get()
            ->map(fn ($row): array => [
                'date' => $row->job->cutting_date->toDateString(),
                'step' => 'cutting',
                'section' => $row->job->cutting_type,
                'dozens' => $row->dozens,
                'pieces' => CuttingQuantity::pieces($row),
                'lot' => $row->lot->lot_number,
                'lot_id' => $row->lot_id,
                'color' => $row->color,
                'dia' => $row->dia,
                'body_job_id' => $row->sourceBodyRow?->cutting_job_id,
                'job_id' => $row->cutting_job_id,
                'roll_weight_kg' => $row->roll_weight_kg,
                'cut_weight_kg' => $row->cut_weight_kg,
                'cutting_scrap_kg' => $row->cutting_scrap_kg,
                'remaining_fabric_kg' => $row->remaining_fabric_kg,
                'waste_kg' => $row->waste_kg,
            ]);

        $packing = PackingJobRow::with(['job', 'lot'])
            ->whereHas('job', fn ($q) => $q->where('staff_id', $staff->id)->whereBetween('packing_date', [$from, $to]))
            ->get()
            ->map(fn ($row): array => [
                'date' => $row->job->packing_date->toDateString(),
                'step' => 'packing',
                'section' => 'packing',
                'dozens' => (string) $row->dozens,
                'packets' => (int) $row->packets,
                'pieces' => (string) $row->total_pieces,
                'lot' => $row->lot->lot_number,
                'lot_id' => $row->lot_id,
                'color' => $row->color,
                'dia' => $row->dia,
                'size' => $row->size,
                'job_id' => $row->packing_job_id,
            ]);

        return $cutting->concat($packing)->sortByDesc('date')->values();
    }

    /** @return array<int, string> */
    public function lifetimePieces(Collection $staffs): array
    {
        $staffIds = $staffs->pluck('id')->all();

        $cutting = CuttingJobRoll::whereNotNull('cut_weight_kg')
            ->with('job:id,staff_id')
            ->whereHas('job', fn ($query) => $query->whereIn('staff_id', $staffIds))
            ->get()
            ->groupBy('job.staff_id');

        $packing = PackingJobRow::with('job:id,staff_id')
            ->whereHas('job', fn ($query) => $query->whereIn('staff_id', $staffIds))
            ->get()
            ->groupBy('job.staff_id');

        $result = [];
        foreach ($staffIds as $id) {
            $cuttingPieces = Decimal::sum(($cutting->get($id) ?? collect())->map(fn ($row): string => CuttingQuantity::pieces($row)));
            $packingPieces = (string) (($packing->get($id) ?? collect())->sum('total_pieces'));
            $result[$id] = Decimal::scale(Decimal::of($cuttingPieces)->plus($packingPieces));
        }

        return $result;
    }

    public function totals(Collection $entries): array
    {
        $totals = [];
        foreach ($entries->groupBy(fn (array $entry): string => $entry['step'].':'.$entry['section']) as $key => $rows) {
            $pieces = Decimal::scale(Decimal::sum($rows->pluck('pieces')));
            $dozens = CuttingQuantity::dozens($pieces);
            $total = [
                'dozens' => $dozens,
                'pieces' => $pieces,
            ];
            if ($key === 'packing:packing') {
                $total['packets'] = (int) $rows->sum(fn ($r) => (int) ($r['packets'] ?? 0));
            }
            $totals[$key] = $total;
        }

        return $totals;
    }
}
