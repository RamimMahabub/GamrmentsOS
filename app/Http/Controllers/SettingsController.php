<?php

namespace App\Http\Controllers;

use App\Http\Requests\ColorRequest;
use App\Http\Requests\SettingsRequest;
use App\Models\Color;
use App\Models\Setting;
use App\Modules\Dyeing\ColorService;
use App\Support\UnderpantsSewingConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('settings.edit', [
            'colors' => Color::orderBy('name')->paginate(25),
            'colorOptions' => Color::orderBy('name')->get(['id', 'name']),
            'knittingRate' => Setting::getValue('default_knitting_rate_per_kg', '0'),
            'dyeingRate' => Setting::getValue('default_dyeing_rate_per_kg', '0'),
            'cuttingRates' => Setting::getValue('cutting_rates', ['body' => '0', 'neck' => '0', 'rib' => '0']),
            'sewingRates' => Setting::getValue('sewing_rates', [
                'fold' => '0', 'lock' => '0', 'down' => '0', 'neck_join' => '0',
                'neck_finishing' => '0', 'qc_phase_1' => '0', 'iron' => '0', 'qc_phase_2' => '0',
            ]),
            'carryingCost' => Setting::getValue('carrying_cost_per_dozen', '0'),
            'threadCost' => Setting::getValue('thread_cost_per_dozen', '0'),
            'underpantsSteps' => UnderpantsSewingConfig::getGroups(),
        ]);
    }

    public function update(SettingsRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $data = $request->validated();
            if (isset($data['underpants_sewing_steps']) && is_array($data['underpants_sewing_steps'])) {
                UnderpantsSewingConfig::saveGroups($data['underpants_sewing_steps']);
                unset($data['underpants_sewing_steps']);
            }
            foreach ($data as $key => $value) {
                Setting::putValue($key, $key === 'dia_presets' ? array_map('intval', $value) : $value);
            }
        }, 5);

        return to_route('settings.edit')->with('status', 'Settings updated.');
    }

    public function color(ColorRequest $request, Color $color, ColorService $service): RedirectResponse
    {
        $data = $request->validated();
        if ($request->filled('target_id')) {
            $service->merge($color, Color::findOrFail($data['target_id']));
        } else {
            $service->update($color, $data);
        }

        return to_route('settings.edit')->with('status', 'Color updated.');
    }
}
