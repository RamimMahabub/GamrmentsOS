<?php

namespace App\Http\Controllers;

use App\Http\Requests\ColorRequest;
use App\Http\Requests\SettingsRequest;
use App\Models\Color;
use App\Models\Setting;
use App\Modules\Dyeing\ColorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('settings.edit', ['colors' => Color::orderBy('name')->paginate(25), 'colorOptions' => Color::orderBy('name')->get(['id', 'name'])]);
    }

    public function update(SettingsRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            foreach ($request->validated() as $key => $value) {
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
