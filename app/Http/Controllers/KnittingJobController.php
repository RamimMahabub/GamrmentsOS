<?php

namespace App\Http\Controllers;

use App\Http\Requests\KnittingRequest;
use App\Models\KnittingJob;
use App\Models\Setting;
use App\Models\Yarn;
use App\Modules\Knitting\KnittingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KnittingJobController extends Controller
{
    public function index(Request $request): View
    {
        $jobs = KnittingJob::with(['lot', 'company', 'yarnLinks.yarn', 'diaRows'])
            ->when($request->q, fn ($query) => $query->where(function ($query) use ($request): void {
                $query->whereHas('lot', fn ($query) => $query->where('lot_number', 'like', '%'.$request->q.'%'))
                    ->orWhereHas('company', fn ($query) => $query->where('name', 'like', '%'.$request->q.'%'));
            }))->orderBy(in_array($request->sort, ['entry_date', 'delivery_date', 'id'], true) ? $request->sort : 'id', $request->direction === 'asc' ? 'asc' : 'desc')->paginate(25)->withQueryString();

        return view('knitting.index', compact('jobs'));
    }

    public function create(): View
    {
        return $this->form(new KnittingJob);
    }

    public function edit(KnittingJob $knittingJob): View
    {
        return $this->form($knittingJob->load(['lot', 'yarnLinks', 'diaRows.yarnLines']));
    }

    private function form(KnittingJob $job): View
    {
        $yarns = Yarn::withSum('movements as available_kg', 'quantity_kg')->orderBy('name')->get();
        $rows = $job->exists ? $job->diaRows->map(fn ($row): array => ['dia' => (string) $row->dia, 'waste_percent' => $row->waste_percent, 'quantities' => $row->yarnLines->pluck('quantity_kg', 'yarn_id')->all()])->all() : [];
        $form = ['yarns' => $yarns->map(fn ($yarn): array => ['id' => (string) $yarn->id, 'name' => $yarn->name, 'type' => $yarn->yarn_type,
            'stock' => (string) ($yarn->available_kg ?? '0'), 'bagKg' => $yarn->bag_size_kg])->all(),
            'selected' => array_map('strval', old('yarn_ids', $job->exists ? $job->yarnLinks->pluck('yarn_id')->all() : [])),
            'rows' => old('rows', $rows), 'unit' => old('input_unit', $job->input_unit ?? Setting::getValue('default_weight_unit', 'lb')),
            'presets' => Setting::getValue('dia_presets', [32, 34, 36, 40]), 'date' => old('entry_date', $job->entry_date?->format('Y-m-d') ?? now()->toDateString()),
            'lotNumber' => $job->exists ? $job->lot->lot_number : null];

        return view('knitting.form', compact('job', 'form'));
    }

    public function store(KnittingRequest $request, KnittingService $service): RedirectResponse
    {
        $job = $service->save($request->validated());

        return to_route('knitting-jobs.show', $job)->with('status', 'Shipment assigned and yarn stock deducted.');
    }

    public function update(KnittingRequest $request, KnittingJob $knittingJob, KnittingService $service): RedirectResponse
    {
        $service->save($request->validated(), $knittingJob);

        return to_route('knitting-jobs.show', $knittingJob)->with('status', 'Shipment updated and stock reconciled.');
    }

    public function show(KnittingJob $knittingJob): View
    {
        return view('knitting.show', ['job' => $knittingJob->load(['lot', 'company.contacts', 'yarnLinks.yarn', 'diaRows.yarnLines.yarn'])]);
    }

    public function destroy(KnittingJob $knittingJob, KnittingService $service): RedirectResponse
    {
        $service->delete($knittingJob);

        return to_route('knitting-jobs.index')->with('status', 'Shipment deleted and stock restored.');
    }
}
