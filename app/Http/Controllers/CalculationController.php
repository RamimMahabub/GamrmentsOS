<?php

namespace App\Http\Controllers;

use App\Models\DyeingJob;
use App\Models\Lot;
use App\Models\Yarn;
use App\Modules\Dyeing\FabricAvailability;
use App\Modules\Knitting\LotGenerator;
use App\Rules\DecimalInput;
use App\Support\Decimal;
use App\Support\WasteCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalculationController extends Controller
{
    public function preview(Request $request, LotGenerator $generator): JsonResponse
    {
        $data = $request->validate(['yarn_id' => ['required', 'integer', 'exists:yarns,id'], 'date' => ['required', 'date_format:Y-m-d']]);

        return response()->json(['lot_number' => $generator->preview(Yarn::findOrFail($data['yarn_id']), $data['date'])]);
    }

    public function fabric(Request $request, Lot $lot, FabricAvailability $fabric): JsonResponse
    {
        $data = $request->validate(['job_id' => ['nullable', 'integer'], 'colors' => ['nullable', 'integer', 'min:1', 'max:50']]);
        $jobId = isset($data['job_id']) ? DyeingJob::where('lot_id', $lot->id)->findOrFail($data['job_id'])->id : null;
        $lot->load(['knittingJob.company', 'knittingJob.yarnLinks.yarn', 'knittingJob.diaRows']);
        $rows = $fabric->forLot($lot, $jobId);
        foreach ($rows as &$row) {
            $row['split'] = $fabric->split($row['available_kg'], (int) ($data['colors'] ?? 1));
        }

        return response()->json(['lot_number' => $lot->lot_number, 'knitting_company' => $lot->knittingJob->company->name,
            'yarns' => $lot->knittingJob->yarnLinks->map(fn ($link): string => $link->yarn->name)->all(), 'rows' => $rows]);
    }

    public function calculate(Request $request): JsonResponse
    {
        $data = $request->validate(['input_kg' => ['required', new DecimalInput], 'waste_percent' => ['required', new DecimalInput(2, false, '100')],
            'returned_kg' => ['nullable', new DecimalInput]]);

        $input = Decimal::normalize($data['input_kg']);
        $returned = isset($data['returned_kg']) ? Decimal::normalize($data['returned_kg']) : null;

        return response()->json(['output_kg' => WasteCalculator::output($input, $data['waste_percent']),
            'dyeing_waste_percent' => WasteCalculator::percent($input, $returned)]);
    }
}
