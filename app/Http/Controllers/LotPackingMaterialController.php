<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use App\Models\PackingMaterial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LotPackingMaterialController extends Controller
{
    /**
     * Display a master manager for assigning packaging materials to lots.
     */
    public function index(Request $request): View
    {
        $lots = Lot::with(['packingMaterials'])
            ->latest('id')
            ->get();

        $materials = PackingMaterial::where('is_active', true)
            ->with('movements')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $selectedLotId = $request->query('lot_id', $lots->first()?->id);
        $activeLot = $selectedLotId ? $lots->firstWhere('id', (int) $selectedLotId) : $lots->first();

        return view('packing.materials.lots', [
            'lots' => $lots,
            'activeLot' => $activeLot,
            'materials' => $materials,
        ]);
    }

    /**
     * Update assigned packaging materials for a specific lot.
     */
    public function update(Request $request, Lot $lot): RedirectResponse
    {
        $validated = $request->validate([
            'material_ids' => ['nullable', 'array'],
            'material_ids.*' => ['integer', 'exists:packing_materials,id'],
        ]);

        $materialIds = $validated['material_ids'] ?? [];
        $lot->packingMaterials()->sync($materialIds);

        return back()->with('status', 'Packaging materials configuration saved for lot '.$lot->lot_number.'.');
    }
}
