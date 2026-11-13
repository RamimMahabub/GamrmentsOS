<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="eyebrow flex items-center gap-2 mb-1">
                    <span class="h-2 w-2 rounded-full bg-accent animate-pulse"></span>
                    Detailed Fabric & Rolls Trace
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-ink">Lot {{ $lot->lot_number }} · Fabric & Rolls</h1>
                <p class="text-sm text-muted mt-1">Itemized rolls, cutting consumption, and real remaining balances.</p>
            </div>
            <div class="flex items-center gap-2">
                <a class="btn btn-secondary text-xs" href="{{ route('fabric.index') }}">
                    &larr; All Fabric Batches
                </a>
                <a class="btn btn-secondary text-xs" href="{{ route('lots.show', $lot) }}">
                    Lot Trace &nearr;
                </a>
                <a class="btn btn-primary shadow-sm shadow-accent/25 text-xs" href="{{ route('cutting.body.create') }}">
                    <x-icon name="scissors" class="h-3.5 w-3.5"/>
                    Send to Cutting
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Lot Fabric Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="rounded-2xl border border-line bg-surface p-5 shadow-sm">
            <span class="text-xs font-semibold text-muted uppercase tracking-wider">Returned from Dyeing</span>
            <div class="text-2xl font-bold text-ink mt-2">
                {{ $totalLotReturnedKg }} <span class="text-xs font-normal text-muted">kg</span>
            </div>
            <div class="text-xs text-muted mt-1">{{ $rollsData->count() }} itemized rolls</div>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-5 shadow-sm">
            <span class="text-xs font-semibold text-muted uppercase tracking-wider">Consumed in Cutting</span>
            <div class="text-2xl font-bold text-muted mt-2">
                {{ $totalLotUsedKg }} <span class="text-xs font-normal text-muted">kg</span>
            </div>
            <div class="text-xs text-muted mt-1">{{ $rollsData->where('cuts_count', '>', 0)->count() }} rolls engaged</div>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-5 shadow-sm">
            <span class="text-xs font-semibold text-muted uppercase tracking-wider">Current Available Fabric</span>
            <div class="text-2xl font-bold text-accent mt-2">
                {{ $totalLotRemainingKg }} <span class="text-xs font-normal text-muted">kg</span>
            </div>
            <div class="text-xs text-muted mt-1">{{ $rollsData->where('is_cut', false)->count() }} rolls ready for cutting</div>
        </div>
    </div>

    <!-- Balance by Color & DIA -->
    @if($grouped->isNotEmpty())
        <div class="panel shadow-sm border border-line bg-surface rounded-3xl overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-line flex items-center justify-between bg-surface">
                <div>
                    <h2 class="text-base font-bold text-ink">Color & DIA Fabric Balance</h2>
                    <p class="text-xs text-muted">Aggregated balances for Lot {{ $lot->lot_number }}.</p>
                </div>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>COLOR</th>
                            <th>DIA</th>
                            <th class="number">TOTAL ROLLS</th>
                            <th class="number">AVAILABLE ROLLS</th>
                            <th class="number">RETURNED (KG)</th>
                            <th class="number">CUT (KG)</th>
                            <th class="number">AVAILABLE (KG)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grouped as $grp)
                            <tr>
                                <td>
                                    <span class="inline-flex items-center gap-2">
                                        <span class="h-3 w-3 rounded-full border border-line" style="background-color: {{ $grp['hex'] }}"></span>
                                        <strong>{{ $grp['color'] }}</strong>
                                    </span>
                                </td>
                                <td><span class="pill font-mono font-bold text-xs">{{ $grp['dia'] }}</span></td>
                                <td class="number">{{ $grp['total_rolls'] }}</td>
                                <td class="number font-bold {{ $grp['available_rolls'] > 0 ? 'text-accent' : 'text-muted' }}">
                                    {{ $grp['available_rolls'] }}
                                </td>
                                <td class="number">{{ $grp['total_weight_kg'] }}</td>
                                <td class="number text-muted">{{ $grp['used_weight_kg'] }}</td>
                                <td class="number font-bold {{ (float)$grp['remaining_weight_kg'] > 0 ? 'text-accent' : 'text-muted' }}">
                                    {{ $grp['remaining_weight_kg'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Individual Roll Details Table -->
    <div class="panel shadow-sm border border-line bg-surface rounded-3xl overflow-hidden">
        <div class="px-6 py-4 border-b border-line flex items-center justify-between bg-surface">
            <div>
                <h2 class="text-base font-bold text-ink">Itemized Rolls & Cutting History</h2>
                <p class="text-xs text-muted">Actual cutting consumption per roll.</p>
            </div>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ROLL NUMBER</th>
                        <th>COLOR</th>
                        <th>DIA</th>
                        <th class="number">ORIGINAL WEIGHT</th>
                        <th class="number">CUT WEIGHT</th>
                        <th class="number">REMAINING WEIGHT</th>
                        <th>STATUS</th>
                        <th>CUTTING EXECUTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rollsData as $item)
                        @php
                            $roll = $item['roll'];
                            $colorObj = $roll->getEffectiveColor();
                            $dia = $roll->getEffectiveDia();
                        @endphp
                        <tr>
                            <td><strong class="font-mono text-ink">{{ $roll->number }}</strong></td>
                            <td>
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="h-2.5 w-2.5 rounded-full border border-line" style="background-color: {{ $colorObj?->hex ?? '#cbd9b6' }}"></span>
                                    <span>{{ $colorObj?->name ?? '—' }}</span>
                                </span>
                            </td>
                            <td><span class="pill font-mono font-bold text-xs">{{ $dia ?? '—' }}</span></td>
                            <td class="number font-medium">{{ $item['weight_kg'] }} kg</td>
                            <td class="number text-muted">{{ $item['used_weight_kg'] }} kg</td>
                            <td class="number font-bold {{ (float)$item['remaining_weight_kg'] > 0 ? 'text-accent' : 'text-zinc-400' }}">
                                {{ $item['remaining_weight_kg'] }} kg
                            </td>
                            <td>
                                @if($item['is_cut'])
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-zinc-100 text-zinc-700 border border-zinc-300">
                                        Fully Cut
                                    </span>
                                @elseif($item['is_partial'])
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-300">
                                        Partially Cut
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                                        Available (Full)
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($roll->cuttingRows->isNotEmpty())
                                    <div class="flex flex-col gap-1 text-xs">
                                        @foreach($roll->cuttingRows as $cutRow)
                                            <a class="link" href="{{ route('cutting.'.$cutRow->cutting_type.'.show', $cutRow->job) }}">
                                                {{ ucfirst($cutRow->cutting_type) }} #{{ $cutRow->cutting_job_id }} &middot; {{ $cutRow->cut_weight_kg }} kg ({{ $cutRow->job?->staff?->name ?? 'Master' }}) &nearr;
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-muted">No cuts recorded yet</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

