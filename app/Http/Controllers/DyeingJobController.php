<?php

namespace App\Http\Controllers;

use App\Http\Requests\DyeingRequest;
use App\Models\Color;
use App\Models\DyeingJob;
use App\Modules\Dyeing\DyeingService;
use App\Modules\Dyeing\FabricAvailability;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DyeingJobController extends Controller
{
    public function index(Request $request): View
    {
        $jobs = DyeingJob::with(['lot', 'company', 'colorBlocks.color', 'colorBlocks.rows'])->when($request->q, fn ($query) => $query->where(function ($query) use ($request): void {
            $query->whereHas('lot', fn ($query) => $query->where('lot_number', 'like', '%'.$request->q.'%'))->orWhereHas('company', fn ($query) => $query->where('name', 'like', '%'.$request->q.'%'))
                ->orWhereHas('colorBlocks.color', fn ($query) => $query->where('name', 'like', '%'.$request->q.'%'));
        }))->orderBy(in_array($request->sort, ['entry_date', 'return_date', 'id'], true) ? $request->sort : 'id', $request->direction === 'asc' ? 'asc' : 'desc')->paginate(25)->withQueryString();

        return view('dyeing.index', compact('jobs'));
    }

    public function create(Request $request): View
    {
        return $this->form(new DyeingJob, $request->integer('lot_id') ?: null);
    }

    public function edit(DyeingJob $dyeingJob): View
    {
        return $this->form($dyeingJob->load(['lot', 'colorBlocks.color', 'colorBlocks.rows']), $dyeingJob->lot_id);
    }

    private function form(DyeingJob $job, ?int $preselectedId): View
    {
        $lots = app(FabricAvailability::class)->selectable($job->lot_id)->orderByDesc('id')->get(['id', 'lot_number']);
        $blocks = $job->exists ? $job->colorBlocks->map(fn ($block): array => ['color' => $block->color->name, 'rows' => $block->rows->map(fn ($row): array => ['dia' => $row->dia, 'given_fabric_kg' => $row->given_fabric_kg, 'returned_fabric_kg' => $row->returned_fabric_kg, 'rolls' => $row->rolls ?? []])->all()])->all() : [];
        $form = ['lotId' => (string) old('lot_id', $job->lot_id ?? $preselectedId ?? ''), 'blocks' => old('blocks', $blocks), 'jobId' => $job->id, 'colors' => Color::orderBy('name')->pluck('name')->all()];

        return view('dyeing.form', compact('job', 'lots', 'form'));
    }

    public function store(DyeingRequest $request, DyeingService $service): RedirectResponse
    {
        $job = $service->save($request->validated());

        return to_route('dyeing-jobs.show', $job)->with('status', 'Dyeing allocation saved.');
    }

    public function update(DyeingRequest $request, DyeingJob $dyeingJob, DyeingService $service): RedirectResponse
    {
        $service->save($request->validated(), $dyeingJob);

        return to_route('dyeing-jobs.show', $dyeingJob)->with('status', 'Dyeing job updated.');
    }

    public function show(DyeingJob $dyeingJob): View
    {
        return view('dyeing.show', ['job' => $dyeingJob->load(['lot', 'company.contacts', 'colorBlocks.color', 'colorBlocks.rows'])]);
    }

    public function destroy(DyeingJob $dyeingJob, DyeingService $service): RedirectResponse
    {
        $service->delete($dyeingJob);

        return to_route('dyeing-jobs.index')->with('status', 'Dyeing job deleted; fabric allocation released.');
    }
}
