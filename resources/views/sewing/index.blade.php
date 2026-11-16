<x-app-layout>
    <x-slot name="header"><x-page-heading title="Sewing" subtitle="Choose a station to record its completed batches."></x-page-heading></x-slot>
    <div x-data="{
        active: null,
        workflow: @js(request('workflow', 'sando')),
        setWorkflow(w) {
            this.workflow = w;
            this.active = null;
            const url = new URL(window.location);
            url.searchParams.set('workflow', w);
            url.searchParams.delete('step');
            window.history.replaceState({}, '', url);
        }
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
                <button type="button" class="btn btn-xs" :class="workflow === 'sando' ? 'btn-primary' : 'btn-secondary'" @click="setWorkflow('sando')">
                    Sando (Undershirt)
                </button>
                <button type="button" class="btn btn-xs" :class="workflow === 'underpants' ? 'btn-primary' : 'btn-secondary'" @click="setWorkflow('underpants')">
                    Underpants (Boxer / Underwear)
                </button>
            </div>
        </div>
        <div class="sewing-station-grid" x-show="workflow === 'sando'">
            @foreach($steps as $step)
                <button type="button" class="sewing-station-card" :class="active === '{{ $step['key'] }}' ? 'active' : ''" @click="active = active === '{{ $step['key'] }}' ? null : '{{ $step['key'] }}'">
                    <span class="sewing-station-number">{{ $loop->iteration }}</span>
                    <strong class="truncate text-xs">{{ $step['label'] }}</strong>
                    <b class="sewing-station-action" x-text="active === '{{ $step['key'] }}' ? 'Close ↑' : 'Open →'"></b>
                </button>
            @endforeach
        </div>
        <div class="sewing-station-grid" x-show="workflow === 'underpants'" x-cloak>
            @foreach($underpantsSteps as $step)
                <button type="button" class="sewing-station-card" :class="active === '{{ $step['key'] }}' ? 'active' : ''" @click="active = active === '{{ $step['key'] }}' ? null : '{{ $step['key'] }}'">
                    <span class="sewing-station-number">{{ $loop->iteration }}</span>
                    <strong class="truncate text-xs">{{ $step['label'] }}</strong>
                    <b class="sewing-station-action" x-text="active === '{{ $step['key'] }}' ? 'Close ↑' : 'Open →'"></b>
                </button>
            @endforeach
        </div>
        <div class="sewing-inline-stations">
            @foreach($steps as $step)
                <section x-show="workflow === 'sando' && active === '{{ $step['key'] }}'" x-cloak class="sewing-inline-station">
                    @include('sewing._station', $sandoStationPanels[$step['key']])
                </section>
            @endforeach
            @foreach($underpantsSteps as $step)
                <section x-show="workflow === 'underpants' && active === '{{ $step['key'] }}'" x-cloak class="sewing-inline-station">
                    @include('sewing._station', $underpantsStationPanels[$step['key']])
                </section>
            @endforeach
        </div>
    </section>
    <section class="sewing-overview" x-show="active === null" x-cloak>
        <div class="sewing-overview-head">
            <div>
                <div class="sewing-eyebrow">Sewing overview</div>
                <h2>Production at a glance (<span x-text="workflow === 'sando' ? 'Sando' : 'Underpants'"></span>)</h2>
                <p class="hint">Open a station above to view its detailed batch table.</p>
            </div>
            <form method="GET" class="sewing-overview-filters">
                <input type="hidden" name="workflow" :value="workflow">
                <label class="field">From<input type="date" name="from" value="{{ request('from') }}"></label>
                <label class="field">To<input type="date" name="to" value="{{ request('to') }}"></label>
                <label class="field">Operator<select name="staff_id"><option value="">All operators</option>@foreach($staffs as $staff)<option value="{{ $staff->id }}" @selected(request('staff_id') == $staff->id)>{{ $staff->name }}</option>@endforeach</select></label>
                <label class="field">Lot
                    <select name="lot_id" x-show="workflow === 'sando'" :disabled="workflow !== 'sando'">
                        <option value="">All lots</option>
                        @foreach($sandoLots as $lot)
                            <option value="{{ $lot->id }}" @selected(request('lot_id') == $lot->id)>{{ $lot->lot_number }}</option>
                        @endforeach
                    </select>
                    <select name="lot_id" x-show="workflow === 'underpants'" :disabled="workflow !== 'underpants'" x-cloak>
                        <option value="">All lots</option>
                        @foreach($underpantsLots as $lot)
                            <option value="{{ $lot->id }}" @selected(request('lot_id') == $lot->id)>{{ $lot->lot_number }}</option>
                        @endforeach
                    </select>
                </label>
                <button class="btn btn-secondary">Filter</button>
            </form>
        </div>

        {{-- Sando Overview --}}
        <div x-show="workflow === 'sando'">
            <div class="sewing-overview-kpis grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 p-5 bg-[#fafafc] border-b border-line">
                <div class="sewing-overview-kpi">
                    <span>Total handoffs</span>
                    <strong>{{ $sandoKpis['handoffs'] }}</strong>
                    <small>across stations</small>
                </div>
                <div class="sewing-overview-kpi">
                    <span>Batches logged</span>
                    <strong>{{ $sandoKpis['batches'] }}</strong>
                    <small>recorded entries</small>
                </div>
                <div class="sewing-overview-kpi sewing-overview-kpi-primary">
                    <span>Final QC passed</span>
                    <strong class="text-emerald-700">{{ \App\Support\Decimal::display((string) $sandoKpis['final_passed'], 0, true) }}</strong>
                    <small>ready for packing</small>
                </div>
                <div class="sewing-overview-kpi">
                    <span>Total altered</span>
                    <strong class="text-blue-700">{{ \App\Support\Decimal::display((string) $sandoKpis['altered'], 0, true) }}</strong>
                    <small>pieces altered</small>
                </div>
                <div class="sewing-overview-kpi">
                    <span>Total rejected</span>
                    <strong class="text-rose-700">{{ \App\Support\Decimal::display((string) $sandoKpis['rejected'], 0, true) }}</strong>
                    <small>pieces discarded</small>
                </div>
            </div>
            <div class="sewing-progress-grid">
                @foreach($steps as $step)
                    <div class="sewing-progress-card">
                        <span class="sewing-progress-icon"><x-icon name="{{ $step['icon'] }}" class="h-5 w-5"/></span>
                        <span class="sewing-progress-label">{{ $step['label'] }}</span>
                        <strong>{{ \App\Support\Decimal::display((string) $sandoOverview[$step['key']]['pieces'], 0, true) }} <small>pieces</small></strong>
                        <span>{{ $sandoOverview[$step['key']]['batches'] }} batches · {{ $sandoOverview[$step['key']]['executions'] }} handoffs</span>
                        @if(in_array($step['key'], ['qc_phase_1', 'qc_phase_2'], true))
                            <div class="mt-2 pt-2 border-t border-line/60 w-full flex flex-wrap gap-x-3 text-[11px] leading-tight">
                                <span class="text-emerald-700 font-semibold">Pass: {{ \App\Support\Decimal::display((string) $sandoOverview[$step['key']]['passed_pieces'], 0, true) }}</span>
                                <span class="text-blue-700 font-medium">Altered: {{ \App\Support\Decimal::display((string) $sandoOverview[$step['key']]['altered'], 0, true) }}</span>
                                <span class="text-rose-700 font-medium">Rej: {{ \App\Support\Decimal::display((string) $sandoOverview[$step['key']]['rejected'], 0, true) }}</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Underpants Overview --}}
        <div x-show="workflow === 'underpants'" x-cloak>
            <div class="sewing-overview-kpis grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 p-5 bg-[#fafafc] border-b border-line">
                <div class="sewing-overview-kpi">
                    <span>Total handoffs</span>
                    <strong>{{ $underpantsKpis['handoffs'] }}</strong>
                    <small>across stations</small>
                </div>
                <div class="sewing-overview-kpi">
                    <span>Batches logged</span>
                    <strong>{{ $underpantsKpis['batches'] }}</strong>
                    <small>recorded entries</small>
                </div>
                <div class="sewing-overview-kpi sewing-overview-kpi-primary">
                    <span>Final QC passed</span>
                    <strong class="text-emerald-700">{{ \App\Support\Decimal::display((string) $underpantsKpis['final_passed'], 0, true) }}</strong>
                    <small>ready for packing</small>
                </div>
                <div class="sewing-overview-kpi">
                    <span>Total altered</span>
                    <strong class="text-blue-700">{{ \App\Support\Decimal::display((string) $underpantsKpis['altered'], 0, true) }}</strong>
                    <small>pieces altered</small>
                </div>
                <div class="sewing-overview-kpi">
                    <span>Total rejected</span>
                    <strong class="text-rose-700">{{ \App\Support\Decimal::display((string) $underpantsKpis['rejected'], 0, true) }}</strong>
                    <small>pieces discarded</small>
                </div>
            </div>
            <div class="sewing-progress-grid">
                @foreach($underpantsSteps as $step)
                    <div class="sewing-progress-card">
                        <span class="sewing-progress-icon"><x-icon name="{{ $step['icon'] }}" class="h-5 w-5"/></span>
                        <span class="sewing-progress-label">{{ $step['label'] }}</span>
                        <strong>{{ \App\Support\Decimal::display((string) $underpantsOverview[$step['key']]['pieces'], 0, true) }} <small>pieces</small></strong>
                        <span>{{ $underpantsOverview[$step['key']]['batches'] }} batches · {{ $underpantsOverview[$step['key']]['executions'] }} handoffs</span>
                        @if(in_array($step['key'], ['qc_phase_1', 'qc_phase_2'], true))
                            <div class="mt-2 pt-2 border-t border-line/60 w-full flex flex-wrap gap-x-3 text-[11px] leading-tight">
                                <span class="text-emerald-700 font-semibold">Pass: {{ \App\Support\Decimal::display((string) $underpantsOverview[$step['key']]['passed_pieces'], 0, true) }}</span>
                                <span class="text-blue-700 font-medium">Altered: {{ \App\Support\Decimal::display((string) $underpantsOverview[$step['key']]['altered'], 0, true) }}</span>
                                <span class="text-rose-700 font-medium">Rej: {{ \App\Support\Decimal::display((string) $underpantsOverview[$step['key']]['rejected'], 0, true) }}</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    </div>
</x-app-layout>
