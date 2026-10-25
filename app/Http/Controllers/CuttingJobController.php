<?php

namespace App\Http\Controllers;

use App\Http\Requests\CuttingRequest;
use App\Models\Color;
use App\Models\CuttingJob;
use App\Models\CuttingJobRoll;
use App\Models\Lot;
use App\Models\ReturnedRoll;
use App\Models\Staff;
use App\Support\CuttingService;
use App\Support\Decimal;
use App\Support\NeckAvailability;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CuttingJobController extends Controller
{
    private function type(Request $request): string
    {
        return $request->route()->defaults['cutting_type'];
    }

    private function check(Request $request, CuttingJob $cuttingJob): void
    {
        abort_unless($cuttingJob->cutting_type === $this->type($request), 404);
    }

    public function index(Request $request): View
    {
        $request->validate(['from' => ['nullable', 'date_format:Y-m-d'], 'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'], 'staff_id' => ['nullable', 'integer'], 'lot_id' => ['nullable', 'integer'], 'color' => ['nullable', 'string', 'max:255']]);
        $type = $this->type($request);
        $jobs = CuttingJob::with(['staff', 'rows.lot', 'rows.sourceBodyRow', 'rows.roll.dyeingRow.colorBlock.color'])->where('cutting_type', $type)->when($request->from, fn ($q) => $q->whereDate('cutting_date', '>=', $request->from))->when($request->to, fn ($q) => $q->whereDate('cutting_date', '<=', $request->to))->when($request->staff_id, fn ($q) => $q->where('staff_id', $request->staff_id))->when($request->lot_id || $request->color, fn ($q) => $q->whereHas('rows', fn ($q) => $q->when($request->lot_id, fn ($q) => $q->where('lot_id', $request->lot_id))->when($request->color, fn ($q) => $q->where('color', $request->color))))->orderByDesc('cutting_date')->orderByDesc('id')->paginate(25)->withQueryString();

        return view('cutting.index', ['jobs' => $jobs, 'type' => $type, 'staffs' => Staff::orderBy('name')->get(), 'lots' => Lot::orderByDesc('id')->get(), 'colors' => Color::orderBy('name')->pluck('name')]);
    }

    public function create(Request $request): View
    {
        return $this->form($request, new CuttingJob);
    }

    public function edit(Request $request, CuttingJob $cuttingJob): View
    {
        $this->check($request, $cuttingJob);

        return $this->form($request, $cuttingJob);
    }

    private function form(Request $request, CuttingJob $job): View
    {
        $type = $this->type($request);
        $available = $type === 'neck' ? collect() : ReturnedRoll::with(['dyeingJob.lot', 'dyeingRow.colorBlock.color'])->where('is_available', true)->whereHas('dyeingJob')->whereDoesntHave('cuttingRows', fn ($q) => $q->where('cutting_type', $type)->when($job->exists, fn ($q) => $q->where('cutting_job_id', '!=', $job->id)))->orderBy('number')->get()->map(fn ($roll): array => ['id' => $roll->id, 'number' => $roll->number, 'lot_id' => $roll->dyeingJob->lot_id, 'lot' => $roll->dyeingJob->lot->lot_number, 'color' => $roll->dyeingRow->colorBlock->color->name, 'color_hex' => $roll->dyeingRow->colorBlock->color->hex, 'dia' => $roll->dyeingRow->dia, 'weight_kg' => $roll->weight_kg])->values();
        if ($type === 'neck') {
            $availability = app(NeckAvailability::class);
            $available = CuttingJobRoll::with(['job.staff', 'lot', 'roll.dyeingRow.colorBlock.color',
                'roll.cuttingRows' => fn ($query) => $query->where('cutting_type', 'neck')->when($job->exists, fn ($query) => $query->where('cutting_job_id', '!=', $job->id)),
            ])->where('cutting_type', 'body')->where('dozens', '>', 0)->where('cut_weight_kg', '>', 0)->whereHas('job')
                ->whereHas('roll', fn ($query) => $query->where('is_available', true)->whereHas('dyeingJob'))
                ->orderBy('cutting_job_id')->orderBy('id')->get()->map(fn ($body): array => [
                    'id' => $body->roll_id, 'source_body_row_id' => $body->id, 'body_job_id' => $body->cutting_job_id, 'body_url' => route('cutting.body.show', $body->cutting_job_id), 'body_date' => $body->job->cutting_date->format('d/m/Y'), 'body_staff' => $body->job->staff->name,
                    'number' => $body->roll->number, 'lot_id' => $body->lot_id, 'lot' => $body->lot->lot_number, 'color' => $body->color, 'color_hex' => $body->roll->dyeingRow->colorBlock->color->hex, 'dia' => $body->dia,
                    ...$availability->remaining($body, $body->roll->cuttingRows),
                ])->filter(fn (array $body): bool => Decimal::of($body['dozens'])->isPositive())->values();
        }
        $rows = old('rows', $job->exists ? $job->rows->map(fn ($row): array => $row->only(['roll_id', 'source_body_row_id', 'dozens', 'weight_per_dozen', 'weight_unit', 'remaining_fabric']))->all() : []);
        if ($type === 'neck') {
            $rows = collect($rows)->map(function (array $row) use ($available): array {
                $source = $available->firstWhere('source_body_row_id', $row['source_body_row_id'] ?? null);
                if ($source) {
                    $row['roll_id'] = $source['id'];
                }

                return $row;
            })->all();
        }

        return view('cutting.form', ['job' => $job, 'type' => $type, 'staffs' => Staff::where('is_active', true)->orderBy('name')->get(), 'form' => ['isNeck' => $type === 'neck', 'canRecordResults' => $job->exists, 'available' => $available, 'rows' => array_values($rows), 'labels' => config('cutting.labels')]]);
    }

    public function store(CuttingRequest $request, CuttingService $service): RedirectResponse
    {
        $type = $this->type($request);
        $job = $service->save($request->validated(), $type);

        return to_route('cutting.'.$type.'.show', $job)->with('status', 'Cutting job saved.');
    }

    public function update(CuttingRequest $request, CuttingJob $cuttingJob, CuttingService $service): RedirectResponse
    {
        $this->check($request, $cuttingJob);
        $service->save($request->validated(), $this->type($request), $cuttingJob);

        return to_route('cutting.'.$this->type($request).'.show', $cuttingJob)->with('status', 'Cutting job updated.');
    }

    public function show(Request $request, CuttingJob $cuttingJob): View
    {
        $this->check($request, $cuttingJob);

        return view('cutting.show', ['job' => $cuttingJob->load(['staff', 'rows.roll.dyeingRow.colorBlock.color', 'rows.lot', 'rows.sourceBodyRow.job', 'rows.neckRows.job', 'updatedBy']), 'type' => $this->type($request)]);
    }

    public function destroy(Request $request, CuttingJob $cuttingJob, CuttingService $service): RedirectResponse
    {
        $this->check($request, $cuttingJob);
        $service->delete($cuttingJob);

        return to_route('cutting.'.$this->type($request).'.index')->with('status', $this->type($request) === 'neck' ? 'Cutting job deleted. Body batches released for neck cutting.' : 'Cutting job deleted. Rolls released for this section.');
    }
}
