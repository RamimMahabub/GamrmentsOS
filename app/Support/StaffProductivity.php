<?php

namespace App\Support;

use App\Models\CuttingJobRoll;
use App\Models\Staff;
use Illuminate\Support\Collection;

final class StaffProductivity
{
    public function entries(Staff $staff, string $from, string $to): Collection
    {
        return CuttingJobRoll::whereNotNull('cut_weight_kg')->with(['job', 'lot', 'sourceBodyRow'])->whereHas('job', fn ($q) => $q->where('staff_id', $staff->id)->whereBetween('cutting_date', [$from, $to]))->get()->map(fn ($row): array => [
            'date' => $row->job->cutting_date->toDateString(), 'step' => 'cutting', 'section' => $row->job->cutting_type, 'dozens' => $row->dozens, 'pieces' => Decimal::scale(Decimal::of($row->dozens)->multipliedBy(12)),
            'lot' => $row->lot->lot_number, 'lot_id' => $row->lot_id, 'color' => $row->color, 'dia' => $row->dia, 'body_job_id' => $row->sourceBodyRow?->cutting_job_id, 'job_id' => $row->cutting_job_id, 'roll_weight_kg' => $row->roll_weight_kg, 'cut_weight_kg' => $row->cut_weight_kg, 'cutting_scrap_kg' => $row->cutting_scrap_kg, 'remaining_fabric_kg' => $row->remaining_fabric_kg, 'waste_kg' => $row->waste_kg,
        ])->sortByDesc('date')->values();
    }

    /** @return array<int, string> */
    public function lifetimeDozens(Collection $staffs): array
    {
        return CuttingJobRoll::whereNotNull('cut_weight_kg')->with('job:id,staff_id')->whereHas('job', fn ($query) => $query->whereIn('staff_id', $staffs->pluck('id')))->get()
            ->groupBy('job.staff_id')->map(fn (Collection $rows): string => Decimal::scale(Decimal::sum($rows->pluck('dozens'))))->all();
    }

    public function totals(Collection $entries): array
    {
        $totals = [];
        foreach ($entries->groupBy(fn (array $entry): string => $entry['step'].':'.$entry['section']) as $key => $rows) {
            $dozens = Decimal::scale(Decimal::sum($rows->pluck('dozens')));
            $totals[$key] = ['dozens' => $dozens, 'pieces' => Decimal::scale(Decimal::of($dozens)->multipliedBy(12))];
        }

        return $totals;
    }
}
