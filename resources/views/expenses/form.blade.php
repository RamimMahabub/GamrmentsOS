<x-app-layout>
    <x-slot name="header">
        <x-page-heading :title="($expense->exists ? 'Edit' : 'Record').' Factory Expense'" subtitle="Log overheads like rent, power bills, and staff salaries.">
            <a class="btn btn-secondary" href="{{ route('expenses.index') }}">Back</a>
        </x-page-heading>
    </x-slot>

    <form method="POST" action="{{ $expense->exists ? route('expenses.update', $expense) : route('expenses.store') }}" class="panel max-w-2xl p-6 space-y-6" x-data="{ method: '{{ old('allocation_method', $expense->allocation_method ?? 'pro_rata_finished_dozens') }}' }">
        @csrf
        @if($expense->exists)
            @method('PUT')
        @endif

        <div class="grid gap-5 sm:grid-cols-2">
            <label class="field sm:col-span-2">
                <span>Expense Title / Purpose</span>
                <input type="text" required name="title" value="{{ old('title', $expense->title) }}" placeholder="e.g. Factory Rent for November 2026, DESCO Electric Bill">
            </label>

            <label class="field">
                <span>Category</span>
                <select required name="category">
                    <option value="">Select Category</option>
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}" @selected(old('category', $expense->category) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="field">
                <span>Amount ({{ $currency }})</span>
                <input type="number" step="0.01" min="0.01" required name="amount" value="{{ old('amount', $expense->amount) }}" placeholder="0.00">
            </label>

            <label class="field">
                <span>Expense Date</span>
                <input type="date" required name="expense_date" value="{{ old('expense_date', $expense->expense_date?->toDateString() ?? now()->toDateString()) }}">
            </label>

            <label class="field">
                <span>Allocation Basis</span>
                <select required name="allocation_method" x-model="method">
                    <option value="pro_rata_finished_dozens">Shared (Pro-rata by finished dozens across all lots)</option>
                    <option value="lot_specific">Specific Lot only</option>
                    <option value="unallocated">Unallocated Factory Overhead</option>
                </select>
                <span class="hint">Shared costs will be divided across lots active in this calendar month.</span>
            </label>

            <div class="field sm:col-span-2" x-show="method === 'lot_specific'" x-cloak>
                <label>
                    <span>Assign to Specific Lot</span>
                    <select name="lot_id" class="w-full">
                        <option value="">Choose lot</option>
                        @foreach($lots as $lot)
                            <option value="{{ $lot->id }}" @selected(old('lot_id', $expense->lot_id) == $lot->id)>Lot {{ $lot->lot_number }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <label class="field sm:col-span-2">
                <span>Notes &amp; Invoicing Details (Optional)</span>
                <textarea name="notes" placeholder="e.g. Receipt #, payment voucher reference, check number...">{{ old('notes', $expense->notes) }}</textarea>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-line pt-5">
            <a href="{{ route('expenses.index') }}" class="btn btn-secondary">Cancel</a>
            <button class="btn btn-primary">Save Expense</button>
        </div>
    </form>
</x-app-layout>

