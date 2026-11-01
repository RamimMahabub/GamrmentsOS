<x-app-layout>
    <x-slot name="header">
        <div class="staff-profile-header">
            <div class="flex min-w-0 items-center gap-4">
                <span class="staff-avatar">{{ strtoupper(substr($staff->name, 0, 1)) }}</span>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-3">
                        <h1>{{ $staff->name }}</h1>
                        <span class="status-badge {{ $staff->is_active ? 'status-badge-active' : 'status-badge-inactive' }}">{{ $staff->is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                    <p class="mt-2 truncate text-sm text-muted">{{ $staff->phone ?: 'No phone number' }} <span class="mx-2 text-faint">•</span> {{ $staff->address ?: 'No address recorded' }}</p>
                </div>
            </div>
            <a class="btn btn-secondary" href="{{ route('staffs.edit', $staff) }}">Edit staff</a>
        </div>
    </x-slot>

    @php
        $grandPieces = \App\Support\Decimal::sum($entries->pluck('pieces'));
        $grandDozens = \App\Support\CuttingQuantity::dozens($grandPieces);
        $sectionNames = ['body' => 'Body', 'neck' => 'Neck', 'rib' => 'Rib'];
    @endphp

    <div class="staff-dashboard space-y-6">
        <form method="GET" class="panel staff-filter-bar">
            <div class="staff-filter-title"><span class="dashboard-icon">⌁</span><div><h2>Performance overview</h2><p class="hint">Review cutting output for a selected period.</p></div></div>
            <div class="flex flex-wrap items-end gap-3">
                <label class="field"><span>From</span><input type="date" name="from" value="{{ $from }}"></label>
                <label class="field"><span>To</span><input type="date" name="to" value="{{ $to }}"></label>
                <button class="btn btn-primary">Apply filter</button>
            </div>
        </form>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="staff-kpi staff-kpi-primary"><span class="staff-kpi-label">Total output</span><strong>{{ $grandDozens }}</strong><span>dozen completed</span><small>{{ \App\Support\Decimal::scale($grandPieces) }} pieces</small></div>
            @foreach($sectionNames as $section => $label)
                @php $sectionTotal = $totals['cutting:'.$section] ?? ['dozens' => '0.000', 'pieces' => '0.000']; @endphp
                <div class="staff-kpi"><span class="staff-kpi-label">{{ $label }} cutting</span><strong>{{ $sectionTotal['dozens'] }}</strong><span>dozen completed</span><small>{{ $sectionTotal['pieces'] }} pieces</small></div>
            @endforeach
        </div>

        <div class="staff-note"><span class="dashboard-icon">i</span><p>Productivity measures cutting work. Body and neck processing count as separate work stages, so the same pieces may appear in both totals.</p></div>
    </div>

    @if(view()->exists('staffs.cutting-entries'))
        @include('staffs.cutting-entries')
    @else
        <div class="panel mt-6 p-8 text-center"><h2>No cutting entries in this period</h2><p class="hint mt-2">Try expanding the date range to see more activity.</p></div>
    @endif
</x-app-layout>
