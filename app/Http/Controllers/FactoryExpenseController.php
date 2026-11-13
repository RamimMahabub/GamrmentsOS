<?php

namespace App\Http\Controllers;

use App\Http\Requests\FactoryExpenseRequest;
use App\Models\FactoryExpense;
use App\Models\Lot;
use App\Support\Decimal;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FactoryExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $selectedMonth = $request->query('month', now()->format('Y-m'));

        $query = FactoryExpense::with('lot')
            ->where('period_month', $selectedMonth)
            ->latest('expense_date');

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        $expenses = $query->paginate(25)->withQueryString();

        $allMonthExpenses = FactoryExpense::where('period_month', $selectedMonth)->get();
        $totalAmount = Decimal::sum($allMonthExpenses->pluck('amount'));
        $allocatedAmount = Decimal::sum($allMonthExpenses->where('allocation_method', '!=', 'unallocated')->pluck('amount'));
        $unallocatedAmount = Decimal::scale(Decimal::of($totalAmount)->minus($allocatedAmount));

        $availableMonths = FactoryExpense::select('period_month')
            ->distinct()
            ->orderByDesc('period_month')
            ->pluck('period_month');

        if (! $availableMonths->contains($selectedMonth)) {
            $availableMonths->prepend($selectedMonth);
        }

        return view('expenses.index', [
            'expenses' => $expenses,
            'selectedMonth' => $selectedMonth,
            'availableMonths' => $availableMonths,
            'totalAmount' => $totalAmount,
            'allocatedAmount' => $allocatedAmount,
            'unallocatedAmount' => $unallocatedAmount,
            'categories' => self::categories(),
        ]);
    }

    public function create(): View
    {
        return view('expenses.form', [
            'expense' => new FactoryExpense(['expense_date' => now()->toDateString(), 'allocation_method' => 'pro_rata_finished_dozens']),
            'categories' => self::categories(),
            'lots' => Lot::orderBy('lot_number')->get(['id', 'lot_number']),
        ]);
    }

    public function store(FactoryExpenseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['period_month'] = Carbon::parse($data['expense_date'])->format('Y-m');

        if ($data['allocation_method'] !== 'lot_specific') {
            $data['lot_id'] = null;
        }

        FactoryExpense::create($data);

        return redirect()->route('expenses.index', ['month' => $data['period_month']])
            ->with('status', 'Expense recorded successfully.');
    }

    public function edit(FactoryExpense $expense): View
    {
        return view('expenses.form', [
            'expense' => $expense,
            'categories' => self::categories(),
            'lots' => Lot::orderBy('lot_number')->get(['id', 'lot_number']),
        ]);
    }

    public function update(FactoryExpenseRequest $request, FactoryExpense $expense): RedirectResponse
    {
        $data = $request->validated();
        $data['period_month'] = Carbon::parse($data['expense_date'])->format('Y-m');

        if ($data['allocation_method'] !== 'lot_specific') {
            $data['lot_id'] = null;
        }

        $expense->update($data);

        return redirect()->route('expenses.index', ['month' => $data['period_month']])
            ->with('status', 'Expense updated successfully.');
    }

    public function destroy(FactoryExpense $expense): RedirectResponse
    {
        $month = $expense->period_month;
        $expense->delete();

        return redirect()->route('expenses.index', ['month' => $month])
            ->with('status', 'Expense deleted.');
    }

    /**
     * @return array<string, string>
     */
    public static function categories(): array
    {
        return [
            'rent' => 'Factory Rent',
            'electric_bill' => 'Electricity Bill',
            'gas_bill' => 'Gas Bill',
            'water_bill' => 'Water Bill',
            'salary' => 'Salaried Staff',
            'maintenance' => 'Repairs & Maintenance',
            'other' => 'Other Overhead',
        ];
    }
}
