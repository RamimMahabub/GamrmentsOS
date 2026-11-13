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
use App\Support\UnderpantsSewingConfig;
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
            ['key' => 'neck_finishing', 'label' => 'NECK FINISHING', 'description' => 'Finish neck stitching', 'icon' => 'neck'],
            ['key' => 'qc_phase_1', 'label' => 'QC PHASE 1', 'description' => 'Quality check & defect sorting', 'icon' => 'check'],
            ['key' => 'iron', 'label' => 'IRON', 'description' => 'Steam press passed pieces', 'icon' => 'sparkles'],
            ['key' => 'qc_phase_2', 'label' => 'QC PHASE 2', 'description' => 'Final quality inspection', 'icon' => 'shield'],
        ];

        $underpantsConfig = UnderpantsSewingConfig::allSubSteps();
        $underpantsSteps = collect($underpantsConfig)->map(fn ($s) => [
            'key' => $s['key'],
            'label' => strtoupper($s['label']),
            'description' => ucwords(str_replace('_', ' ', $s['group'])).' station',
            'icon' => 'factory',
            'group' => $s['group'],
        ])->push(
            ['key' => 'qc_phase_1', 'label' => 'QC PHASE 1', 'description' => 'Quality check & defect sorting', 'icon' => 'check', 'group' => 'qc'],
            ['key' => 'iron', 'label' => 'IRON', 'description' => 'Steam press passed pieces', 'icon' => 'sparkles', 'group' => 'qc'],
            ['key' => 'qc_phase_2', 'label' => 'QC PHASE 2', 'description' => 'Final quality inspection', 'icon' => 'shield', 'group' => 'qc']
        )->all();

        $allSteps = collect($steps)->concat($underpantsSteps)->unique('key')->values();

        $stationPanels = $allSteps->mapWithKeys(fn (array $step): array => [$step['key'] => [
            ...$this->formData(new SewingJob, $step['key']),
            'stationJobs' => $overviewJobs->where('current_step', $step['key'])->values(),
        ]])->all();

        $overview = $allSteps->mapWithKeys(function (array $step) use ($overviewJobs): array {
            $stepJobs = $overviewJobs->where('current_step', $step['key']);
            $column = $step['key'].'_dozens';
            $piecesColumn = $step['key'].'_pieces';

            $basePieces = Decimal::sum($stepJobs->flatMap(fn (SewingJob $job) => $job->rows->map(function (SewingJobRow $row) use ($step, $column, $piecesColumn): string {
                if (isset($row->stage_data['total_pieces']) && ($row->stage_data['step'] ?? null) === $step['key']) {
                    return (string) $row->stage_data['total_pieces'];
                }
                if ($row->job?->current_step === $step['key'] && isset($row->stage_data['dozens'])) {
                    return (string) Decimal::of($row->stage_data['dozens'])->multipliedBy(12)->plus($row->stage_data['pieces'] ?? 0);
                }
                if (isset($row->{$column})) {
                    return (string) Decimal::of($row->{$column})->multipliedBy(12)->plus($row->{$piecesColumn} ?? 0);
                }

                return '0';
            })));

            $alteredPieces = in_array($step['key'], ['qc_phase_1', 'qc_phase_2'], true)
                ? (string) $stepJobs->flatMap(fn (SewingJob $job) => $job->rows->pluck($step['key'].'_altered_pieces'))->sum()
                : '0';
            $rejectedPieces = in_array($step['key'], ['qc_phase_1', 'qc_phase_2'], true)
                ? (string) $stepJobs->flatMap(fn (SewingJob $job) => $job->rows->pluck($step['key'].'_rejected_pieces'))->sum()
                : Decimal::sum($stepJobs->flatMap(fn (SewingJob $job) => $job->rows->pluck('rejected_pieces')));

            return [$step['key'] => [
                'executions' => $stepJobs->count(),
                'batches' => $stepJobs->sum(fn (SewingJob $job): int => $job->rows->count()),
                'pieces' => $basePieces,
                'passed_pieces' => $basePieces,
                'altered' => $alteredPieces,
                'rejected' => $rejectedPieces,
            ]];
        })->all();

        return view('sewing.index', [
            'jobs' => $jobs,
            'overview' => $overview,
            'overviewJobs' => $overviewJobs,
            'steps' => $steps,
            'underpantsSteps' => $underpantsSteps,
            'stationPanels' => $stationPanels,
            'staffs' => Staff::orderBy('name')->get(),
            'lots' => Lot::latest('id')->get(),
        ]);
    }

    public function create(): View
    {
        return $this->form(new SewingJob);
    }

    public function store(SewingRequest $request, SewingService $service): RedirectResponse
    {
        $validated = $request->validated();
        $job = $service->save($validated);
        $step = $validated['current_step'] ?? $job->current_step;

        return to_route('sewing-jobs.index', ['step' => $step])
            ->with('status', strtoupper(str_replace('_', ' ', $step)).' station handoff saved.')
            ->with('active_step', $step);
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
        $validated = $request->validated();
        $service->save($validated, $sewingJob);
        $step = $validated['current_step'] ?? $sewingJob->current_step;

        return to_route('sewing-jobs.index', ['step' => $step])
            ->with('status', strtoupper(str_replace('_', ' ', $step)).' station handoff updated.')
            ->with('active_step', $step);
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
        $sandoSteps = ['fold', 'lock', 'down', 'neck_join', 'neck_finishing', 'qc_phase_1', 'iron', 'qc_phase_2'];
        $underpantsFlow = SewingService::getUnderpantsFlow();
        $allowedSteps = array_unique(array_merge($sandoSteps, $underpantsFlow, ['over_lock', 'elastic_join']));

        abort_unless(in_array($step, $allowedSteps, true), 404);

        $isUnderpantsStep = in_array($step, $underpantsFlow, true);
        $steps = $isUnderpantsStep ? $underpantsFlow : $sandoSteps;

        $jobId = $job->id;

        $available = CuttingJobRoll::with(['job', 'lot'])->whereIn('cutting_type', ['body', 'cutting', 'neck'])->whereHas('job')->get()->groupBy(fn (CuttingJobRoll $row): string => $row->lot_id.':'.$row->color.':'.$row->dia)->map(function (Collection $group, string $key) use ($step, $jobId): ?array {
            $firstRoll = $group->first();
            if (! $firstRoll || ! $firstRoll->lot) {
                return null;
            }

            $isUnderpants = $firstRoll->lot->isUnderpants();

            if ($isUnderpants) {
                $cutRows = $group->where('cutting_type', 'cutting');
                if ($cutRows->isEmpty()) {
                    return null;
                }
                $cutPieces = Decimal::of(Decimal::sum($cutRows->map(fn (CuttingJobRoll $row): string => $row->pieces ?? (string) Decimal::of($row->dozens)->multipliedBy(12))));
                $bodyPieces = $cutPieces;
                $neckPieces = $cutPieces;
            } else {
                $body = $group->where('cutting_type', 'body');
                $neck = $group->where('cutting_type', 'neck');
                if ($body->isEmpty() || $neck->isEmpty()) {
                    return null;
                }
                $bodyPieces = Decimal::of(Decimal::sum($body->map(fn (CuttingJobRoll $row): string => $row->pieces ?? (string) Decimal::of($row->dozens)->multipliedBy(12))));
                $neckPieces = Decimal::of(Decimal::sum($neck->map(fn (CuttingJobRoll $row): string => $row->pieces ?? (string) Decimal::of($row->dozens)->multipliedBy(12))));
            }

            $remaining = SewingService::getAvailableForStep($firstRoll->lot, $key, $step, $jobId);

            if (! $remaining->isPositive()) {
                return null;
            }

            $availPiecesTotal = (int) $remaining->toInt();
            $availDozens = intdiv($availPiecesTotal, 12);
            $availPiecesRemainder = $availPiecesTotal % 12;

            $bodyRow = $group->firstWhere('cutting_type', 'body') ?? $firstRoll;
            $neckRow = $group->firstWhere('cutting_type', 'neck') ?? $firstRoll;

            return [
                'id' => $key,
                'group_key' => $key,
                'lot_id' => $firstRoll->lot_id,
                'lot' => $firstRoll->lot->lot_number,
                'product_type' => $firstRoll->lot->product_type,
                'color' => $firstRoll->color,
                'dia' => $firstRoll->dia,
                'body_pieces' => Decimal::scale($bodyPieces),
                'neck_pieces' => Decimal::scale($neckPieces),
                'capacity_pieces' => Decimal::scale($remaining),
                'total_pieces' => $availPiecesTotal,
                'pieces' => $availPiecesRemainder,
                'dozens' => $availDozens,
                'source_body_row_id' => $bodyRow->id,
                'source_neck_row_id' => $neckRow->id,
            ];
        })->filter()->values();

        $rows = old('rows', $job->exists ? $job->rows->map(function (SewingJobRow $row) use ($step): array {
            $isStandard = in_array($step, SewingService::STEPS, true);
            $dozens = $row->stage_data['dozens'] ?? ($isStandard ? (int) $row->{$step.'_dozens'} : 0);
            $pieces = $row->stage_data['pieces'] ?? ($isStandard ? (int) $row->{$step.'_pieces'} : 0);

            return [
                'group_key' => $row->group_key,
                'source_body_row_id' => $row->source_body_row_id,
                'source_neck_row_id' => $row->source_neck_row_id,
                $step.'_dozens' => $dozens,
                $step.'_pieces' => $pieces,
                'dozens' => $dozens,
                'pieces' => $pieces,
                'fold_dozens' => (int) $row->fold_dozens,
                'fold_pieces' => (int) $row->fold_pieces,
                'lock_dozens' => (int) $row->lock_dozens,
                'lock_pieces' => (int) $row->lock_pieces,
                'down_dozens' => (int) $row->down_dozens,
                'down_pieces' => (int) $row->down_pieces,
                'neck_join_dozens' => (int) $row->neck_join_dozens,
                'neck_join_pieces' => (int) $row->neck_join_pieces,
                'neck_finishing_dozens' => (int) $row->neck_finishing_dozens,
                'neck_finishing_pieces' => (int) $row->neck_finishing_pieces,
                'qc_phase_1_dozens' => (int) $row->qc_phase_1_dozens,
                'qc_phase_1_pieces' => (int) $row->qc_phase_1_pieces,
                'qc_phase_1_alter_pieces' => (int) $row->qc_phase_1_alter_pieces,
                'qc_phase_1_altered_pieces' => (int) $row->qc_phase_1_altered_pieces,
                'qc_phase_1_rejected_pieces' => (int) $row->qc_phase_1_rejected_pieces,
                'iron_dozens' => (int) $row->iron_dozens,
                'iron_pieces' => (int) $row->iron_pieces,
                'qc_phase_2_dozens' => (int) $row->qc_phase_2_dozens,
                'qc_phase_2_pieces' => (int) $row->qc_phase_2_pieces,
                'qc_phase_2_alter_pieces' => (int) $row->qc_phase_2_alter_pieces,
                'qc_phase_2_altered_pieces' => (int) $row->qc_phase_2_altered_pieces,
                'qc_phase_2_rejected_pieces' => (int) $row->qc_phase_2_rejected_pieces,
                'rejected_pieces' => (int) $row->rejected_pieces,
            ];
        })->all() : []);

        return ['job' => $job, 'step' => $step, 'steps' => $steps, 'staffs' => Staff::where('is_active', true)->orderBy('name')->get(), 'available' => $available, 'rows' => array_values($rows)];
    }
}
