<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use App\Models\Yarn;
use App\Support\Decimal;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $yarns = Yarn::withSum('movements as available_kg', 'quantity_kg')->orderBy('name')->get();
        $totalKg = Decimal::scale(Decimal::sum($yarns->pluck('available_kg')->map(fn ($value): string => (string) ($value ?? '0'))));
        $counts = Lot::selectRaw('current_stage, count(*) as count')->groupBy('current_stage')->pluck('count', 'current_stage');
        $lots = Lot::with('knittingJob.company')->latest()->limit(10)->get();

        return view('dashboard', compact('yarns', 'totalKg', 'counts', 'lots'));
    }
}
