<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LotDispatchController extends Controller
{
    /**
     * Manually dispatch a production lot.
     */
    public function dispatch(Request $request, Lot $lot): RedirectResponse
    {
        $validated = $request->validate([
            'dispatch_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $lot->dispatch($validated['dispatch_notes'] ?? null);

        return back()->with('status', "Lot {$lot->lot_number} has been marked as Dispatched (Production finished).");
    }

    /**
     * Undispatch a production lot.
     */
    public function undispatch(Lot $lot): RedirectResponse
    {
        $lot->undispatch();

        return back()->with('status', "Lot {$lot->lot_number} dispatch status reverted to {$lot->current_stage}.");
    }
}
