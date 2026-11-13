<?php

namespace App\Http\Controllers;

use App\Http\Requests\PackingMaterialRequest;
use App\Http\Requests\PackingStockRequest;
use App\Models\PackingMaterial;
use App\Support\Decimal;
use App\Support\PackingStockLedger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackingMaterialController extends Controller
{
    public function index(Request $request, PackingStockLedger $ledger): View
    {
        $materials = PackingMaterial::with('movements')
            ->when($request->category, fn ($q) => $q->where('category', $request->category))
            ->when($request->q, fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        $allMaterials = PackingMaterial::with('movements')->get();
        $totalValuation = '0';
        $lowStockCount = 0;

        foreach ($allMaterials as $m) {
            $bal = $ledger->balance($m);
            if ($m->cost_per_unit && Decimal::of($bal)->isPositive()) {
                $val = Decimal::of($bal)->multipliedBy($m->cost_per_unit);
                $totalValuation = (string) Decimal::of($totalValuation)->plus($val);
            }
            if (Decimal::of($bal)->isLessThan((string) $m->min_stock_alert)) {
                $lowStockCount++;
            }
        }

        return view('packing.materials.index', [
            'materials' => $materials,
            'ledger' => $ledger,
            'totalValuation' => $totalValuation,
            'lowStockCount' => $lowStockCount,
        ]);
    }

    public function create(): View
    {
        return view('packing.materials.form', [
            'material' => new PackingMaterial,
        ]);
    }

    public function store(PackingMaterialRequest $request, PackingStockLedger $ledger): RedirectResponse
    {
        $data = $request->validated();
        $material = PackingMaterial::create($data);

        if ($request->filled('initial_stock') && Decimal::of($request->input('initial_stock'))->isPositive()) {
            $ledger->write(
                $material,
                'opening',
                (string) $request->input('initial_stock'),
                now()->toDateString(),
                unitCost: $material->cost_per_unit,
                note: 'Initial opening stock'
            );
        }

        return to_route('packing-materials.index')->with('status', "Material '{$material->name}' created.");
    }

    public function edit(PackingMaterial $packingMaterial): View
    {
        return view('packing.materials.form', [
            'material' => $packingMaterial,
        ]);
    }

    public function update(PackingMaterialRequest $request, PackingMaterial $packingMaterial): RedirectResponse
    {
        $packingMaterial->update($request->validated());

        return to_route('packing-materials.index')->with('status', "Material '{$packingMaterial->name}' updated.");
    }

    public function stock(PackingStockRequest $request, PackingStockLedger $ledger): RedirectResponse
    {
        $data = $request->validated();
        $material = PackingMaterial::findOrFail($data['packing_material_id']);

        $ledger->write(
            $material,
            $data['type'],
            (string) $data['quantity'],
            $data['movement_date'],
            unitCost: $data['unit_cost'] ?? $material->cost_per_unit,
            note: $data['note'] ?? null
        );

        return back()->with('status', "Stock movement recorded for '{$material->name}'.");
    }
}
