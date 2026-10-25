<?php

namespace App\Modules\Dyeing;

use App\Models\ActivityLog;
use App\Models\DyeingColorBlock;
use App\Models\DyeingJob;
use App\Models\DyeingRow;
use App\Models\Lot;
use App\Modules\Companies\CompanyService;
use App\Modules\Yarn\UnitConverter;
use App\Support\Decimal as D;
use App\Support\ReturnedRolls;
use App\Support\WasteCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class DyeingService
{
    public function __construct(private FabricAvailability $fabric, private ColorService $colors, private CompanyService $companies) {}

    public function save(array $data, ?DyeingJob $job = null): DyeingJob
    {
        return DB::transaction(function () use ($data, $job): DyeingJob {
            $rollNumbers = [];
            foreach ($data['blocks'] as $i => $block) {
                foreach ($block['rows'] as $j => $row) {
                    $rolls = [];
                    foreach ($row['rolls'] ?? [] as $k => $roll) {
                        $number = trim($roll['number']);
                        $key = mb_strtolower($number);
                        if ($number === '' || isset($rollNumbers[$key])) {
                            throw ValidationException::withMessages(['blocks.'.$i.'.rows.'.$j.'.rolls.'.$k.'.number' => 'Enter a unique roll number within this dyeing job.']);
                        }
                        $rollNumbers[$key] = true;
                        $kg = UnitConverter::toKg($roll['weight'], $roll['unit']);
                        if (! D::of($kg)->isPositive()) {
                            throw ValidationException::withMessages(['blocks.'.$i.'.rows.'.$j.'.rolls.'.$k.'.weight' => 'Roll weight must be at least 0.001 kg.']);
                        }
                        $rolls[] = ['number' => $number, 'weight' => D::scale(D::normalize($roll['weight'])), 'unit' => $roll['unit'], 'weight_kg' => $kg];
                    }
                    $data['blocks'][$i]['rows'][$j]['rolls'] = $rolls;
                    if ($rolls !== []) {
                        $data['blocks'][$i]['rows'][$j]['returned_fabric_kg'] = D::scale(D::sum(array_column($rolls, 'weight_kg')));
                        if (D::of($data['blocks'][$i]['rows'][$j]['returned_fabric_kg'])->isGreaterThan('99999999999.999')) {
                            throw ValidationException::withMessages(['blocks.'.$i.'.rows.'.$j.'.rolls' => 'Combined roll weight exceeds the supported weight range.']);
                        }
                    }
                }
            }
            if ($job && $job->lot_id !== (int) $data['lot_id']) {
                throw ValidationException::withMessages(['lot_id' => 'The lot of a saved dyeing job is immutable.']);
            }
            $lot = Lot::lockForUpdate()->findOrFail($data['lot_id']);
            $job = $job ? DyeingJob::lockForUpdate()->findOrFail($job->id) : new DyeingJob;
            $before = $job->exists ? $job->load(['colorBlocks.color', 'colorBlocks.rows'])->toArray() : null;
            $this->companies->select((int) $data['company_id'], 'dyeing', $job->company_id);
            $available = collect($this->fabric->forLot($lot, $job->id))->keyBy('dia');
            $given = [];
            $colorNames = [];
            $hasPositive = false;
            foreach ($data['blocks'] as $i => $block) {
                $key = mb_strtolower(trim($block['color']));
                if (isset($colorNames[$key])) {
                    throw ValidationException::withMessages(['blocks.'.$i.'.color' => 'Colors must be unique within a job.']);
                }
                $colorNames[$key] = true;
                $seen = [];
                foreach ($block['rows'] as $j => $row) {
                    $dia = (int) $row['dia'];
                    if (! $available->has($dia) || isset($seen[$dia])) {
                        throw ValidationException::withMessages(['blocks.'.$i.'.rows' => 'Each DIA must occur once and exist in this lot.']);
                    }
                    $seen[$dia] = true;
                    $given[$dia] = (string) D::of($given[$dia] ?? '0')->plus($row['given_fabric_kg']);
                    $hasPositive = $hasPositive || D::of($row['given_fabric_kg'])->isPositive();
                    if (isset($row['returned_fabric_kg']) && D::of($row['returned_fabric_kg'])->isGreaterThan($row['given_fabric_kg']) && empty($data['confirm_excess_return'])) {
                        throw ValidationException::withMessages(['confirm_excess_return' => 'Returned exceeds given — confirm the added dye weight.']);
                    }
                }
                if (count($seen) !== $available->count()) {
                    throw ValidationException::withMessages(['blocks.'.$i.'.rows' => 'Include every DIA from this lot.']);
                }
            }
            if (! $hasPositive) {
                throw ValidationException::withMessages(['blocks' => 'Enter a positive fabric allocation.']);
            }
            foreach ($given as $dia => $kg) {
                if (D::of($kg)->isGreaterThan($available[$dia]['available_kg'])) {
                    throw ValidationException::withMessages(['blocks' => 'DIA '.$dia.' has '.$available[$dia]['available_kg'].' kg remaining; requested '.D::scale($kg).' kg.']);
                }
            }
            $job->fill(['lot_id' => $lot->id, 'company_id' => $data['company_id'], 'entry_date' => $data['entry_date'], 'return_date' => $data['return_date'] ?? null, 'notes' => $data['notes'] ?? null]);
            $job->save();
            foreach ($job->colorBlocks()->get() as $block) {
                $block->rows()->get()->each->delete();
                $block->delete();
            }
            $ordered = $data['blocks'];
            uasort($ordered, fn ($a, $b): int => strcmp(mb_strtolower(trim($a['color'])), mb_strtolower(trim($b['color']))));
            foreach ($ordered as $i => $block) {
                $color = $this->colors->findOrCreate($block['color']);
                $savedBlock = DyeingColorBlock::withTrashed()->updateOrCreate(['dyeing_job_id' => $job->id, 'color_id' => $color->id], ['sort_order' => $i, 'deleted_at' => null]);
                foreach ($block['rows'] as $row) {
                    $givenKg = D::scale($row['given_fabric_kg']);
                    $returned = isset($row['returned_fabric_kg']) ? D::scale($row['returned_fabric_kg']) : null;
                    DyeingRow::withTrashed()->updateOrCreate(['color_block_id' => $savedBlock->id, 'dia' => $row['dia']],
                        ['given_fabric_kg' => $givenKg, 'returned_fabric_kg' => $returned, 'waste_percent' => WasteCalculator::percent($givenKg, $returned), 'rolls' => $row['rolls'], 'deleted_at' => null]);
                }
            }
            app(ReturnedRolls::class)->sync($job);
            $lot->update(['current_stage' => 'dyeing']);
            if ($before !== null) {
                ActivityLog::create(['event' => 'updated', 'subject_type' => $job->getMorphClass(), 'subject_id' => $job->id,
                    'properties' => ['before' => $before, 'after' => $job->fresh(['colorBlocks.color', 'colorBlocks.rows'])->toArray()]]);
            }

            return $job;
        }, 5);
    }

    public function delete(DyeingJob $job): void
    {
        DB::transaction(function () use ($job): void {
            $lot = Lot::lockForUpdate()->findOrFail($job->lot_id);
            $job = DyeingJob::lockForUpdate()->findOrFail($job->id);
            app(ReturnedRolls::class)->release($job);
            foreach ($job->colorBlocks as $block) {
                $block->rows->each->delete();
                $block->delete();
            }
            $job->delete();
            if (! $lot->dyeingJobs()->exists()) {
                $lot->update(['current_stage' => 'knitting']);
            }
        }, 5);
    }
}
