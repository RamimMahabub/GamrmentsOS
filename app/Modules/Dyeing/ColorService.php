<?php

namespace App\Modules\Dyeing;

use App\Models\ActivityLog;
use App\Models\Color;
use App\Models\DyeingColorBlock;
use App\Models\DyeingJob;
use App\Models\DyeingRow;
use App\Models\Lot;
use App\Support\Decimal as D;
use App\Support\WasteCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ColorService
{
    public function findOrCreate(string $name): Color
    {
        $name = trim($name);
        $key = mb_strtolower($name);
        DB::table('colors')->insertOrIgnore(['name' => $name, 'name_key' => $key, 'created_at' => now(), 'updated_at' => now(), 'created_by' => auth()->id(), 'updated_by' => auth()->id()]);
        $color = Color::withTrashed()->where('name_key', $key)->lockForUpdate()->firstOrFail();
        if ($color->trashed()) {
            $color->restore();
        }

        return $color;
    }

    public function update(Color $color, array $data): void
    {
        DB::transaction(function () use ($color, $data): void {
            $color = Color::lockForUpdate()->findOrFail($color->id);
            $color->update(['name' => trim($data['name']), 'name_key' => mb_strtolower(trim($data['name'])), 'hex' => $data['hex'] ?? null]);
        }, 5);
    }

    public function merge(Color $source, Color $target): void
    {
        DB::transaction(function () use ($source, $target): void {
            if ($source->id === $target->id) {
                throw ValidationException::withMessages(['color' => 'Choose a different target color.']);
            }
            $jobIds = DyeingColorBlock::where('color_id', $source->id)->pluck('dyeing_job_id');
            $lotIds = DyeingJob::whereIn('id', $jobIds)->pluck('lot_id')->unique()->sort()->values();
            Lot::whereIn('id', $lotIds)->orderBy('id')->lockForUpdate()->get();
            $ids = [$source->id, $target->id];
            sort($ids);
            Color::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
            $source = Color::findOrFail($source->id);
            $target = Color::findOrFail($target->id);
            foreach (DyeingColorBlock::with('rows')->where('color_id', $source->id)->get() as $block) {
                $destination = DyeingColorBlock::withTrashed()->where('dyeing_job_id', $block->dyeing_job_id)->where('color_id', $target->id)->first();
                if (! $destination) {
                    $block->update(['color_id' => $target->id]);
                } else {
                    $active = ! $destination->trashed();
                    foreach ($block->rows as $row) {
                        $other = $active ? $destination->rows()->where('dia', $row->dia)->first() : null;
                        if ($other && (($other->returned_fabric_kg === null) !== ($row->returned_fabric_kg === null))) {
                            throw ValidationException::withMessages(['color' => 'Finish returns for both colors before merging partially returned rows within the same job.']);
                        }
                        $given = D::scale(D::of($row->given_fabric_kg)->plus($other?->given_fabric_kg ?? '0'));
                        $returned = $row->returned_fabric_kg === null ? null : D::scale(D::of($row->returned_fabric_kg)->plus($other?->returned_fabric_kg ?? '0'));
                        DyeingRow::withTrashed()->updateOrCreate(['color_block_id' => $destination->id, 'dia' => $row->dia],
                            ['given_fabric_kg' => $given, 'returned_fabric_kg' => $returned, 'waste_percent' => WasteCalculator::percent($given, $returned), 'deleted_at' => null]);
                        $row->delete();
                    }
                    if (! $active) {
                        $destination->restore();
                    }
                    $block->delete();
                }
                DyeingJob::findOrFail($block->dyeing_job_id)->touch();
                ActivityLog::create(['event' => 'updated', 'subject_type' => DyeingJob::class, 'subject_id' => $block->dyeing_job_id,
                    'properties' => ['operation' => 'color_merge', 'source_color_id' => $source->id, 'target_color_id' => $target->id]]);
            }
            $source->delete();
        }, 5);
    }
}
