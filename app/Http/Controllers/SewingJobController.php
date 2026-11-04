<?php

namespace App\Http\Controllers;

use App\Http\Requests\SewingRequest;
use App\Models\CuttingJobRoll;
use App\Models\Lot;
use App\Models\SewingJob;
use App\Models\SewingJobRow;
use App\Models\Staff;
use App\Support\Decimal;
use App\Support\SewingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SewingJobController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['from' => ['nullable', 'date_format:Y-m-d'], 'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'], 'staff_id' => ['nullable', 'integer'], 'lot_id' => ['nullable', 'integer']]);
        $filteredJobs = SewingJob::with(['staff', 'rows.lot'])->when($request->from, fn ($q) => $q->whereDate('sewing_date', '>=', $request->from))->when($request->to, fn ($q) => $q->whereDate('sewing_date', '<=', $request->to))->when($request->staff_id, fn ($q) => $q->where('staff_id', $request->staff_id))->when($request->lot_id, fn ($q) => $q->whereHas('rows', fn ($q) => $q->where('lot_id', $request->lot_id)));
        $overviewJobs = (clone $filteredJobs)->get();
        $jobs = (clone $filteredJobs)->latest('sewing_date')->latest('id')->paginate(25)->withQueryString();

        $steps = [
            ['key' => 'fold', 'label' => 'FOLD', 'description' => 'Prepare body pieces', 'icon' => 'layers'],
            ['key' => 'lock', 'label' => 'LOCK', 'description' => 'Secure folded parts', 'icon' => 'factory'],
            ['key' => 'down', 'label' => 'DOWN', 'description' => 'Join lower body', 'icon' => 'arrow'],
            ['key' => 'neck_join', 'label' => 'NECK JOIN', 'description' => 'Attach neck piece', 'icon' => 'plus'],
            ['key' => 'neck_finishing', 'label' => 'NECK FINISHING', 'description' => 'Finish and inspect', 'icon' => 'settings'],
        ];

        $stationPanels = collect($steps)->mapWithKeys(fn (array $step): array => [$step['key'] => [
            ...$this->formData(new SewingJob, $step['key']),
            'stationJobs' => $overviewJobs->where('current_step', $step['key'])->values(),
        ]])->all();

        $overview = collect($steps)->mapWithKeys(function (array $step) use ($overviewJobs): array {
            $stepJobs = $overviewJobs->where('current_step', $step['key']);
            $column = $step['key'].'_dozens';
            $piecesColumn = $step['key'].'_pieces';

            return [$step['key'] => [
                'executions' => $stepJobs->count(),
                'batches' => $stepJobs->sum(fn (SewingJob $job): int => $job->rows->count()),
                'pieces' => Decimal::sum($stepJobs->flatMap(fn (SewingJob $job) => $job->rows->map(fn (SewingJobRow $row): string => (string) Decimal::of($row->{$column})->multipliedBy(12)->plus($row->{$piecesColumn})))),
                'rejected' => Decimal::sum($stepJobs->flatMap(fn (SewingJob $job) => $job->rows->pluck('rejected_pieces'))),
            ]];
        })->all();

        return view('sewing.index', ['jobs' => $jobs, 'overview' => $overview, 'overviewJobs' => $overviewJobs, 'steps' => $steps, 'stationPanels' => $stationPanels, 'staffs' => Staff::orderBy('name')->get(), 'lots' => Lot::latest('id')->get()]);
    }

    public function create(): View
    {
        return $this->form(new SewingJob);
    }

    public function store(SewingRequest $request, SewingService $service): RedirectResponse
    {
        return to_route('sewing-jobs.show', $service->save($request->validated()))->with('status', 'Sewing job saved.');
    }

    public function show(SewingJob $sewingJob): View
    {
        return view('sewing.show', ['job' => $sewingJob->load(['staff', 'rows.lot', 'rows.sourceBodyRow', 'rows.sourceNeckRow', 'rows.sourceRibRow'])]);
    }

    public function edit(SewingJob $sewingJob): View
    {
        return $this->form($sewingJob);
    }

    public function update(SewingRequest $request, SewingJob $sewingJob, SewingService $service): RedirectResponse
    {
        $service->save($request->validated(), $sewingJob);

        return to_route('sewing-jobs.show', $sewingJob)->with('status', 'Sewing job updated.');
    }

    public function destroy(SewingJob $sewingJob, SewingService $service): RedirectResponse
    {
        $service->delete($sewingJob);

        return to_route('sewing-jobs.index')->with('status', 'Sewing job deleted.');
    }

    private function form(SewingJob $job): View
    {
        $step = request('step', $job->exists ? $job->current_step : 'fold');

        return view('sewing.form', $this->formData($job, $step));
    }

    private function formData(SewingJob $job, string $step): array
    {
        abort_unless(in_array($step, ['fold', 'lock', 'down', 'neck_join', 'neck_finishing'], true), 404);
        $steps = ['fold', 'lock', 'down', 'neck_join', 'neck_finishing'];
        $stepIndex = array_search($step, $steps, true);
        $current = $step.'_dozens';
        $previous = $stepIndex > 0 ? $steps[$stepIndex - 1] : null;
        $used = SewingJobRow::query()->when($job->exists, fn ($q) => $q->where('sewing_job_id', '!=', $job->id))->selectRaw("group_key, SUM({$current} * 12 + {$step}_pieces) AS pieces")->groupBy('group_key')->pluck('pieces', 'group_key');
        $previousTotals = $previous ? SewingJobRow::query()->selectRaw("group_key, SUM({$previous}_dozens * 12 + {$previous}_pieces) AS pieces")->groupBy('group_key')->pluck('pieces', 'group_key') : collect();
        $available = CuttingJobRoll::with(['job', 'lot'])->whereIn('cutting_type', ['body', 'neck'])->whereHas('job')->get()->groupBy(fn (CuttingJobRoll $row): string => $row->lot_id.':'.$row->color.':'.$row->dia)->map(function (Collection $group, string $key) use ($used, $previousTotals, $stepIndex): ?array {
            $body = $group->where('cutting_type', 'body');
            $neck = $group->where('cutting_type', 'neck');
            if ($body->isEmpty() || $neck->isEmpty()) {
                return null;
            }
            $bodyPieces = Decimal::of(Decimal::sum($body->map(fn (CuttingJobRoll $row): string => $row->pieces ?? (string) Decimal::of($row->dozens)->multipliedBy(12))));
            $neckPieces = Decimal::of(Decimal::sum($neck->map(fn (CuttingJobRoll $row): string => $row->pieces ?? (string) Decimal::of($row->dozens)->multipliedBy(12))));
            $capacity = $stepIndex === 0 ? ($bodyPieces->isLessThan($neckPieces) ? $bodyPieces : $neckPieces) : Decimal::of($previousTotals[$key] ?? '0');
            $remaining = $capacity->minus($used[$key] ?? '0');
            if (! $remaining->isPositive()) {
                return null;
            }

            return ['id' => $key, 'group_key' => $key, 'lot_id' => $body->first()->lot_id, 'lot' => $body->first()->lot->lot_number, 'color' => $body->first()->color, 'dia' => $body->first()->dia, 'body_pieces' => Decimal::scale($bodyPieces), 'neck_pieces' => Decimal::scale($neckPieces), 'capacity_pieces' => Decimal::scale($capacity), 'pieces' => Decimal::scale($remaining), 'dozens' => Decimal::scale($remaining->dividedBy(12, 3)), 'source_body_row_id' => $body->first()->id, 'source_neck_row_id' => $neck->first()->id];
        })->filter()->values();
        $rows = old('rows', $job->exists ? $job->rows->map(fn (SewingJobRow $row): array => $row->only(['group_key', 'source_body_row_id', 'source_neck_row_id', 'fold_dozens', 'fold_pieces', 'lock_dozens', 'lock_pieces', 'down_dozens', 'down_pieces', 'neck_join_dozens', 'neck_join_pieces', 'neck_finishing_dozens', 'neck_finishing_pieces', 'rejected_pieces']))->all() : []);

        return ['job' => $job, 'step' => $step, 'steps' => $steps, 'staffs' => Staff::where('is_active', true)->orderBy('name')->get(), 'available' => $available, 'rows' => array_values($rows)];
    }
}
