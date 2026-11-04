<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use App\Modules\Dyeing\DyedFabricBalance;
use App\Modules\Dyeing\FabricAvailability;
use App\Support\JobTotals;
use Illuminate\View\View;

class LotController extends Controller
{
    public function show(Lot $lot, FabricAvailability $fabric, DyedFabricBalance $dyed): View
    {
        $lot->syncCurrentStage();

        $lot->load([
            'knittingJob.company.contacts',
            'knittingJob.yarnLinks.yarn',
            'knittingJob.diaRows.yarnLines.yarn',
            'dyeingJobs.company.contacts',
            'dyeingJobs.colorBlocks.color',
            'dyeingJobs.colorBlocks.rows',
            'cuttingRows.job',
            'cuttingRows.lot',
            'cuttingRows.sourceBodyRow',
            'cuttingRows.roll.dyeingRow.colorBlock.color',
            'sewingRows.job.staff',
            'sewingRows.sourceBodyRow',
            'sewingRows.sourceNeckRow',
        ]);

        $allLots = Lot::orderBy('lot_number')->get(['id', 'lot_number', 'current_stage']);

        return view('lots.show', [
            'lot' => $lot,
            'allLots' => $allLots,
            'fabric' => $fabric->forLot($lot),
            'dyed' => $dyed->forLot($lot),
            'pipeline' => JobTotals::pipeline($lot),
        ]);
    }
}
