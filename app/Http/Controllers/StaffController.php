<?php

namespace App\Http\Controllers;

use App\Http\Requests\StaffRequest;
use App\Models\Staff;
use App\Support\StaffProductivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(Request $request, StaffProductivity $productivity): View
    {
        $request->validate(['q' => ['nullable', 'string', 'max:255']]);
        $staffs = Staff::when($request->q, fn ($query) => $query->where(fn ($q) => $q->where('name', 'like', '%'.$request->q.'%')->orWhere('phone', 'like', '%'.$request->q.'%')))->orderBy('name')->paginate(25)->withQueryString();

        $lifetimePieces = $productivity->lifetimePieces($staffs->getCollection());

        return view('staffs.index', compact('staffs', 'lifetimePieces'));
    }

    public function create(): View
    {
        return view('staffs.form', ['staff' => new Staff]);
    }

    public function edit(Staff $staff): View
    {
        return view('staffs.form', compact('staff'));
    }

    public function store(StaffRequest $request): RedirectResponse
    {
        $staff = Staff::create($request->validated());

        return to_route('staffs.show', $staff)->with('status', 'Staff created.');
    }

    public function update(StaffRequest $request, Staff $staff): RedirectResponse
    {
        $staff->update($request->validated());

        return to_route('staffs.show', $staff)->with('status', 'Staff updated.');
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        $staff->update(['is_active' => false]);

        return to_route('staffs.index')->with('status', 'Staff deactivated. History retained.');
    }

    public function show(Request $request, Staff $staff, StaffProductivity $productivity): View
    {
        $request->validate(['from' => ['nullable', 'date_format:Y-m-d'], 'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from']]);
        $from = $request->input('from') ?: now()->startOfMonth()->toDateString();
        $to = $request->input('to') ?: now()->endOfMonth()->toDateString();
        $entries = $productivity->entries($staff, $from, $to);
        $totals = $productivity->totals($entries);

        return view('staffs.show', compact('staff', 'entries', 'totals', 'from', 'to'));
    }
}
