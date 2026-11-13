<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use App\Support\LotCostCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LotCostController extends Controller
{
    public function show(Lot $lot): View
    {
        $costSheet = LotCostCalculator::forLot($lot);

        return view('lots.cost_sheet', [
            'lot' => $lot,
            'costSheet' => $costSheet,
        ]);
    }

    public function updateRates(Request $request, Lot $lot): RedirectResponse
    {
        $data = $request->validate([
            'carrying_cost_per_dozen' => ['nullable', 'numeric', 'min:0'],
            'thread_cost_per_dozen' => ['nullable', 'numeric', 'min:0'],
        ]);

        $lot->update($data);

        return redirect()->route('lots.cost-sheet', $lot)
            ->with('status', 'Lot custom cost rates updated.');
    }
}
