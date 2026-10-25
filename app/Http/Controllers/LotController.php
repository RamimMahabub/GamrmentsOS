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
        $lot->load(['knittingJob.company.contacts', 'knittingJob.yarnLinks.yarn', 'knittingJob.diaRows.yarnLines.yarn', 'dyeingJobs.company.contacts', 'dyeingJobs.colorBlocks.color', 'dyeingJobs.colorBlocks.rows', 'cuttingRows.job', 'cuttingRows.lot', 'cuttingRows.sourceBodyRow', 'cuttingRows.roll.dyeingRow.colorBlock.color']);

        return view('lots.show', ['lot' => $lot, 'fabric' => $fabric->forLot($lot), 'dyed' => $dyed->forLot($lot), 'pipeline' => JobTotals::pipeline($lot)]);
    }
}
