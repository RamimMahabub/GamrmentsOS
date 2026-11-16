<x-app-layout>
    <x-slot name="header">
        <x-page-heading title="Factory Expenses &amp; Overhead" subtitle="Track factory rent, electricity, gas, salaried staff, and shared lot overheads.">
            <a class="btn btn-primary btn-xs" href="{{ route('expenses.create') }}">
                <x-icon name="plus" class="h-3.5 w-3.5"/> Record Expense
            </a>
        </x-page-heading>
    </x-slot>

    <!-- Month & Allocation Overview KPIs Ribbon -->
    <div class="mb-3 flex flex-wrap items-center gap-2 rounded-xl border border-line bg-surface px-3 py-1.5 shadow-2xs">
        <div class="flex items-center gap-1.5 text-xs">
            <span class="text-muted">Total ({{ $selectedMonth }}):</span>
            <strong class="font-bold text-ink">{{ $currency }} {{ number_format((float)$totalAmount, 2) }}</strong>
        </div>
        <span class="text-line">•</span>
        <div class="flex items-center gap-1.5 text-xs">
            <span class="text-muted">Allocated to Lots:</span>
            <strong class="font-bold text-accent">{{ $currency }} {{ number_format((float)$allocatedAmount, 2) }}</strong>
        </div>
        <span class="text-line">•</span>
        <div class="flex items-center gap-1.5 text-xs">
            <span class="text-muted">Unallocated / Standalone:</span>
            <strong class="font-bold text-ink">{{ $currency }} {{ number_format((float)$unallocatedAmount, 2) }}</strong>
        </div>
    </div>

    <!-- Expenses Table Panel -->
    <div class="panel shadow-2xs border border-line bg-surface rounded-xl overflow-hidden">
        <div class="panel-head flex flex-wrap items-center justify-between gap-2.5 px-3.5 py-2 border-b border-line">
            <div>
                <h2>Expense Ledger &middot; {{ date('F Y', strtotime($selectedMonth.'-01')) }}</h2>
                <span class="hint">Shared costs are allocated to lots pro-rata by finished dozens.</span>
            </div>
            <form method="GET" action="{{ route('expenses.index') }}" class="flex flex-wrap items-center gap-2 text-xs">
                <label class="flex items-center gap-1.5 font-semibold text-ink">
                    <span>Month:</span>
                    <select name="month" class="py-1 px-2 text-xs rounded-lg border border-line bg-surface cursor-pointer h-7" onchange="this.form.submit()">
                        @foreach($availableMonths as $m)
                            <option value="{{ $m }}" @selected($selectedMonth === $m)>{{ date('F Y', strtotime($m.'-01')) }} ({{ $m }})</option>
                        @endforeach
                    </select>
                </label>
                <label class="flex items-center gap-1.5 font-semibold text-ink">
                    <span>Category:</span>
                    <select name="category" class="py-1 px-2 text-xs rounded-lg border border-line bg-surface cursor-pointer h-7" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach($categories as $catKey => $catLabel)
                            <option value="{{ $catKey }}" @selected(request('category') === $catKey)>{{ $catLabel }}</option>
                        @endforeach
                    </select>
                </label>
                @if(request('category'))
                    <a href="{{ route('expenses.index', ['month' => $selectedMonth]) }}" class="text-muted hover:text-accent font-medium text-xs">Clear</a>
                @endif
                <span class="text-muted font-medium text-[11px] ml-1">({{ $expenses->total() }} entries)</span>
            </form>
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
                        <th class="number pr-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td class="font-medium text-ink text-xs whitespace-nowrap">{{ $expense->expense_date->format('d/m/Y') }}</td>
                            <td>
                                <strong class="text-ink text-xs">{{ $expense->title }}</strong>
                                @if($expense->notes)
                                    <span class="hint block text-[11px] truncate max-w-xs">{{ $expense->notes }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="pill font-semibold text-[10px] px-1.5 py-0.2">
                                    {{ $categories[$expense->category] ?? ucfirst($expense->category) }}
                                </span>
                            </td>
                            <td>
                                @if($expense->allocation_method === 'pro_rata_finished_dozens')
                                    <span class="inline-flex items-center gap-1 text-xs text-accent font-medium">
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
                            <td class="number font-bold text-ink text-xs">
                                {{ $currency }} {{ number_format((float)$expense->amount, 2) }}
                            </td>
                            <td class="number pr-4 whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
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
                            <td colspan="6" class="p-6 text-center text-muted text-xs">
                                No expenses logged for {{ date('F Y', strtotime($selectedMonth.'-01')) }}.
                                <div class="mt-1.5">
                                    <a class="link text-xs" href="{{ route('expenses.create') }}">+ Add factory expense</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())
            <div class="p-2.5 border-t border-line">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
