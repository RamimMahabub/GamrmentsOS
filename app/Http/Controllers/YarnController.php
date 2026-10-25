<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockRequest;
use App\Http\Requests\YarnRequest;
use App\Models\Setting;
use App\Models\Yarn;
use App\Modules\Yarn\StockLedger;
use App\Modules\Yarn\YarnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class YarnController extends Controller
{
    public function index(Request $request): View
    {
        $sort = in_array($request->sort, ['name', 'yarn_type', 'country_of_origin', 'price_per_kg'], true) ? $request->sort : 'name';
        $yarns = Yarn::withSum('movements as available_kg', 'quantity_kg')->when($request->q, fn ($query) => $query->where(function ($query) use ($request): void {
            $query->where('name', 'like', '%'.$request->q.'%')->orWhere('yarn_type', 'like', '%'.$request->q.'%')->orWhere('country_of_origin', 'like', '%'.$request->q.'%');
        }))->orderBy($sort, $request->direction === 'desc' ? 'desc' : 'asc')->paginate(25)->withQueryString();

        return view('yarns.index', compact('yarns'));
    }

    public function create(): View
    {
        return $this->form(new Yarn);
    }

    public function edit(Yarn $yarn): View
    {
        return $this->form($yarn);
    }

    private function form(Yarn $yarn): View
    {
        $unit = Setting::getValue('default_weight_unit', 'lb');
        $form = ['priceKg' => old('price_per_kg', $yarn->price_per_kg ?? '0'), 'unit' => old('price_unit', $yarn->price_unit ?? $unit),
            'bagKg' => old('bag_size_kg', $yarn->bag_size_kg ?? ''), 'bagUnit' => old('bag_size_unit', $yarn->bag_size_unit ?? $unit),
            'kg' => old('initial_kg', '0'), 'weightUnit' => $unit];

        return view('yarns.form', compact('yarn', 'form'));
    }

    public function store(YarnRequest $request, YarnService $service): RedirectResponse
    {
        $yarn = $service->save($request->validated());

        return to_route('yarns.show', $yarn)->with('status', 'Yarn and opening stock recorded.');
    }

    public function update(YarnRequest $request, Yarn $yarn, YarnService $service): RedirectResponse
    {
        $service->save($request->validated(), $yarn);

        return to_route('yarns.show', $yarn)->with('status', 'Yarn updated.');
    }

    public function show(Yarn $yarn): View
    {
        $movements = $yarn->movements()->with('ref')->orderBy('movement_date')->orderBy('id')->paginate(25);
        $first = $movements->first();
        $running = $first ? (string) $yarn->movements()->where(function ($query) use ($first): void {
            $query->where('movement_date', '<', $first->getRawOriginal('movement_date'))->orWhere(function ($query) use ($first): void {
                $query->where('movement_date', $first->getRawOriginal('movement_date'))->where('id', '<', $first->id);
            });
        })->sum('quantity_kg') : '0';
        $balance = app(StockLedger::class)->balance($yarn);

        return view('yarns.show', compact('yarn', 'movements', 'running', 'balance'));
    }

    public function stock(StockRequest $request, Yarn $yarn, YarnService $service): RedirectResponse
    {
        $service->addStock($yarn, $request->validated());

        return to_route('yarns.show', $yarn)->with('status', 'Stock purchase recorded.');
    }

    public function destroy(Yarn $yarn, YarnService $service): RedirectResponse
    {
        $service->delete($yarn);

        return to_route('yarns.index')->with('status', 'Yarn deleted.');
    }
}
