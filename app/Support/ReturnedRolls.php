<?php

namespace App\Support;

use App\Models\DyeingJob;
use App\Models\ReturnedRoll;
use Illuminate\Validation\ValidationException;

final class ReturnedRolls
{
    /** Synchronize inside the dyeing transaction while its lot is locked. */
    public function sync(DyeingJob $job): void
    {
        $existing = ReturnedRoll::where('dyeing_job_id', $job->id)->lockForUpdate()->get()->keyBy('number_key');
        $seen = [];
        foreach ($job->fresh(['colorBlocks.rows', 'colorBlocks.color'])->colorBlocks as $block) {
            foreach ($block->rows as $row) {
                foreach ($row->rolls ?? [] as $data) {
                    if ($row->returned_fabric_kg === null) {
                        continue;
                    }
                    $key = mb_strtolower(trim($data['number']));
                    $seen[] = $key;
                    $roll = $existing->get($key);
                    if ($roll && $roll->cuttingRows()->exists() && ($roll->dyeing_row_id !== $row->id || ! Decimal::of($roll->weight_kg)->isEqualTo($data['weight_kg']) || $roll->number !== $data['number'])) {
                        throw ValidationException::withMessages(['blocks' => 'Roll '.$roll->number.' has cutting records. Its number, color, DIA and weight cannot be changed.']);
                    }
                    ReturnedRoll::updateOrCreate(['dyeing_job_id' => $job->id, 'number_key' => $key], ['dyeing_row_id' => $row->id, 'number' => $data['number'], 'weight' => $data['weight'], 'unit' => $data['unit'], 'weight_kg' => $data['weight_kg'], 'is_available' => true]);
                }
            }
        }
        foreach ($existing as $key => $roll) {
            if (! in_array($key, $seen, true)) {
                if ($roll->cuttingRows()->exists()) {
                    throw ValidationException::withMessages(['blocks' => 'Roll '.$roll->number.' has cutting records and cannot be removed.']);
                }
                $roll->update(['is_available' => false]);
            }
        }
    }

    public function release(DyeingJob $job): void
    {
        $rolls = ReturnedRoll::where('dyeing_job_id', $job->id)->lockForUpdate()->get();
        foreach ($rolls as $roll) {
            if ($roll->cuttingRows()->exists()) {
                throw ValidationException::withMessages(['job' => 'This dyeing job has cutting records and cannot be deleted.']);
            }
        }
        ReturnedRoll::where('dyeing_job_id', $job->id)->update(['is_available' => false]);
    }
}
