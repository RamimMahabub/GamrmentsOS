<x-app-layout>
    <x-slot name="header"><x-page-heading title="Sewing" subtitle="Choose a station to record its completed batches."></x-page-heading></x-slot>
    <div x-data="{
        active: @js(session('active_step', request('step'))),
        workflow: @js(request('workflow', (session('active_step') && in_array(session('active_step'), array_column($underpantsSteps, 'key')) ? 'underpants' : 'sando')))
    }">
    <section class="sewing-station-board">
        <div class="sewing-station-board-heading flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="sewing-eyebrow">Production flow</div>
                <h2>Choose a sewing station</h2>
                <p class="hint" x-show="workflow === 'sando'">Start at FOLD, complete assembly, then inspect at QC 1, steam at IRON, and finish at QC 2.</p>
                <p class="hint" x-show="workflow === 'underpants'" x-cloak>Underpants multi-step: Over Lock &rarr; Lock &rarr; Elastic Join &rarr; QC 1 &rarr; Iron &rarr; QC 2.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="btn btn-xs" :class="workflow === 'sando' ? 'btn-primary' : 'btn-secondary'" @click="workflow = 'sando'; if (active && !@js(collect($steps)->pluck('key')->all()).includes(active)) active = null;">
                    Sando (Undershirt)
                </button>
                <button type="button" class="btn btn-xs" :class="workflow === 'underpants' ? 'btn-primary' : 'btn-secondary'" @click="workflow = 'underpants'; if (active && !@js(collect($underpantsSteps)->pluck('key')->all()).includes(active)) active = null;">
                    Underpants (Boxer / Underwear)
                </button>
            </div>
        </div>
        <div class="sewing-station-grid" x-show="workflow === 'sando'">
            @foreach($steps as $step)
                <button type="button" class="sewing-station-card" :class="active === '{{ $step['key'] }}' ? 'active' : ''" @click="active = active === '{{ $step['key'] }}' ? null : '{{ $step['key'] }}'">
                    <span class="sewing-station-number">{{ $loop->iteration }}</span>
                    <span class="sewing-station-icon"><x-icon name="{{ $step['icon'] }}" class="h-7 w-7"/></span>
                    <strong>{{ $step['label'] }}</strong>
                    <span>{{ $step['description'] }}</span>
                    <b class="sewing-station-action" x-text="active === '{{ $step['key'] }}' ? 'Close station ↑' : 'Open station →'"></b>
                </button>
            @endforeach
        </div>
        <div class="sewing-station-grid" x-show="workflow === 'underpants'" x-cloak>
            @foreach($underpantsSteps as $step)
                <button type="button" class="sewing-station-card" :class="active === '{{ $step['key'] }}' ? 'active' : ''" @click="active = active === '{{ $step['key'] }}' ? null : '{{ $step['key'] }}'">
                    <span class="sewing-station-number">{{ $loop->iteration }}</span>
                    <span class="sewing-station-icon"><x-icon name="{{ $step['icon'] }}" class="h-7 w-7"/></span>
                    <strong>{{ $step['label'] }}</strong>
                    <span>{{ $step['description'] }}</span>
                    <b class="sewing-station-action" x-text="active === '{{ $step['key'] }}' ? 'Close station ↑' : 'Open station →'"></b>
                </button>
            @endforeach
        </div>
        <div class="sewing-inline-stations">
            @foreach(collect($steps)->concat($underpantsSteps)->unique('key') as $step)
                <section x-show="active === '{{ $step['key'] }}'" x-cloak class="sewing-inline-station">
                    @include('sewing._station', $stationPanels[$step['key']])
                </section>
            @endforeach
        </div>
    </section>
    <section class="sewing-overview" x-show="active === null" x-cloak>
        <div class="sewing-overview-head">
            <div>
                <div class="sewing-eyebrow">Sewing overview</div>
                <h2>Production at a glance</h2>
                <p class="hint">Open a station above to view its detailed batch table.</p>
            </div>
            <form method="GET" class="sewing-overview-filters">
                <label class="field">From<input type="date" name="from" value="{{ request('from') }}"></label>
                <label class="field">To<input type="date" name="to" value="{{ request('to') }}"></label>
                <label class="field">Operator<select name="staff_id"><option value="">All operators</option>@foreach($staffs as $staff)<option value="{{ $staff->id }}" @selected(request('staff_id') == $staff->id)>{{ $staff->name }}</option>@endforeach</select></label>
                <label class="field">Lot<select name="lot_id"><option value="">All lots</option>@foreach($lots as $lot)<option value="{{ $lot->id }}" @selected(request('lot_id') == $lot->id)>{{ $lot->lot_number }}</option>@endforeach</select></label>
                <button class="btn btn-secondary">Filter</button>
            </form>
        </div>
        @php
            $totalAltered = (int) \App\Support\Decimal::sum(collect($overview)->pluck('altered'));
            $totalRejected = \App\Support\Decimal::sum(collect($overview)->pluck('rejected'));
            $finalPassed = $overview['qc_phase_2']['pieces'] ?? '0';
        @endphp
        <div class="sewing-overview-kpis grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 p-5 bg-[#fafafc] border-b border-line">
            <div class="sewing-overview-kpi">
                <span>Total handoffs</span>
                <strong>{{ $overviewJobs->count() }}</strong>
                <small>across stations</small>
            </div>
            <div class="sewing-overview-kpi">
                <span>Batches logged</span>
                <strong>{{ $overviewJobs->sum(fn ($job) => $job->rows->count()) }}</strong>
                <small>recorded entries</small>
            </div>
            <div class="sewing-overview-kpi sewing-overview-kpi-primary">
                <span>Final QC passed</span>
                <strong class="text-emerald-700">{{ \App\Support\Decimal::display($finalPassed, 0, true) }}</strong>
                <small>ready for packing</small>
            </div>
            <div class="sewing-overview-kpi">
                <span>Total altered</span>
                <strong class="text-blue-700">{{ \App\Support\Decimal::display($totalAltered, 0, true) }}</strong>
                <small>pieces altered</small>
            </div>
            <div class="sewing-overview-kpi">
                <span>Total rejected</span>
                <strong class="text-rose-700">{{ \App\Support\Decimal::display($totalRejected, 0, true) }}</strong>
                <small>pieces discarded</small>
            </div>
        </div>
        <div class="sewing-progress-grid">
            @foreach($steps as $step)
                <div class="sewing-progress-card">
                    <span class="sewing-progress-icon"><x-icon name="{{ $step['icon'] }}" class="h-5 w-5"/></span>
                    <span class="sewing-progress-label">{{ $step['label'] }}</span>
                    <strong>{{ \App\Support\Decimal::display((string) $overview[$step['key']]['pieces'], 0, true) }} <small>pieces</small></strong>
                    <span>{{ $overview[$step['key']]['batches'] }} batches · {{ $overview[$step['key']]['executions'] }} handoffs</span>
                    @if(in_array($step['key'], ['qc_phase_1', 'qc_phase_2'], true))
                        <div class="mt-2 pt-2 border-t border-line/60 w-full flex flex-wrap gap-x-3 text-[11px] leading-tight">
                            <span class="text-emerald-700 font-semibold">Pass: {{ \App\Support\Decimal::display($overview[$step['key']]['passed_pieces'], 0, true) }}</span>
                            <span class="text-blue-700 font-medium">Altered: {{ \App\Support\Decimal::display($overview[$step['key']]['altered'], 0, true) }}</span>
                            <span class="text-rose-700 font-medium">Rej: {{ \App\Support\Decimal::display($overview[$step['key']]['rejected'], 0, true) }}</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
    </div>
</x-app-layout>
