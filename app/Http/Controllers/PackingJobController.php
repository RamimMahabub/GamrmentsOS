<?php

namespace App\Http\Controllers;

use App\Http\Requests\PackingJobRequest;
use App\Models\Lot;
use App\Models\PackingJob;
use App\Models\PackingMaterial;
use App\Models\Staff;
use App\Support\Decimal;
use App\Support\PackingService;
use App\Support\PackingStockLedger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackingJobController extends Controller
{
    public function index(Request $request, PackingStockLedger $ledger): View
    {
        $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'staff_id' => ['nullable', 'integer'],
            'lot_id' => ['nullable', 'integer'],
        ]);

        $query = PackingJob::with(['staff', 'rows.lot'])
            ->when($request->from, fn ($q) => $q->whereDate('packing_date', '>=', $request->from))
            ->when($request->to, fn ($q) => $q->whereDate('packing_date', '<=', $request->to))
            ->when($request->staff_id, fn ($q) => $q->where('staff_id', $request->staff_id))
            ->when($request->lot_id, fn ($q) => $q->whereHas('rows', fn ($q) => $q->where('lot_id', $request->lot_id)));

        $overviewJobs = (clone $query)->get();
        $jobs = (clone $query)->latest('packing_date')->latest('id')->paginate(25)->withQueryString();

        $totalPieces = $overviewJobs->flatMap->rows->sum('total_pieces');
        $totalPackets = $overviewJobs->flatMap->rows->sum('packets');
        $totalDozens = Decimal::scale(Decimal::sum($overviewJobs->flatMap->rows->pluck('dozens')));

        $materials = PackingMaterial::with('movements')->orderBy('category')->orderBy('name')->get();
        $materialsSummary = $materials->map(fn (PackingMaterial $m): array => [
            'id' => $m->id,
            'name' => $m->name,
            'category' => $m->category,
            'size_code' => $m->size_code,
            'unit' => $m->unit,
            'balance' => $ledger->balance($m),
            'min_stock_alert' => $m->min_stock_alert,
            'is_low' => Decimal::of($ledger->balance($m))->isLessThan((string) $m->min_stock_alert),
        ]);

        $lowStockCount = $materialsSummary->where('is_low', true)->count();

        return view('packing.index', [
            'jobs' => $jobs,
            'overviewJobs' => $overviewJobs,
            'totalPieces' => $totalPieces,
            'totalPackets' => $totalPackets,
            'totalDozens' => $totalDozens,
            'materialsSummary' => $materialsSummary,
            'lowStockCount' => $lowStockCount,
            'staffs' => Staff::orderBy('name')->get(),
            'lots' => Lot::latest('id')->get(),
        ]);
    }

    public function create(PackingService $service): View
    {
        return $this->form(new PackingJob, $service);
    }

    public function store(PackingJobRequest $request, PackingService $service): RedirectResponse
    {
        $job = $service->save($request->validated());

        return to_route('packing-jobs.show', $job)->with('status', 'Packing job recorded.');
    }

    public function show(PackingJob $packingJob): View
    {
        return view('packing.show', [
            'job' => $packingJob->load(['staff', 'rows.lot', 'materialMovements.material']),
        ]);
    }

    public function edit(PackingJob $packingJob, PackingService $service): View
    {
        return $this->form($packingJob, $service);
    }

    public function update(PackingJobRequest $request, PackingJob $packingJob, PackingService $service): RedirectResponse
    {
        $service->save($request->validated(), $packingJob);

        return to_route('packing-jobs.show', $packingJob)->with('status', 'Packing job updated.');
    }

    public function destroy(PackingJob $packingJob, PackingService $service): RedirectResponse
    {
        $service->delete($packingJob);

        return to_route('packing-jobs.index')->with('status', 'Packing job deleted.');
    }

    private function form(PackingJob $job, PackingService $service): View
    {
        $available = $service->getAvailableBatches($job);

        $rows = old('rows', $job->exists ? $job->rows->map(fn ($row): array => [
            'group_key' => $row->group_key,
            'size' => $row->size,
            'dozens' => (int) $row->dozens,
            'pieces' => (int) $row->pieces,
            'total_pieces' => (int) $row->total_pieces,
            'rejected_pieces' => (int) $row->rejected_pieces,
        ])->all() : []);

        $materials = PackingMaterial::where('is_active', true)->with('movements')->get()->map(fn ($m): array => [
            'id' => $m->id,
            'name' => $m->name,
            'category' => $m->category,
            'size_code' => $m->size_code,
            'balance' => $m->available_stock,
        ]);

        $lots = Lot::with('packingMaterials')->whereIn('id', $available->pluck('lot_id')->unique())->get()->map(fn ($l): array => [
            'id' => $l->id,
            'lot_number' => $l->lot_number,
            'selected_material_ids' => $l->packingMaterials->pluck('id')->all(),
        ]);

        return view('packing.form', [
            'job' => $job,
            'staffs' => Staff::where('is_active', true)->orderBy('name')->get(),
            'available' => $available,
            'rows' => array_values($rows),
            'materials' => $materials,
            'lots' => $lots,
        ]);
    }
}
