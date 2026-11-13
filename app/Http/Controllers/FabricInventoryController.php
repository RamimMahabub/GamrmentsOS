<?php

namespace App\Http\Controllers;

use App\Http\Requests\MarketRollLotRequest;
use App\Models\Color;
use App\Models\Lot;
use App\Models\ReturnedRoll;
use App\Models\Setting;
use App\Support\Decimal;
use App\Support\MarketRollService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FabricInventoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('q');
        $sort = $request->query('sort', 'latest');
        $status = $request->query('status', 'all'); // 'all', 'available', 'exhausted'

        // Retrieve lots that have completed/returned dyeing jobs OR market rolls
        $lotsQuery = Lot::where(function ($query): void {
            $query->whereHas('dyeingJobs.colorBlocks.rows', function ($q): void {
                $q->whereNotNull('returned_fabric_kg');
            })->orWhere('fabric_source', 'market_purchase');
        })->with([
            'dyeingJobs.company',
            'dyeingJobs.colorBlocks.color',
            'dyeingJobs.colorBlocks.rows',
            'returnedRolls.color',
            'cuttingRows',
        ]);

        if ($search) {
            $lotsQuery->where(function ($q) use ($search): void {
                $q->where('lot_number', 'like', "%{$search}%")
                    ->orWhere('supplier_name', 'like', "%{$search}%")
                    ->orWhereHas('dyeingJobs.company', fn ($sub) => $sub->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('dyeingJobs.colorBlocks.color', fn ($sub) => $sub->where('name', 'like', "%{$search}%"));
            });
        }

        $allLots = $lotsQuery->orderByDesc('id')->get();

        // Calculate metrics for each lot
        $lotSummaries = $allLots->map(function (Lot $lot): array {
            if ($lot->fabric_source === 'market_purchase') {
                $returnedKg = Decimal::scale(Decimal::sum($lot->returnedRolls->pluck('weight_kg')));
                $dyeingHouses = collect($lot->returnedRolls->pluck('supplier_name')->filter()->unique()->values());
                $colors = $lot->returnedRolls->map(fn ($r) => [
                    'name' => $r->color?->name ?? 'Unknown',
                    'hex' => $r->color?->hex ?? '#cbd9b6',
                ])->unique('name')->values();
                $dias = $lot->returnedRolls->pluck('dia')->filter()->unique()->sort()->values();
            } else {
                $dyeingRows = $lot->dyeingJobs->flatMap->colorBlocks->flatMap->rows->whereNotNull('returned_fabric_kg');
                $returnedKg = Decimal::scale(Decimal::sum($dyeingRows->pluck('returned_fabric_kg')));
                $dyeingHouses = $lot->dyeingJobs->pluck('company.name')->unique()->values();
                $colors = $lot->dyeingJobs->flatMap->colorBlocks->map(fn ($b) => [
                    'name' => $b->color->name,
                    'hex' => $b->color->hex ?? '#cbd9b6',
                ])->unique('name')->values();
                $dias = $dyeingRows->pluck('dia')->unique()->sort()->values();
            }

            // Cut fabric calculation (from cutting job rows)
            $cutWeightKg = Decimal::scale(Decimal::sum($lot->cuttingRows->whereIn('cutting_type', ['body', 'cutting', 'rib'])->pluck('roll_weight_kg')));
            $remainingKg = Decimal::scale(Decimal::of($returnedKg)->minus($cutWeightKg));
            if (Decimal::of($remainingKg)->isNegative()) {
                $remainingKg = '0.000';
            }

            // Rolls analysis
            $rolls = ReturnedRoll::where(function ($q) use ($lot): void {
                $q->where('lot_id', $lot->id)
                    ->orWhereHas('dyeingJob', fn ($sq) => $sq->where('lot_id', $lot->id));
            })->with('cuttingRows')->get();
            $totalRolls = $rolls->count();
            $availableRolls = 0;
            $exhaustedRolls = 0;
            $partialRolls = 0;

            foreach ($rolls as $roll) {
                $used = Decimal::sum($roll->cuttingRows->pluck('roll_weight_kg'));
                $rem = Decimal::of($roll->weight_kg)->minus($used);
                if (! $rem->isPositive()) {
                    $exhaustedRolls++;
                } elseif (Decimal::of($used)->isPositive()) {
                    $partialRolls++;
                    $availableRolls++;
                } else {
                    $availableRolls++;
                }
            }

            return [
                'lot' => $lot,
                'returned_kg' => $returnedKg,
                'cut_weight_kg' => $cutWeightKg,
                'remaining_kg' => $remainingKg,
                'is_exhausted' => Decimal::of($remainingKg)->isZero() && $totalRolls > 0,
                'total_rolls' => $totalRolls,
                'available_rolls' => $availableRolls,
                'exhausted_rolls' => $exhaustedRolls,
                'partial_rolls' => $partialRolls,
                'colors' => $colors,
                'dias' => $dias,
                'dyeing_houses' => $lot->dyeingJobs->pluck('company.name')->unique()->values(),
                'latest_return_date' => $lot->dyeingJobs->max('return_date'),
            ];
        });

        if ($status === 'available') {
            $lotSummaries = $lotSummaries->filter(fn ($item): bool => ! $item['is_exhausted']);
        } elseif ($status === 'exhausted') {
            $lotSummaries = $lotSummaries->filter(fn ($item): bool => $item['is_exhausted']);
        }

        // Global KPIs
        $totalFabricReturnedKg = Decimal::scale(Decimal::sum($lotSummaries->pluck('returned_kg')));
        $totalFabricAvailableKg = Decimal::scale(Decimal::sum($lotSummaries->pluck('remaining_kg')));
        $totalFabricCutKg = Decimal::scale(Decimal::sum($lotSummaries->pluck('cut_weight_kg')));
        $totalAvailableRolls = $lotSummaries->sum('available_rolls');
        $totalExhaustedRolls = $lotSummaries->sum('exhausted_rolls');

        // Paginate manually or pass collections
        $lotsCount = $lotSummaries->count();

        return view('fabric.index', compact(
            'lotSummaries',
            'search',
            'status',
            'sort',
            'totalFabricReturnedKg',
            'totalFabricAvailableKg',
            'totalFabricCutKg',
            'totalAvailableRolls',
            'totalExhaustedRolls',
            'lotsCount'
        ));
    }

    public function createMarketLot(MarketRollService $service): View
    {
        return view('fabric.create_market_lot', [
            'colorOptions' => Color::orderBy('name')->pluck('name'),
            'diaPresets' => Setting::getValue('dia_presets', [18, 20, 22, 24, 26, 28, 30, 32, 34, 36, 40]),
            'defaultWeightUnit' => Setting::getValue('default_weight_unit', 'kg'),
            'currency' => Setting::getValue('currency_label', 'BDT'),
            'previewBoxerLot' => $service->generateLotNumber('boxer', now()->toDateString()),
            'previewUnderwearLot' => $service->generateLotNumber('underwear', now()->toDateString()),
        ]);
    }

    public function storeMarketLot(MarketRollLotRequest $request, MarketRollService $service): RedirectResponse
    {
        $lot = $service->createLotWithRolls($request->validated());

        return to_route('fabric.show', $lot)->with('status', "Underpants Lot {$lot->lot_number} created with market rolls.");
    }

    public function show(Lot $lot): View
    {
        $lot->load([
            'dyeingJobs.company',
            'dyeingJobs.colorBlocks.color',
            'dyeingJobs.colorBlocks.rows',
            'returnedRolls.color',
            'cuttingRows.job.staff',
        ]);

        $rolls = ReturnedRoll::where(function ($q) use ($lot): void {
            $q->where('lot_id', $lot->id)
                ->orWhereHas('dyeingJob', fn ($sq) => $sq->where('lot_id', $lot->id));
        })
            ->with([
                'dyeingRow.colorBlock.color',
                'dyeingJob.company',
                'color',
                'cuttingRows.job.staff',
            ])
            ->orderBy('number')
            ->get();

        $rollsData = $rolls->map(function (ReturnedRoll $roll): array {
            $usedWeight = Decimal::scale(Decimal::sum($roll->cuttingRows->pluck('roll_weight_kg')));
            $remainingWeight = Decimal::scale(Decimal::of($roll->weight_kg)->minus($usedWeight));
            if (Decimal::of($remainingWeight)->isNegative()) {
                $remainingWeight = '0.000';
            }
            $isCut = Decimal::of($remainingWeight)->isZero();
            $isPartial = ! $isCut && Decimal::of($usedWeight)->isPositive();

            return [
                'roll' => $roll,
                'weight_kg' => $roll->weight_kg,
                'used_weight_kg' => $usedWeight,
                'remaining_weight_kg' => $remainingWeight,
                'is_cut' => $isCut,
                'is_partial' => $isPartial,
                'cuts_count' => $roll->cuttingRows->count(),
            ];
        });

        // Group rolls by Color and DIA
        $grouped = $rollsData->groupBy(function ($item): string {
            $color = $item['roll']->getEffectiveColor()?->name ?? 'Unknown';
            $dia = $item['roll']->getEffectiveDia() ?? '—';

            return $color.'__'.$dia;
        })->map(function ($group, $key): array {
            [$color, $dia] = explode('__', $key);
            $totalRolls = $group->count();
            $availableRolls = $group->where('is_cut', false)->count();
            $totalWeight = Decimal::scale(Decimal::sum($group->pluck('weight_kg')));
            $usedWeight = Decimal::scale(Decimal::sum($group->pluck('used_weight_kg')));
            $remainingWeight = Decimal::scale(Decimal::sum($group->pluck('remaining_weight_kg')));

            return [
                'color' => $color,
                'hex' => $group->first()['roll']->getEffectiveColor()?->hex ?? '#cbd9b6',
                'dia' => $dia,
                'total_rolls' => $totalRolls,
                'available_rolls' => $availableRolls,
                'total_weight_kg' => $totalWeight,
                'used_weight_kg' => $usedWeight,
                'remaining_weight_kg' => $remainingWeight,
                'rolls' => $group,
            ];
        })->values();

        $totalLotReturnedKg = Decimal::scale(Decimal::sum($rollsData->pluck('weight_kg')));
        $totalLotUsedKg = Decimal::scale(Decimal::sum($rollsData->pluck('used_weight_kg')));
        $totalLotRemainingKg = Decimal::scale(Decimal::sum($rollsData->pluck('remaining_weight_kg')));

        return view('fabric.show', compact(
            'lot',
            'grouped',
            'rollsData',
            'totalLotReturnedKg',
            'totalLotUsedKg',
            'totalLotRemainingKg'
        ));
    }
}
