<?php

namespace App\Support;

use App\Models\CuttingJobRoll;
use App\Models\Lot;
use App\Models\SewingJob;
use App\Models\SewingJobRow;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SewingService
{
    public const STEPS = [
        'fold',
        'lock',
        'down',
        'neck_join',
        'neck_finishing',
        'qc_phase_1',
        'iron',
        'qc_phase_2',
    ];

    public static function getUnderpantsFlow(): array
    {
        $subSteps = UnderpantsSewingConfig::allSubSteps();
        $keys = array_column($subSteps, 'key');
        if (empty($keys)) {
            $keys = ['over_lock', 'lock', 'elastic_join'];
        }

        return array_merge($keys, ['qc_phase_1', 'iron', 'qc_phase_2']);
    }

    public static function getSandoFlow(): array
    {
        return self::STEPS;
    }

    public static function getFlowForLot(Lot $lot): array
    {
        return $lot->isUnderpants() ? self::getUnderpantsFlow() : self::getSandoFlow();
    }

    public static function getCompletedPieces(string $groupKey, string $step, ?int $excludeJobId = null): BigDecimal
    {
        $query = SewingJobRow::query()->where('group_key', $groupKey)
            ->when($excludeJobId, fn ($q) => $q->where('sewing_job_id', '!=', $excludeJobId));

        if (in_array($step, self::STEPS, true)) {
            $colD = "{$step}_dozens";
            $colP = "{$step}_pieces";
            $colSum = Decimal::of(Decimal::sum((clone $query)->pluck($colD)))->multipliedBy(12)
                ->plus(Decimal::sum((clone $query)->pluck($colP)));

            return Decimal::of($colSum);
        }

        $rows = $query->where(function ($q) use ($step) {
            $q->where('stage_data->step', $step)
                ->orWhereHas('job', fn ($jq) => $jq->where('current_step', $step));
        })->get();

        $total = Decimal::sum($rows->map(fn ($r) => (string) ($r->stage_data['total_pieces'] ?? (Decimal::of($r->stage_data['dozens'] ?? 0)->multipliedBy(12)->plus($r->stage_data['pieces'] ?? 0)))));

        return Decimal::of($total);
    }

    public static function getStepUsedPieces(string $groupKey, string $step, ?int $excludeJobId = null): BigDecimal
    {
        $query = SewingJobRow::query()->where('group_key', $groupKey)
            ->when($excludeJobId, fn ($q) => $q->where('sewing_job_id', '!=', $excludeJobId));

        if (in_array($step, ['qc_phase_1', 'qc_phase_2'], true)) {
            $passed = Decimal::of(Decimal::sum($query->pluck("{$step}_dozens")))->multipliedBy(12)
                ->plus(Decimal::sum($query->pluck("{$step}_pieces")));
            $rejected = Decimal::sum($query->pluck("{$step}_rejected_pieces"));

            return $passed->plus($rejected);
        }

        return self::getCompletedPieces($groupKey, $step, $excludeJobId);
    }

    public static function getAvailableForStep(Lot $lot, string $groupKey, string $step, ?int $jobId = null): BigDecimal
    {
        $isUnderpants = $lot->isUnderpants();
        $flow = self::getFlowForLot($lot);
        $stepIndex = array_search($step, $flow, true);

        if ($stepIndex === false) {
            return Decimal::of('0');
        }

        [$lotId, $color, $dia] = explode(':', $groupKey, 3);

        if ($stepIndex === 0) {
            if ($isUnderpants) {
                $cutRows = CuttingJobRoll::query()->where('lot_id', $lotId)->where('color', $color)->where('dia', $dia)->where('cutting_type', 'cutting')->get();
                $capacity = Decimal::of(Decimal::sum($cutRows->map(fn ($r) => $r->pieces ?? (string) Decimal::of($r->dozens)->multipliedBy(12))));
            } else {
                $bodyRows = CuttingJobRoll::query()->where('lot_id', $lotId)->where('color', $color)->where('dia', $dia)->where('cutting_type', 'body')->get();
                $neckRows = CuttingJobRoll::query()->where('lot_id', $lotId)->where('color', $color)->where('dia', $dia)->where('cutting_type', 'neck')->get();
                $bPcs = Decimal::of(Decimal::sum($bodyRows->map(fn ($r) => $r->pieces ?? (string) Decimal::of($r->dozens)->multipliedBy(12))));
                $nPcs = Decimal::of(Decimal::sum($neckRows->map(fn ($r) => $r->pieces ?? (string) Decimal::of($r->dozens)->multipliedBy(12))));
                $capacity = $bPcs->isLessThan($nPcs) ? $bPcs : $nPcs;
            }
        } elseif ($step === 'iron') {
            $capacity = self::getCompletedPieces($groupKey, 'qc_phase_1');
        } else {
            $prevStep = $flow[$stepIndex - 1];
            $capacity = self::getCompletedPieces($groupKey, $prevStep);
        }

        $used = self::getStepUsedPieces($groupKey, $step, $jobId);

        return $capacity->minus($used);
    }

    public function save(array $data, ?SewingJob $job = null): SewingJob
    {
        return DB::transaction(function () use ($data, $job): SewingJob {
            $job ??= new SewingJob;
            $step = $data['current_step'];
            $job->fill([
                'sewing_date' => $data['sewing_date'],
                'staff_id' => $data['staff_id'],
                'current_step' => $step,
                'rate_per_dozen' => $data['rate_per_dozen'] ?? null,
                'notes' => $data['notes'] ?? null,
            ])->save();
            $job->rows()->delete();

            foreach ($data['rows'] as $row) {
                [$lotId, $color, $dia] = explode(':', $row['group_key'], 3);
                $lot = Lot::findOrFail($lotId);
                $isUnderpants = $lot->isUnderpants();

                if ($isUnderpants) {
                    $sources = CuttingJobRoll::query()->where('lot_id', $lotId)->where('color', $color)->where('dia', $dia)->where('cutting_type', 'cutting')->lockForUpdate()->get();
                    $source = $sources->first();
                    $neck = $source;
                    abort_unless($source, 422, 'The selected sewing-ready group must have completed cutting quantities.');
                } else {
                    $sources = CuttingJobRoll::query()->where('lot_id', $lotId)->where('color', $color)->where('dia', $dia)->whereIn('cutting_type', ['body', 'neck'])->lockForUpdate()->get()->groupBy('cutting_type');
                    $source = $sources->get('body', collect())->first();
                    $neck = $sources->get('neck', collect())->first();
                    abort_unless($source && $neck, 422, 'The selected sewing-ready group must have completed body and neck quantities.');
                }

                $stepColumn = in_array($step, self::STEPS, true) ? $step.'_dozens' : null;
                $rowDozens = $row[$stepColumn] ?? $row['dozens'] ?? '0';
                $rowPieces = (int) ($row["{$step}_pieces"] ?? $row['pieces'] ?? 0);
                $passedPieces = Decimal::of('0');
                $stepPieces = Decimal::of('0');

                if (in_array($step, ['qc_phase_1', 'qc_phase_2'], true)) {
                    $passedPieces = Decimal::of($rowDozens)->multipliedBy(12)->plus($rowPieces);
                    $alteredPieces = (int) ($row["{$step}_altered_pieces"] ?? 0);
                    $rejectedPieces = (int) ($row["{$step}_rejected_pieces"] ?? 0);

                    $prevStepName = $step === 'qc_phase_1'
                        ? ($isUnderpants ? (last(array_column(UnderpantsSewingConfig::allSubSteps(), 'key')) ?: 'elastic_join') : 'neck_finishing')
                        : 'iron';

                    $prevCapacity = self::getCompletedPieces($row['group_key'], $prevStepName);
                    $usedSoFar = self::getStepUsedPieces($row['group_key'], $step, $job->id);
                    $remainingIncoming = $prevCapacity->minus($usedSoFar);
                    $accounted = $passedPieces->plus($rejectedPieces);

                    if ($accounted->isGreaterThan($remainingIncoming)) {
                        $label = ucwords(str_replace('_', ' ', $prevStepName));
                        throw ValidationException::withMessages(['rows' => "The quantity ({$accounted} pcs) exceeds the available quantity from {$label} ({$remainingIncoming} pcs)."]);
                    }

                    if ($alteredPieces > $remainingIncoming->toInt()) {
                        $label = ucwords(str_replace('_', ' ', $prevStepName));
                        throw ValidationException::withMessages(['rows' => "The altered quantity ({$alteredPieces} pcs) exceeds the available quantity from {$label} ({$remainingIncoming} pcs)."]);
                    }

                    if ($accounted->plus($alteredPieces)->toInt() <= 0) {
                        throw ValidationException::withMessages(['rows' => 'Please enter at least one quantity for selected batches.']);
                    }
                } elseif ($step === 'iron') {
                    $capacity = self::getCompletedPieces($row['group_key'], 'qc_phase_1');
                    $used = self::getCompletedPieces($row['group_key'], 'iron', $job->id);
                    $stepPieces = Decimal::of($rowDozens)->multipliedBy(12)->plus($rowPieces);

                    if ($stepPieces->isGreaterThan($capacity->minus($used))) {
                        throw ValidationException::withMessages(['rows' => 'The iron quantity exceeds the passed quantity from QC Phase 1.']);
                    }
                } else {
                    $remaining = self::getAvailableForStep($lot, $row['group_key'], $step, $job->id);
                    $stepPieces = Decimal::of($rowDozens)->multipliedBy(12)->plus($rowPieces);

                    if ($stepPieces->isGreaterThan($remaining)) {
                        throw ValidationException::withMessages(['rows' => "The {$step} quantity exceeds the quantity released by the previous step."]);
                    }

                    if ($step !== 'neck_finishing' && (int) ($row['rejected_pieces'] ?? 0) > 0) {
                        throw ValidationException::withMessages(['rows' => 'Quality rejection is recorded at the QC stations.']);
                    }

                    if ($step === 'neck_finishing' && Decimal::of($row['rejected_pieces'] ?? 0)->isGreaterThan($stepPieces)) {
                        throw ValidationException::withMessages(['rows' => 'Rejected pieces cannot exceed neck finishing output.']);
                    }
                }

                $finalP = in_array($step, ['qc_phase_1', 'qc_phase_2'], true) ? $passedPieces : $stepPieces;
                $finalDozens = $finalP->dividedBy(12, 0, RoundingMode::Down);
                $finalPieces = $finalP->minus($finalDozens->multipliedBy(12))->toInt();

                $job->rows()->create([
                    'group_key' => $row['group_key'],
                    'stage_data' => [
                        'step' => $step,
                        'dozens' => (string) $finalDozens,
                        'pieces' => $finalPieces,
                        'total_pieces' => $finalP->toInt(),
                    ],
                    'source_body_row_id' => $source->id,
                    'source_neck_row_id' => $neck->id,
                    'source_rib_row_id' => null,
                    'lot_id' => $source->lot_id,
                    'color' => $source->color,
                    'dia' => $source->dia,
                    ...collect(self::STEPS)->mapWithKeys(function (string $name) use ($step, $finalDozens, $finalPieces): array {
                        return [
                            $name.'_dozens' => $name === $step ? $finalDozens : 0,
                            $name.'_pieces' => $name === $step ? $finalPieces : 0,
                        ];
                    })->all(),
                    'qc_phase_1_alter_pieces' => $step === 'qc_phase_1' ? (int) ($row['qc_phase_1_alter_pieces'] ?? 0) : 0,
                    'qc_phase_1_altered_pieces' => $step === 'qc_phase_1' ? (int) ($row['qc_phase_1_altered_pieces'] ?? 0) : 0,
                    'qc_phase_1_rejected_pieces' => $step === 'qc_phase_1' ? (int) ($row['qc_phase_1_rejected_pieces'] ?? 0) : 0,
                    'qc_phase_2_alter_pieces' => $step === 'qc_phase_2' ? (int) ($row['qc_phase_2_alter_pieces'] ?? 0) : 0,
                    'qc_phase_2_altered_pieces' => $step === 'qc_phase_2' ? (int) ($row['qc_phase_2_altered_pieces'] ?? 0) : 0,
                    'qc_phase_2_rejected_pieces' => $step === 'qc_phase_2' ? (int) ($row['qc_phase_2_rejected_pieces'] ?? 0) : 0,
                    'rejected_pieces' => match ($step) {
                        'neck_finishing' => (int) ($row['rejected_pieces'] ?? 0),
                        'qc_phase_1' => (int) ($row['qc_phase_1_rejected_pieces'] ?? 0),
                        'qc_phase_2' => (int) ($row['qc_phase_2_rejected_pieces'] ?? 0),
                        default => 0,
                    },
                ]);
            }

            Lot::syncCurrentStageFor($job->rows()->pluck('lot_id')->unique()->all());

            return $job;
        });
    }

    public function delete(SewingJob $job): void
    {
        DB::transaction(function () use ($job): void {
            $lotIds = $job->rows()->pluck('lot_id')->unique()->all();
            $job->rows()->delete();
            $job->delete();
            Lot::syncCurrentStageFor($lotIds);
        });
    }
}
