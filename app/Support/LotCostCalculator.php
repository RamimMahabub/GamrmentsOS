<?php

namespace App\Support;

use App\Models\FactoryExpense;
use App\Models\Lot;
use App\Models\PackingJobRow;
use App\Models\PackingMaterial;
use App\Models\Setting;
use Brick\Math\RoundingMode;

final class LotCostCalculator
{
    /**
     * Compute full cost sheet and metrics for a given lot.
     *
     * @return array<string, mixed>
     */
    public static function forLot(Lot $lot): array
    {
        $lot->loadMissing([
            'knittingJob.company',
            'knittingJob.diaRows.yarnLines.yarn',
            'dyeingJobs.company',
            'dyeingJobs.colorBlocks.color',
            'dyeingJobs.colorBlocks.rows',
            'cuttingRows.job',
            'cuttingRows.roll.dyeingRow.colorBlock.color',
            'sewingRows.job',
            'packingRows.job',
            'expenses',
        ]);

        $missingRates = [];

        // 1. Finished Units (QC Phase 2 passed / packed)
        $packingRows = $lot->packingRows;
        $sewingRows = $lot->sewingRows;

        $packedPieces = (int) $packingRows->sum('total_pieces');
        $qc2Pieces = (int) (string) Decimal::sum(
            $sewingRows->map(fn ($r): string => (string) Decimal::of($r->qc_phase_2_dozens)->multipliedBy(12)->plus($r->qc_phase_2_pieces))
        );

        // Good finished pieces priority: Packing count if packing occurred, else QC Phase 2
        $finishedPieces = $packedPieces > 0 ? $packedPieces : $qc2Pieces;
        $finishedDozens = $finishedPieces > 0 ? CuttingQuantity::dozens((string) $finishedPieces) : '0';

        // 2. Raw Material: Fabric / Yarn Cost
        $yarnCost = '0';
        $yarnDetails = [];
        $marketFabricCost = '0';
        $marketFabricDetails = [];
        $knittingJob = $lot->knittingJob;

        if ($lot->fabric_source === 'market_purchase') {
            $rolls = $lot->returnedRolls()->with('color')->get();
            foreach ($rolls as $roll) {
                $qty = $roll->weight_kg ?? '0';
                $price = $roll->purchase_price_per_kg;
                if (! $price || Decimal::of($price)->isZero()) {
                    $missingRates[] = 'Purchase price per kg for Roll '.$roll->number;
                    $lineCost = '0';
                } else {
                    $lineCost = Decimal::scale(Decimal::of($qty)->multipliedBy($price), 2);
                }
                $marketFabricCost = Decimal::scale(Decimal::of($marketFabricCost)->plus($lineCost), 2);
                $marketFabricDetails[] = [
                    'roll_number' => $roll->number,
                    'color' => $roll->color?->name ?? 'Unknown',
                    'dia' => $roll->dia,
                    'weight_kg' => $qty,
                    'price_per_kg' => $price ?? '0',
                    'cost' => $lineCost,
                ];
            }
        } else {
            if ($knittingJob) {
                foreach ($knittingJob->diaRows as $diaRow) {
                    foreach ($diaRow->yarnLines as $line) {
                        $yarn = $line->yarn;
                        $price = $yarn?->price_per_kg;
                        $qty = $line->quantity_kg ?? '0';

                        if (! $price || Decimal::of($price)->isZero()) {
                            $missingRates[] = 'Yarn price for '.($yarn->name ?? 'DIA '.$diaRow->dia);
                            $lineCost = '0';
                        } else {
                            $lineCost = Decimal::scale(Decimal::of($qty)->multipliedBy($price), 2);
                        }

                        $yarnCost = Decimal::scale(Decimal::of($yarnCost)->plus($lineCost), 2);

                        $yarnDetails[] = [
                            'dia' => $diaRow->dia,
                            'yarn_id' => $yarn?->id,
                            'yarn_name' => $yarn?->name ?? 'Unknown Yarn',
                            'yarn_type' => $yarn?->yarn_type ?? '',
                            'quantity_kg' => $qty,
                            'price_per_kg' => $price ?? '0',
                            'cost' => $lineCost,
                        ];
                    }
                }
            }
        }

        // 3. Mill Labor: Knitting Cost
        $knittingCost = '0';
        $knittingDetails = [];
        if ($knittingJob) {
            $kRate = $knittingJob->rate_per_kg
                ?: $knittingJob->company?->default_knitting_rate_per_kg
                ?: Setting::getValue('default_knitting_rate_per_kg', '0');

            if (! $kRate || Decimal::of($kRate)->isZero()) {
                $missingRates[] = 'Knitting rate per kg ('.$knittingJob->company?->name.')';
            }

            foreach ($knittingJob->diaRows as $diaRow) {
                // Charge basis: output fabric kg
                $weight = $diaRow->output_fabric_kg ?? '0';
                $lineCost = Decimal::scale(Decimal::of($weight)->multipliedBy($kRate ?: '0'), 2);
                $knittingCost = Decimal::scale(Decimal::of($knittingCost)->plus($lineCost), 2);

                $knittingDetails[] = [
                    'dia' => $diaRow->dia,
                    'output_kg' => $weight,
                    'rate_per_kg' => $kRate ?: '0',
                    'cost' => $lineCost,
                ];
            }
        }

        // 4. Mill Labor: Dyeing Cost
        $dyeingCost = '0';
        $dyeingDetails = [];
        foreach ($lot->dyeingJobs as $dJob) {
            $dRate = $dJob->rate_per_kg
                ?: $dJob->company?->default_dyeing_rate_per_kg
                ?: Setting::getValue('default_dyeing_rate_per_kg', '0');

            if (! $dRate || Decimal::of($dRate)->isZero()) {
                $missingRates[] = 'Dyeing rate per kg ('.$dJob->company?->name.')';
            }

            foreach ($dJob->colorBlocks as $block) {
                $colorName = $block->color?->name ?? 'Unknown';
                foreach ($block->rows as $dRow) {
                    // Charge basis: returned fabric kg
                    $weight = $dRow->returned_fabric_kg ?? $dRow->given_fabric_kg ?? '0';
                    $lineCost = Decimal::scale(Decimal::of($weight)->multipliedBy($dRate ?: '0'), 2);
                    $dyeingCost = Decimal::scale(Decimal::of($dyeingCost)->plus($lineCost), 2);

                    $dyeingDetails[] = [
                        'color' => $colorName,
                        'dia' => $dRow->dia,
                        'returned_kg' => $dRow->returned_fabric_kg ?? '0',
                        'given_kg' => $dRow->given_fabric_kg,
                        'rate_per_kg' => $dRate ?: '0',
                        'cost' => $lineCost,
                    ];
                }
            }
        }

        // 5. Factory Labor: Cutting Cost
        $cuttingCost = '0';
        $cuttingDetails = [];
        $cuttingRates = Setting::getValue('cutting_rates', ['body' => '0', 'cutting' => '0', 'neck' => '0', 'rib' => '0']);

        $cuttingRowsGrouped = $lot->cuttingRows->groupBy(fn ($r) => $r->job?->cutting_type ?? 'body');
        foreach (['body', 'cutting', 'neck', 'rib'] as $cType) {
            $rows = $cuttingRowsGrouped->get($cType, collect());
            if ($rows->isEmpty()) {
                continue;
            }

            $totalDozens = Decimal::sum($rows->pluck('dozens'));
            $rate = $rows->first()?->job?->rate_per_dozen
                ?: ($cuttingRates[$cType] ?? ($cType === 'cutting' ? ($cuttingRates['body'] ?? '0') : '0'));

            if (! $rate || Decimal::of($rate)->isZero()) {
                $missingRates[] = ucfirst($cType).' cutting rate per dozen';
            }

            $typeCost = Decimal::scale(Decimal::of($totalDozens)->multipliedBy($rate ?: '0'), 2);
            $cuttingCost = Decimal::scale(Decimal::of($cuttingCost)->plus($typeCost), 2);

            $cuttingDetails[$cType] = [
                'type' => $cType,
                'dozens' => $totalDozens,
                'pieces' => (string) Decimal::of($totalDozens)->multipliedBy(12),
                'rate_per_dozen' => $rate ?: '0',
                'cost' => $typeCost,
            ];
        }

        // 6. Factory Labor: Sewing Cost
        $sewingCost = '0';
        $sewingDetails = [];
        $sewingRates = Setting::getValue('sewing_rates', [
            'fold' => '0', 'lock' => '0', 'down' => '0', 'neck_join' => '0',
            'neck_finishing' => '0', 'qc_phase_1' => '0', 'iron' => '0', 'qc_phase_2' => '0',
        ]);

        if ($lot->isUnderpants()) {
            $underpantsSteps = [];
            foreach (UnderpantsSewingConfig::allSubSteps() as $s) {
                $underpantsSteps[$s['key']] = [
                    'label' => $s['label'],
                    'rate' => $s['rate'] ?? '0',
                ];
            }
            $underpantsSteps['qc_phase_1'] = ['label' => 'QC Phase 1', 'rate' => $sewingRates['qc_phase_1'] ?? '0'];
            $underpantsSteps['iron'] = ['label' => 'Iron', 'rate' => $sewingRates['iron'] ?? '0'];
            $underpantsSteps['qc_phase_2'] = ['label' => 'QC Phase 2', 'rate' => $sewingRates['qc_phase_2'] ?? '0'];
            $sewingSteps = $underpantsSteps;
        } else {
            $sewingSteps = [
                'fold' => ['label' => 'Fold', 'rate' => $sewingRates['fold'] ?? '0'],
                'lock' => ['label' => 'Lock', 'rate' => $sewingRates['lock'] ?? '0'],
                'down' => ['label' => 'Down', 'rate' => $sewingRates['down'] ?? '0'],
                'neck_join' => ['label' => 'Neck Join', 'rate' => $sewingRates['neck_join'] ?? '0'],
                'neck_finishing' => ['label' => 'Neck Finishing', 'rate' => $sewingRates['neck_finishing'] ?? '0'],
                'qc_phase_1' => ['label' => 'QC Phase 1', 'rate' => $sewingRates['qc_phase_1'] ?? '0'],
                'iron' => ['label' => 'Iron', 'rate' => $sewingRates['iron'] ?? '0'],
                'qc_phase_2' => ['label' => 'QC Phase 2', 'rate' => $sewingRates['qc_phase_2'] ?? '0'],
            ];
        }

        foreach ($sewingSteps as $stepKey => $stepMeta) {
            $stepLabel = $stepMeta['label'];
            $defaultRate = $stepMeta['rate'] ?? '0';
            $colDozens = $stepKey.'_dozens';
            $colPieces = $stepKey.'_pieces';

            $totalPieces = Decimal::sum(
                $sewingRows->map(function ($r) use ($stepKey, $colDozens, $colPieces) {
                    if (isset($r->stage_data['total_pieces']) && ($r->stage_data['step'] ?? null) === $stepKey) {
                        return (string) $r->stage_data['total_pieces'];
                    }
                    if ($r->job?->current_step === $stepKey && isset($r->stage_data['dozens'])) {
                        return (string) Decimal::of($r->stage_data['dozens'])->multipliedBy(12)->plus($r->stage_data['pieces'] ?? 0);
                    }
                    if (isset($r->{$colDozens})) {
                        return (string) Decimal::of($r->{$colDozens})->multipliedBy(12)->plus($r->{$colPieces} ?? 0);
                    }

                    return '0';
                })
            );

            if (Decimal::of($totalPieces)->isZero()) {
                continue;
            }

            $dozens = CuttingQuantity::dozens($totalPieces);
            $jobRate = $sewingRows->first(fn ($r) => $r->job?->current_step === $stepKey)?->job?->rate_per_dozen;
            $rate = $jobRate ?: ($sewingRates[$stepKey] ?? $defaultRate);

            if (! $rate || Decimal::of($rate)->isZero()) {
                $missingRates[] = $stepLabel.' sewing rate per dozen';
            }

            $stepCost = Decimal::scale(Decimal::of($dozens)->multipliedBy($rate ?: '0'), 2);
            $sewingCost = Decimal::scale(Decimal::of($sewingCost)->plus($stepCost), 2);

            $sewingDetails[$stepKey] = [
                'step' => $stepKey,
                'label' => $stepLabel,
                'pieces' => $totalPieces,
                'dozens' => $dozens,
                'rate_per_dozen' => $rate ?: '0',
                'cost' => $stepCost,
            ];
        }

        // 7. Direct Trim: Sewing Thread Cost
        $threadRate = $lot->thread_cost_per_dozen
            ?: Setting::getValue('thread_cost_per_dozen', '0');

        if (! $threadRate || Decimal::of($threadRate)->isZero()) {
            $missingRates[] = 'Thread cost per dozen';
        }

        $threadCost = Decimal::scale(Decimal::of($finishedDozens)->multipliedBy($threadRate ?: '0'), 2);

        // 8. Direct Packaging: Poly, Stickers, Packets
        $packingCost = '0';
        $packingDetails = [];
        $activeMaterials = PackingMaterial::all()->keyBy('id');

        if ($packingRows->isNotEmpty()) {
            $polyBag = $activeMaterials->firstWhere('category', 'poly_bag');
            $packetBag = $activeMaterials->firstWhere('category', 'packet_material');
            $otherStickers = $activeMaterials->where('category', 'other_sticker');

            // Poly bags: 1 per piece
            if ($polyBag) {
                $polyRate = $polyBag->cost_per_unit ?? '0';
                if (Decimal::of($polyRate)->isZero()) {
                    $missingRates[] = 'Poly bag unit cost';
                }
                $cost = Decimal::scale(Decimal::of($packedPieces)->multipliedBy($polyRate), 2);
                $packingCost = Decimal::scale(Decimal::of($packingCost)->plus($cost), 2);
                $packingDetails[] = [
                    'item' => $polyBag->name,
                    'category' => 'poly_bag',
                    'quantity' => $packedPieces,
                    'unit_cost' => $polyRate,
                    'cost' => $cost,
                ];
            }

            // Packet master bags: 1 per packet
            $packedPackets = (int) $packingRows->sum('packets');
            if ($packetBag) {
                $pRate = $packetBag->cost_per_unit ?? '0';
                if (Decimal::of($pRate)->isZero()) {
                    $missingRates[] = 'Packet material unit cost';
                }
                $cost = Decimal::scale(Decimal::of($packedPackets)->multipliedBy($pRate), 2);
                $packingCost = Decimal::scale(Decimal::of($packingCost)->plus($cost), 2);
                $packingDetails[] = [
                    'item' => $packetBag->name,
                    'category' => 'packet_material',
                    'quantity' => $packedPackets,
                    'unit_cost' => $pRate,
                    'cost' => $cost,
                ];
            }

            // Size stickers: 1 per piece (if configured)
            $hasSizeStickers = $activeMaterials->contains('category', 'size_sticker');
            if ($hasSizeStickers) {
                foreach ($packingRows->groupBy('size') as $sizeCode => $sRows) {
                    $sPieces = $sRows->sum('total_pieces');
                    $sizeSticker = $activeMaterials->first(fn ($m) => $m->category === 'size_sticker' && $m->size_code === $sizeCode)
                        ?: $activeMaterials->firstWhere('category', 'size_sticker');

                    $sRate = $sizeSticker?->cost_per_unit ?? '0';
                    if (Decimal::of($sRate)->isZero()) {
                        $missingRates[] = 'Size sticker unit cost'.($sizeCode ? ' ('.$sizeCode.')' : '');
                    }
                    $cost = Decimal::scale(Decimal::of($sPieces)->multipliedBy($sRate), 2);
                    $packingCost = Decimal::scale(Decimal::of($packingCost)->plus($cost), 2);
                    $packingDetails[] = [
                        'item' => ($sizeSticker?->name ?? 'Size Sticker').($sizeCode ? ' ('.$sizeCode.')' : ''),
                        'category' => 'size_sticker',
                        'quantity' => $sPieces,
                        'unit_cost' => $sRate,
                        'cost' => $cost,
                    ];
                }
            }

            // Other stickers
            foreach ($otherStickers as $sticker) {
                $oRate = $sticker->cost_per_unit ?? '0';
                if (Decimal::of($oRate)->isZero()) {
                    $missingRates[] = $sticker->name.' unit cost';
                }
                $cost = Decimal::scale(Decimal::of($packedPieces)->multipliedBy($oRate), 2);
                $packingCost = Decimal::scale(Decimal::of($packingCost)->plus($cost), 2);
                $packingDetails[] = [
                    'item' => $sticker->name,
                    'category' => 'other_sticker',
                    'quantity' => $packedPieces,
                    'unit_cost' => $oRate,
                    'cost' => $cost,
                ];
            }
        }

        // 9. Carrying & Logistics Cost
        $carryingRate = $lot->carrying_cost_per_dozen
            ?: Setting::getValue('carrying_cost_per_dozen', '0');

        if (! $carryingRate || Decimal::of($carryingRate)->isZero()) {
            $missingRates[] = 'Carrying cost per dozen';
        }

        $carryingCost = Decimal::scale(Decimal::of($finishedDozens)->multipliedBy($carryingRate ?: '0'), 2);

        // 10. Allocated Overhead / Shared Expenses (Rent, Utilities, Salaries)
        $directExpenses = Decimal::sum($lot->expenses->pluck('amount'));
        $allocatedExpenses = self::calculateAllocatedExpenses($lot, $finishedDozens);
        $totalOverhead = Decimal::scale(Decimal::of($directExpenses)->plus($allocatedExpenses['amount']), 2);

        // 11. Grand Total Cost & Finished Goods Unit Cost
        $totalCost = Decimal::sum([
            $yarnCost,
            $marketFabricCost,
            $knittingCost,
            $dyeingCost,
            $cuttingCost,
            $sewingCost,
            $threadCost,
            $packingCost,
            $carryingCost,
            $totalOverhead,
        ]);

        $costPerDozen = Decimal::of($finishedDozens)->isPositive()
            ? Decimal::scale(Decimal::of($totalCost)->dividedBy($finishedDozens, 6, RoundingMode::HalfUp), 2)
            : null;

        $costPerPiece = $costPerDozen !== null
            ? Decimal::scale(Decimal::of($costPerDozen)->dividedBy(12, 6, RoundingMode::HalfUp), 2)
            : null;

        $missingRates = array_values(array_unique($missingRates));

        return [
            'lot_id' => $lot->id,
            'lot_number' => $lot->lot_number,
            'current_stage' => $lot->current_stage,
            'is_complete' => empty($missingRates),
            'missing_rates' => $missingRates,
            'finished_pieces' => $finishedPieces,
            'finished_dozens' => $finishedDozens,
            'total_cost' => $totalCost,
            'cost_per_dozen' => $costPerDozen,
            'cost_per_piece' => $costPerPiece,
            'components' => [
                'yarn' => [
                    'label' => 'Raw Yarn',
                    'cost' => $yarnCost,
                    'details' => $yarnDetails,
                ],
                'market_fabric' => [
                    'label' => 'Market Purchased Ready Fabric',
                    'cost' => $marketFabricCost,
                    'details' => $marketFabricDetails,
                ],
                'knitting' => [
                    'label' => 'Knitting Labor',
                    'cost' => $knittingCost,
                    'details' => $knittingDetails,
                ],
                'dyeing' => [
                    'label' => 'Dyeing Labor',
                    'cost' => $dyeingCost,
                    'details' => $dyeingDetails,
                ],
                'cutting' => [
                    'label' => 'Cutting Labor',
                    'cost' => $cuttingCost,
                    'details' => $cuttingDetails,
                ],
                'sewing' => [
                    'label' => 'Sewing Assembly',
                    'cost' => $sewingCost,
                    'details' => $sewingDetails,
                ],
                'thread' => [
                    'label' => 'Sewing Thread',
                    'cost' => $threadCost,
                    'rate_per_dozen' => $threadRate ?: '0',
                ],
                'packing' => [
                    'label' => 'Packing Materials',
                    'cost' => $packingCost,
                    'details' => $packingDetails,
                ],
                'carrying' => [
                    'label' => 'Carrying & Transport',
                    'cost' => $carryingCost,
                    'rate_per_dozen' => $carryingRate ?: '0',
                ],
                'overhead' => [
                    'label' => 'Factory Overhead & Expenses',
                    'cost' => $totalOverhead,
                    'direct' => $directExpenses,
                    'allocated' => $allocatedExpenses['amount'],
                    'period_month' => $allocatedExpenses['period_month'],
                    'details' => $allocatedExpenses['details'],
                ],
            ],
            'color_dia_breakdown' => self::buildColorDiaBreakdown($lot, $totalCost, $finishedDozens),
        ];
    }

    /**
     * Allocate monthly shared expenses pro-rata by finished dozens.
     *
     * @return array{amount: string, period_month: string, details: array<int, mixed>}
     */
    private static function calculateAllocatedExpenses(Lot $lot, string $lotDozens): array
    {
        if (! Decimal::of($lotDozens)->isPositive()) {
            return ['amount' => '0', 'period_month' => '', 'details' => []];
        }

        // Determine relevant period month (from packing date, sewing date, or lot date)
        $latestPacking = $lot->packingRows->sortByDesc('job.packing_date')->first()?->job?->packing_date;
        $latestSewing = $lot->sewingRows->sortByDesc('job.sewing_date')->first()?->job?->sewing_date;
        $date = $latestPacking ?? $latestSewing ?? $lot->lot_date;
        $periodMonth = $date ? $date->format('Y-m') : now()->format('Y-m');

        $sharedExpenses = FactoryExpense::where('period_month', $periodMonth)
            ->where('allocation_method', 'pro_rata_finished_dozens')
            ->whereNull('lot_id')
            ->get();

        if ($sharedExpenses->isEmpty()) {
            return ['amount' => '0', 'period_month' => $periodMonth, 'details' => []];
        }

        // Total finished dozens across all lots active in that month
        $allMonthPackingDozens = Decimal::sum(
            PackingJobRow::whereHas('job', fn ($q) => $q->whereRaw("strftime('%Y-%m', packing_date) = ?", [$periodMonth]))
                ->pluck('dozens')
        );

        $totalMonthDozens = Decimal::of($allMonthPackingDozens)->isGreaterThan($lotDozens)
            ? $allMonthPackingDozens
            : $lotDozens;

        $allocatedSum = '0';
        $details = [];

        foreach ($sharedExpenses as $exp) {
            $share = Decimal::scale(Decimal::of($exp->amount)->multipliedBy($lotDozens)->dividedBy($totalMonthDozens, 6, RoundingMode::HalfUp), 2);
            $allocatedSum = Decimal::scale(Decimal::of($allocatedSum)->plus($share), 2);
            $details[] = [
                'title' => $exp->title,
                'category' => $exp->category,
                'total_expense' => $exp->amount,
                'lot_share' => $share,
            ];
        }

        return [
            'amount' => $allocatedSum,
            'period_month' => $periodMonth,
            'details' => $details,
        ];
    }

    /**
     * Color & DIA breakdown of quantities, waste, and yield.
     *
     * @return array<int, mixed>
     */
    private static function buildColorDiaBreakdown(Lot $lot, string $totalLotCost, string $totalFinishedDozens): array
    {
        $breakdown = [];
        $cuttingType = $lot->isUnderpants() ? 'cutting' : 'body';
        $cuttingRows = $lot->cuttingRows->where('cutting_type', $cuttingType);
        $packingRows = $lot->packingRows;

        $groups = $cuttingRows->groupBy(fn ($r) => $r->color.'|'.$r->dia);

        foreach ($groups as $key => $rows) {
            [$color, $dia] = explode('|', $key);
            $cutDozens = Decimal::sum($rows->pluck('dozens'));
            $cutPieces = (int) (string) Decimal::of($cutDozens)->multipliedBy(12);

            $matchedPacking = $packingRows->where('color', $color)->where('dia', (int) $dia);
            $packedPcs = $matchedPacking->sum('total_pieces');
            $packedDoz = $packedPcs > 0 ? CuttingQuantity::dozens((string) $packedPcs) : '0';

            // Pro-rata cost attribution by output finished dozens (or cut dozens if not packed)
            $hasFinished = Decimal::of($totalFinishedDozens)->isPositive() && Decimal::of($packedDoz)->isPositive();

            $batchCost = $hasFinished
                ? Decimal::scale(Decimal::of($totalLotCost)->multipliedBy($packedDoz)->dividedBy($totalFinishedDozens, 6, RoundingMode::HalfUp), 2)
                : '0';

            $batchCostPerDoz = Decimal::of($packedDoz)->isPositive()
                ? Decimal::scale(Decimal::of($batchCost)->dividedBy($packedDoz, 6, RoundingMode::HalfUp), 2)
                : null;

            $breakdown[] = [
                'color' => $color,
                'dia' => (int) $dia,
                'cut_dozens' => $cutDozens,
                'cut_pieces' => $cutPieces,
                'finished_pieces' => $packedPcs,
                'finished_dozens' => $packedDoz,
                'scrap_kg' => Decimal::sum($rows->pluck('cutting_scrap_kg')),
                'batch_cost' => $batchCost,
                'cost_per_dozen' => $batchCostPerDoz,
            ];
        }

        return $breakdown;
    }
}
