<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\CuttingJob;
use App\Models\CuttingJobRoll;
use App\Models\Lot;
use App\Models\ReturnedRoll;
use App\Models\Staff;
use Brick\Math\RoundingMode;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CuttingService
{
    public function __construct(private NeckAvailability $neckAvailability) {}

    public function save(array $data, string $type, ?CuttingJob $job = null): CuttingJob
    {
        abort_unless(in_array($type, ['body', 'neck', 'rib'], true), 404);

        return DB::transaction(function () use ($data, $type, $job): CuttingJob {
            $isNeck = $type === 'neck';
            $sourceIds = array_column($data['rows'], 'source_body_row_id');
            $ids = $isNeck ? CuttingJobRoll::whereIn('id', $sourceIds)->pluck('roll_id')->all() : array_column($data['rows'], 'roll_id');
            $lotIds = ReturnedRoll::whereIn('id', $ids)->with('dyeingJob')->get()->pluck('dyeingJob.lot_id')->all();
            if ($job) {
                $lotIds = array_merge($lotIds, $job->rows()->pluck('lot_id')->all());
            }
            Lot::whereIn('id', array_unique($lotIds))->orderBy('id')->lockForUpdate()->get();
            $staff = Staff::lockForUpdate()->find($data['staff_id']);
            if (! $staff?->is_active) {
                throw ValidationException::withMessages(['staff_id' => 'Choose an active staff member.']);
            }
            if ($job) {
                $job = CuttingJob::lockForUpdate()->findOrFail($job->id);
                abort_unless($job->cutting_type === $type, 404);
            }
            $before = $job?->load('rows')->toArray();
            $sources = CuttingJobRoll::with('job')->whereIn('id', $sourceIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $rolls = ReturnedRoll::with(['dyeingJob.lot', 'dyeingRow.colorBlock.color'])->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $neckRows = $isNeck ? CuttingJobRoll::whereIn('roll_id', $ids)->where('cutting_type', 'neck')
                ->when($job, fn ($query) => $query->where('cutting_job_id', '!=', $job->id))->lockForUpdate()->get()->groupBy('roll_id') : collect();
            $computed = [];
            $seen = [];
            foreach ($data['rows'] as $index => $row) {
                $source = $isNeck ? $sources->get($row['source_body_row_id'] ?? null) : null;
                if ($isNeck && (! $source || $source->cutting_type !== 'body' || $source->job?->cutting_type !== 'body' || $source->dozens === null || ! Decimal::of($source->dozens)->isPositive() || $source->cut_weight_kg === null || ! Decimal::of($source->cut_weight_kg)->isPositive())) {
                    throw ValidationException::withMessages(['rows.'.$index.'.source_body_row_id' => 'Choose a completed body-cut batch with recorded dozens and cut weight.']);
                }
                if ($isNeck && $data['cutting_date'] < $source->job->cutting_date->toDateString()) {
                    throw ValidationException::withMessages(['cutting_date' => 'Neck cutting cannot be dated before its body cutting job.']);
                }
                $roll = $rolls->get($source?->roll_id ?? $row['roll_id'] ?? null);
                if (! $roll || ! $roll->is_available || $roll->dyeingJob->trashed() || ! $roll->dyeingRow->returned_fabric_kg || isset($seen[$roll->id]) || (! $isNeck && CuttingJobRoll::where('roll_id', $roll->id)->whereIn('cutting_type', ['body', 'rib'])->when($job, fn ($q) => $q->where('cutting_job_id', '!=', $job->id))->lockForUpdate()->first(['id']))) {
                    throw ValidationException::withMessages(['rows.'.$index.'.'.($isNeck ? 'source_body_row_id' : 'roll_id') => $isNeck ? 'This body batch is unavailable or already assigned to neck cutting.' : 'This roll is unavailable or already reserved for body or rib cutting.']);
                }
                $seen[$roll->id] = true;
                $inputWeight = $source?->cut_weight_kg ?? $roll->weight_kg;
                if ($isNeck) {
                    $available = $this->neckAvailability->remaining($source, $neckRows->get($roll->id, collect()));
                    $pieces = CuttingQuantity::fromInput($row);
                    $row['dozens'] = CuttingQuantity::dozens($pieces);
                    if (! Decimal::of($pieces)->isPositive() || Decimal::of($pieces)->isGreaterThan($available['pieces'])) {
                        throw ValidationException::withMessages(['rows.'.$index.'.dozens' => 'Choose a positive quantity up to '.CuttingQuantity::display($available['pieces']).' available from this body batch.']);
                    }
                    $inputWeight = $this->neckAvailability->inputWeight($available, $pieces);
                    if (! Decimal::of(Decimal::normalize($row['remaining_fabric'] ?? '0'))->isZero()) {
                        throw ValidationException::withMessages(['rows.'.$index.'.remaining_fabric' => 'Neck cutting uses body pieces; record the removed neck fabric as cutting scrap, not remaining roll fabric.']);
                    }
                }
                $row['weight_unit'] ??= 'kg';
                $hasDozens = isset($row['dozens']) && $row['dozens'] !== '';
                $hasWeight = isset($row['weight_per_dozen']) && $row['weight_per_dozen'] !== '';
                if (! $hasDozens && $hasWeight) {
                    throw ValidationException::withMessages(['rows.'.$index.'.dozens' => 'Enter the quantity before entering weight per dozen.']);
                }
                if (! $hasDozens) {
                    if (Decimal::of(Decimal::normalize($row['remaining_fabric'] ?? '0'))->isPositive()) {
                        throw ValidationException::withMessages(['rows.'.$index.'.remaining_fabric' => 'Add dozens and weight per dozen before recording remaining fabric.']);
                    }
                    $computed[] = ['roll_id' => $roll->id, 'source_body_row_id' => $source?->id, 'cutting_type' => $type, 'lot_id' => $source?->lot_id ?? $roll->dyeingJob->lot_id, 'color' => $source?->color ?? $roll->dyeingRow->colorBlock->color->name, 'dia' => $source?->dia ?? $roll->dyeingRow->dia, 'roll_weight_kg' => $inputWeight,
                        'dozens' => $isNeck ? $row['dozens'] : null, 'pieces' => $isNeck ? $pieces : null, 'weight_per_dozen' => null, 'weight_per_dozen_kg' => null, 'weight_is_automatic' => false, 'cut_weight_kg' => null, 'cutting_scrap_kg' => null, 'waste_kg' => null, 'waste_percent' => null,
                        'weight_unit' => $row['weight_unit'], 'remaining_fabric' => '0', 'remaining_fabric_unit' => $row['weight_unit'], 'remaining_fabric_kg' => '0'];

                    continue;
                }
                $factor = $row['weight_unit'] === 'lb' ? '0.45359237' : '1';
                $dozens = Decimal::scale(Decimal::normalize($row['dozens']));
                if (! Decimal::of($dozens)->isPositive()) {
                    throw ValidationException::withMessages(['rows.'.$index.'.dozens' => 'Enter a positive cutting quantity.']);
                }
                $remaining = Decimal::scale(Decimal::normalize($row['remaining_fabric'] ?? '0'));
                $remainingKg = Decimal::scale(Decimal::of($remaining)->multipliedBy($factor));
                if (Decimal::of($remainingKg)->isGreaterThan($inputWeight)) {
                    throw ValidationException::withMessages(['rows.'.$index.'.remaining_fabric' => 'Remaining fabric cannot exceed the input weight.']);
                }
                if ($hasWeight) {
                    $weight = Decimal::scale(Decimal::normalize($row['weight_per_dozen']), 6);
                    $perKg = Decimal::scale(Decimal::of($weight)->multipliedBy($factor), 8);
                    $cut = $isNeck ? (string) Decimal::of($pieces)->multipliedBy($perKg)->dividedBy(12, 3, RoundingMode::HalfUp) : Decimal::scale(Decimal::of($dozens)->multipliedBy($perKg));
                } else {
                    $quantityPieces = $isNeck ? $pieces : (string) Decimal::of($dozens)->multipliedBy(12);
                    $cut = Decimal::scale(Decimal::of($inputWeight)->minus($remainingKg));
                    $perKg = (string) Decimal::of($cut)->multipliedBy(12)->dividedBy($quantityPieces, 8, RoundingMode::HalfUp);
                    $weight = (string) Decimal::of($cut)->multipliedBy(12)->dividedBy(Decimal::of($quantityPieces)->multipliedBy($factor), 6, RoundingMode::HalfUp);
                    if (Decimal::of($perKg)->isGreaterThan('9999999999.99999999') || Decimal::of($weight)->isGreaterThan('999999999999.999999')) {
                        throw ValidationException::withMessages(['rows.'.$index.'.dozens' => 'This quantity is too small for its input weight.']);
                    }
                }
                if (Decimal::of($cut)->plus($remainingKg)->isGreaterThan($inputWeight)) {
                    throw ValidationException::withMessages(['rows.'.$index.'.'.($isNeck ? 'weight_per_dozen' : 'remaining_fabric') => ($isNeck ? 'Body batch' : 'Roll').' '.$roll->number.': cut weight plus remaining fabric exceeds its '.$inputWeight.' kg input weight.']);
                }
                $scrap = Decimal::scale(Decimal::of($inputWeight)->minus($cut)->minus($remainingKg));
                $waste = Decimal::scale(Decimal::of($scrap)->plus($remainingKg));
                $computed[] = ['roll_id' => $roll->id, 'source_body_row_id' => $source?->id, 'cutting_type' => $type, 'lot_id' => $source?->lot_id ?? $roll->dyeingJob->lot_id, 'color' => $source?->color ?? $roll->dyeingRow->colorBlock->color->name, 'dia' => $source?->dia ?? $roll->dyeingRow->dia, 'roll_weight_kg' => $inputWeight, 'dozens' => $dozens, 'pieces' => $isNeck ? $pieces : null, 'weight_per_dozen' => $weight, 'weight_unit' => $row['weight_unit'], 'weight_per_dozen_kg' => $perKg, 'weight_is_automatic' => ! $hasWeight, 'cut_weight_kg' => $cut, 'remaining_fabric' => $remaining, 'remaining_fabric_unit' => $row['weight_unit'], 'remaining_fabric_kg' => $remainingKg, 'cutting_scrap_kg' => $scrap, 'waste_kg' => $waste, 'waste_percent' => Decimal::of($inputWeight)->isZero() ? '0.00' : (string) Decimal::of($waste)->multipliedBy(100)->dividedBy($inputWeight, 2, RoundingMode::HalfUp)];
            }
            if ($job && $type === 'body') {
                $this->protectBodyBatches($job, $computed, $data['cutting_date']);
            }
            $job ??= new CuttingJob;
            $job->fill(['cutting_type' => $type, 'cutting_date' => $data['cutting_date'], 'staff_id' => $staff->id, 'notes' => $data['notes'] ?? null])->save();
            $job->rows()->whereNotIn('roll_id', array_column($computed, 'roll_id'))->delete();
            foreach ($computed as $attributes) {
                $job->rows()->updateOrCreate(['roll_id' => $attributes['roll_id']], $attributes);
            }
            ActivityLog::create(['event' => $before ? 'updated' : 'created', 'subject_type' => $job->getMorphClass(), 'subject_id' => $job->id, 'properties' => ['before' => $before, 'after' => $job->fresh('rows')->toArray()]]);

            return $job;
        }, 5);
    }

    public function delete(CuttingJob $job): void
    {
        DB::transaction(function () use ($job): void {
            Lot::whereIn('id', $job->rows()->pluck('lot_id'))->orderBy('id')->lockForUpdate()->get();
            $job = CuttingJob::lockForUpdate()->findOrFail($job->id);
            if ($job->cutting_type === 'body') {
                $this->protectBodyBatches($job, [], null);
            }
            $before = $job->load('rows')->toArray();
            $job->rows()->delete();
            $job->delete();
            ActivityLog::create(['event' => 'deleted', 'subject_type' => $job->getMorphClass(), 'subject_id' => $job->id, 'properties' => ['before' => $before, 'after' => null]]);
        }, 5);
    }

    /** @param array<int, array<string, mixed>> $computed */
    private function protectBodyBatches(CuttingJob $job, array $computed, ?string $cuttingDate): void
    {
        $replacements = collect($computed)->keyBy('roll_id');
        foreach ($job->rows as $body) {
            $neckRows = CuttingJobRoll::with('job')->where('roll_id', $body->roll_id)->where('cutting_type', 'neck')->get();
            if ($neckRows->isEmpty()) {
                continue;
            }
            $replacement = $replacements->get($body->roll_id);
            if (! $replacement || ($cuttingDate && $neckRows->contains(fn (CuttingJobRoll $neck): bool => $neck->job && $cuttingDate > $neck->job->cutting_date->toDateString()))) {
                throw ValidationException::withMessages(['job' => 'This body batch is assigned to neck cutting. Remove that neck assignment before removing or changing its source.']);
            }
            foreach (['lot_id', 'color', 'dia', 'dozens', 'cut_weight_kg', 'roll_weight_kg'] as $field) {
                if ((string) $replacement[$field] !== (string) $body->$field) {
                    throw ValidationException::withMessages(['job' => 'Body quantities are used by neck cutting. Remove that neck assignment before changing the body results.']);
                }
            }
        }
    }
}
