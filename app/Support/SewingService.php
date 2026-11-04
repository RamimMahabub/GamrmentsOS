<?php

namespace App\Support;

use App\Models\CuttingJobRoll;
use App\Models\Lot;
use App\Models\SewingJob;
use App\Models\SewingJobRow;
use Brick\Math\RoundingMode;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SewingService
{
    private const STEPS = ['fold', 'lock', 'down', 'neck_join', 'neck_finishing'];

    public function save(array $data, ?SewingJob $job = null): SewingJob
    {
        return DB::transaction(function () use ($data, $job): SewingJob {
            $job ??= new SewingJob;
            $step = $data['current_step'];
            $stepIndex = array_search($step, self::STEPS, true);
            $stepColumn = $step.'_dozens';
            $previous = $stepIndex > 0 ? self::STEPS[$stepIndex - 1] : null;
            $job->fill(['sewing_date' => $data['sewing_date'], 'staff_id' => $data['staff_id'], 'current_step' => $step, 'notes' => $data['notes'] ?? null])->save();
            $job->rows()->delete();

            foreach ($data['rows'] as $row) {
                [$lotId, $color, $dia] = explode(':', $row['group_key'], 3);
                $sources = CuttingJobRoll::query()->where('lot_id', $lotId)->where('color', $color)->where('dia', $dia)->whereIn('cutting_type', ['body', 'neck'])->lockForUpdate()->get()->groupBy('cutting_type');
                $source = $sources->get('body', collect())->first();
                $neck = $sources->get('neck', collect())->first();
                abort_unless($source && $neck, 422, 'The selected sewing-ready group must have completed body and neck quantities.');
                $bodyPieces = Decimal::of(Decimal::sum($sources->get('body', collect())->map(fn (CuttingJobRoll $model): string => $model->pieces ?? (string) Decimal::of($model->dozens)->multipliedBy(12))));
                $neckPieces = Decimal::of(Decimal::sum($sources->get('neck', collect())->map(fn (CuttingJobRoll $model): string => $model->pieces ?? (string) Decimal::of($model->dozens)->multipliedBy(12))));
                $used = Decimal::of(Decimal::sum(SewingJobRow::query()->where('sewing_job_id', '!=', $job->id)->where('group_key', $row['group_key'])->pluck("{$stepColumn}")))->multipliedBy(12)
                    ->plus(Decimal::sum(SewingJobRow::query()->where('sewing_job_id', '!=', $job->id)->where('group_key', $row['group_key'])->pluck("{$step}_pieces")));
                $capacity = $previous ? Decimal::of(Decimal::sum(SewingJobRow::query()->where('group_key', $row['group_key'])->pluck("{$previous}_dozens")))->multipliedBy(12)->plus(Decimal::sum(SewingJobRow::query()->where('group_key', $row['group_key'])->pluck("{$previous}_pieces"))) : ($bodyPieces->isLessThan($neckPieces) ? $bodyPieces : $neckPieces);
                $stepPieces = Decimal::of($row[$stepColumn] ?? '0')->multipliedBy(12)->plus($row["{$step}_pieces"] ?? 0);
                if ($stepPieces->isGreaterThan($capacity->minus($used))) {
                    throw ValidationException::withMessages(['rows' => "The {$step} quantity exceeds the quantity released by the previous step."]);
                }
                if ($step !== 'neck_finishing' && (int) ($row['rejected_pieces'] ?? 0) > 0) {
                    throw ValidationException::withMessages(['rows' => 'Quality rejection can only be recorded at Neck Finishing.']);
                }
                if ($step === 'neck_finishing' && Decimal::of($row['rejected_pieces'] ?? 0)->isGreaterThan($stepPieces)) {
                    throw ValidationException::withMessages(['rows' => 'Rejected pieces cannot exceed neck finishing output.']);
                }
                $job->rows()->create([
                    'group_key' => $row['group_key'], 'source_body_row_id' => $source->id, 'source_neck_row_id' => $neck->id, 'source_rib_row_id' => null, 'lot_id' => $source->lot_id, 'color' => $source->color, 'dia' => $source->dia,
                    ...collect(self::STEPS)->mapWithKeys(fn (string $name): array => [$name.'_dozens' => $name === $step ? $stepPieces->dividedBy(12, 0, RoundingMode::Down) : 0, $name.'_pieces' => $name === $step ? $stepPieces->minus($stepPieces->dividedBy(12, 0, RoundingMode::Down)->multipliedBy(12))->toInt() : 0])->all(),
                    'rejected_pieces' => $step === 'neck_finishing' ? $row['rejected_pieces'] : 0,
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
