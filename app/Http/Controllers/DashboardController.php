<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CuttingJob;
use App\Models\Lot;
use App\Models\ReturnedRoll;
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
        $lots = Lot::with(['knittingJob.company', 'dyeingJobs.company'])->latest()->limit(8)->get();

        // Additional manufacturing KPIs and pipeline metrics
        $activePartnersCount = Company::where('is_active', true)->count();
        $totalLotsCount = Lot::count();
        $availableRollsCount = ReturnedRoll::where('is_available', true)->count();
        $totalRollsCount = ReturnedRoll::count();
        $cuttingJobsCount = CuttingJob::count();

        return view('dashboard', compact(
            'yarns',
            'totalKg',
            'counts',
            'lots',
            'activePartnersCount',
            'totalLotsCount',
            'availableRollsCount',
            'totalRollsCount',
            'cuttingJobsCount'
        ));
    }
}
