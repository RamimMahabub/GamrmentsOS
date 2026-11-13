<x-app-layout>
    <x-slot name="header">
        <x-page-heading title="Factory Expenses &amp; Overhead" subtitle="Track factory rent, electricity, gas, salaried staff, and shared lot overheads.">
            <a class="btn btn-primary" href="{{ route('expenses.create') }}">
                <x-icon name="plus"/> Record Expense
            </a>
        </x-page-heading>
    </x-slot>

    <!-- Month & Allocation Overview KPIs -->
    <div class="grid gap-4 mb-6 grid-cols-1 sm:grid-cols-3">
        <div class="panel p-5">
            <span class="text-xs uppercase font-bold tracking-wider text-muted">Total Expenses ({{ $selectedMonth }})</span>
            <div class="text-2xl font-bold text-ink mt-1">
                {{ $currency }} {{ number_format((float)$totalAmount, 2) }}
            </div>
            <span class="hint block mt-1">All overheads logged for this month</span>
        </div>
        <div class="panel p-5 border-l-4 border-l-accent">
            <span class="text-xs uppercase font-bold tracking-wider text-accent">Allocated to Production</span>
            <div class="text-2xl font-bold text-accent mt-1">
                {{ $currency }} {{ number_format((float)$allocatedAmount, 2) }}
            </div>
            <span class="hint block mt-1">Divided across lots active in this month</span>
        </div>
        <div class="panel p-5">
            <span class="text-xs uppercase font-bold tracking-wider text-muted">Unallocated / Standalone</span>
            <div class="text-2xl font-bold text-ink mt-1">
                {{ $currency }} {{ number_format((float)$unallocatedAmount, 2) }}
            </div>
            <span class="hint block mt-1">General operational expenses</span>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="panel mb-6 p-4">
        <form method="GET" action="{{ route('expenses.index') }}" class="flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex flex-wrap items-center gap-3">
                <label class="flex items-center gap-2 font-semibold text-ink">
                    <span>Month:</span>
                    <select name="month" class="py-1 px-3 text-xs rounded-xl border border-line bg-surface cursor-pointer" onchange="this.form.submit()">
                        @foreach($availableMonths as $m)
                            <option value="{{ $m }}" @selected($selectedMonth === $m)>{{ date('F Y', strtotime($m.'-01')) }} ({{ $m }})</option>
                        @endforeach
                    </select>
                </label>
                <label class="flex items-center gap-2 font-semibold text-ink">
                    <span>Category:</span>
                    <select name="category" class="py-1 px-3 text-xs rounded-xl border border-line bg-surface cursor-pointer" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach($categories as $catKey => $catLabel)
                            <option value="{{ $catKey }}" @selected(request('category') === $catKey)>{{ $catLabel }}</option>
                        @endforeach
                    </select>
                </label>
                @if(request('category'))
                    <a href="{{ route('expenses.index', ['month' => $selectedMonth]) }}" class="text-muted hover:text-accent font-medium">Clear category</a>
                @endif
            </div>
            <div class="text-muted font-medium">
                {{ $expenses->total() }} expense entries
            </div>
        </form>
    </div>

    <!-- Expenses Table -->
    <div class="panel">
        <div class="panel-head flex items-center justify-between">
            <h2>Expense Ledger &middot; {{ date('F Y', strtotime($selectedMonth.'-01')) }}</h2>
            <span class="hint">Shared costs are allocated to lots pro-rata by finished dozens.</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Title / Description</th>
                        <th>Category</th>
                        <th>Allocation Basis</th>
                        <th class="number">Amount</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date->format('d/m/Y') }}</td>
                            <td>
                                <strong class="text-ink">{{ $expense->title }}</strong>
                                @if($expense->notes)
                                    <span class="hint block text-[11px] truncate max-w-xs">{{ $expense->notes }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="pill font-semibold text-[11px]">
                                    {{ $categories[$expense->category] ?? ucfirst($expense->category) }}
                                </span>
                            </td>
                            <td>
                                @if($expense->allocation_method === 'pro_rata_finished_dozens')
                                    <span class="inline-flex items-center gap-1.5 text-xs text-accent font-medium">
                                        <span class="h-1.5 w-1.5 rounded-full bg-accent"></span>
                                        Shared (All active lots)
                                    </span>
                                @elseif($expense->allocation_method === 'lot_specific' && $expense->lot)
                                    <a href="{{ route('lots.show', $expense->lot) }}" class="link text-xs font-semibold">
                                        Lot {{ $expense->lot->lot_number }}
                                    </a>
                                @else
                                    <span class="text-muted text-xs">Unallocated</span>
                                @endif
                            </td>
                            <td class="number font-bold text-ink">
                                {{ $currency }} {{ number_format((float)$expense->amount, 2) }}
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a class="btn btn-secondary btn-xs" href="{{ route('expenses.edit', $expense) }}">Edit</a>
                                    <form method="POST" action="{{ route('expenses.destroy', $expense) }}" onsubmit="return confirm('Delete this expense?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-xs">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-10 text-center text-muted">
                                No expenses logged for {{ date('F Y', strtotime($selectedMonth.'-01')) }}.
                                <div class="mt-2">
                                    <a class="link text-xs" href="{{ route('expenses.create') }}">+ Add factory expense</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())
            <div class="p-4 border-t border-line">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>
</x-app-layout>

