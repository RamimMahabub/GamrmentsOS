<?php

namespace App\Modules\Knitting;

use App\Models\ActivityLog;
use App\Models\KnittingDiaRow;
use App\Models\KnittingDiaRowYarn;
use App\Models\KnittingJob;
use App\Models\KnittingJobYarn;
use App\Models\Lot;
use App\Models\Yarn;
use App\Modules\Companies\CompanyService;
use App\Modules\Dyeing\FabricAvailability;
use App\Modules\Yarn\StockLedger;
use App\Support\Decimal as D;
use App\Support\WasteCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class KnittingService
{
    public function __construct(private StockLedger $ledger, private LotGenerator $lots, private FabricAvailability $fabric, private CompanyService $companies) {}

    public function save(array $data, ?KnittingJob $job = null): KnittingJob
    {
        return DB::transaction(function () use ($data, $job): KnittingJob {
            $lot = $job ? Lot::lockForUpdate()->findOrFail($job->lot_id) : null;
            $job = $job ? KnittingJob::lockForUpdate()->findOrFail($job->id) : new KnittingJob;
            $before = $job->exists ? $job->load(['diaRows.yarnLines', 'yarnLinks'])->toArray() : null;
            $this->companies->select((int) $data['company_id'], 'knitting', $job->company_id);
            $selected = array_map('intval', $data['yarn_ids']);
            $lockIds = array_unique(array_merge($selected, $job->exists ? $job->yarnLinks()->pluck('yarn_id')->all() : []));
            sort($lockIds);
            $yarns = Yarn::whereIn('id', $lockIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            foreach ($selected as $id) {
                if (! $yarns->has($id)) {
                    throw ValidationException::withMessages(['yarn_ids' => 'A selected yarn is no longer available.']);
                }
            }
            $totals = array_fill_keys($selected, '0.000');
            $computed = [];
            foreach ($data['rows'] as $index => $row) {
                $quantities = [];
                foreach ($selected as $id) {
                    $quantities[$id] = D::scale($row['quantities'][$id] ?? '0');
                    $totals[$id] = D::scale(D::of($totals[$id])->plus($quantities[$id]));
                }
                $input = D::scale(D::sum($quantities));
                if (D::of($input)->isGreaterThan('99999999999.999')) {
                    throw ValidationException::withMessages(['rows.'.$index => 'The combined DIA input exceeds the supported weight range.']);
                }
                if (! D::of($input)->isPositive()) {
                    throw ValidationException::withMessages(['rows.'.$index => 'Each DIA needs a positive yarn input.']);
                }
                $computed[] = ['dia' => (int) $row['dia'], 'input_kg' => $input, 'waste_percent' => D::scale($row['waste_percent'], 2),
                    'output_fabric_kg' => WasteCalculator::output($input, $row['waste_percent']), 'sort_order' => $index, 'quantities' => $quantities];
            }
            if ($job->exists) {
                $used = $this->fabric->consumed($lot);
                $newRows = collect($computed)->keyBy('dia');
                foreach ($job->diaRows as $oldRow) {
                    if (! $newRows->has($oldRow->dia)) {
                        throw ValidationException::withMessages(['rows' => 'Saved DIA '.$oldRow->dia.' cannot be removed.']);
                    }
                    if (D::of($newRows[$oldRow->dia]['output_fabric_kg'] ?? '0')->isLessThan($used[$oldRow->dia] ?? '0')) {
                        throw ValidationException::withMessages(['rows' => 'DIA '.$oldRow->dia.' already has '.D::scale($used[$oldRow->dia]).' kg assigned to dyeing.']);
                    }
                }
                $this->ledger->reverseJob($job, now()->toDateString());
            }
            foreach ($selected as $id) {
                if (D::of($totals[$id])->isGreaterThan('99999999999.999')) {
                    throw ValidationException::withMessages(['yarn_ids' => 'The total shipment quantity for '.$yarns[$id]->name.' exceeds the supported weight range.']);
                }
                $available = $this->ledger->balance($yarns[$id]);
                if (D::of($totals[$id])->isGreaterThan($available)) {
                    throw ValidationException::withMessages(['yarn_ids' => $yarns[$id]->name.' stock shortfall: '.D::scale(D::of($totals[$id])->minus($available)).' kg.']);
                }
            }
            $lot ??= $this->lots->create($yarns[$selected[0]], $data['entry_date']);
            $job->fill(['lot_id' => $lot->id, 'company_id' => $data['company_id'], 'entry_date' => $data['entry_date'], 'delivery_date' => $data['delivery_date'] ?? null, 'input_unit' => $data['input_unit'], 'notes' => $data['notes'] ?? null]);
            $job->save();
            $job->yarnLinks()->get()->each->delete();
            foreach ($selected as $id) {
                KnittingJobYarn::withTrashed()->updateOrCreate(['knitting_job_id' => $job->id, 'yarn_id' => $id], ['deleted_at' => null]);
            }
            foreach ($job->diaRows()->get() as $oldRow) {
                $oldRow->yarnLines()->get()->each->delete();
                $oldRow->delete();
            }
            foreach ($computed as $row) {
                $quantities = $row['quantities'];
                unset($row['quantities']);
                $dia = KnittingDiaRow::withTrashed()->updateOrCreate(['knitting_job_id' => $job->id, 'dia' => $row['dia']], $row + ['deleted_at' => null]);
                foreach ($quantities as $id => $kg) {
                    KnittingDiaRowYarn::withTrashed()->updateOrCreate(['dia_row_id' => $dia->id, 'yarn_id' => $id], ['quantity_kg' => $kg, 'deleted_at' => null]);
                }
            }
            foreach ($selected as $id) {
                if (D::of($totals[$id])->isPositive()) {
                    $this->ledger->write($yarns[$id], 'knitting_issue', (string) D::of($totals[$id])->negated(), $data['entry_date'], $job);
                }
            }
            if ($before !== null) {
                ActivityLog::create(['event' => 'updated', 'subject_type' => $job->getMorphClass(), 'subject_id' => $job->id,
                    'properties' => ['before' => $before, 'after' => $job->fresh(['diaRows.yarnLines', 'yarnLinks'])->toArray()]]);
            }

            return $job;
        }, 5);
    }

    public function delete(KnittingJob $job): void
    {
        DB::transaction(function () use ($job): void {
            $lot = Lot::lockForUpdate()->findOrFail($job->lot_id);
            $job = KnittingJob::lockForUpdate()->findOrFail($job->id);
            if ($lot->dyeingJobs()->withTrashed()->exists()) {
                throw ValidationException::withMessages(['job' => 'A lot with dyeing history cannot be deleted.']);
            }
            Yarn::whereIn('id', $job->yarnLinks()->pluck('yarn_id'))->orderBy('id')->lockForUpdate()->get();
            $this->ledger->reverseJob($job, now()->toDateString());
            $job->yarnLinks()->get()->each->delete();
            foreach ($job->diaRows as $row) {
                $row->yarnLines->each->delete();
                $row->delete();
            }
            $job->delete();
            $lot->delete();
        }, 5);
    }
}
