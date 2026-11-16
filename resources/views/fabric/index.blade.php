<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2.5">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-ink">Fabric & Rolls</h1>
                <p class="text-xs text-muted">Returned dyed fabric grouped by lot, color & DIA &mdash; ready for cutting.</p>
            </div>
            <div class="flex items-center gap-2">
                <a class="btn btn-secondary btn-xs" href="{{ route('dyeing-jobs.index') }}">
                    <x-icon name="drop" class="h-3 w-3"/>
                    Dyeing Jobs
                </a>
                <a class="btn btn-secondary btn-xs font-semibold" href="{{ route('fabric.market-lot.create') }}">
                    <x-icon name="plus" class="h-3 w-3"/>
                    + Market Rolls
                </a>
                <a class="btn btn-primary btn-xs" href="{{ route('cutting.body.create') }}">
                    <x-icon name="scissors" class="h-3 w-3"/>
                    Send to Cutting
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Compact KPI Ribbon -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 mb-3">
        <div class="flex items-center justify-between px-3 py-2 rounded-xl border border-line bg-surface shadow-2xs">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-muted block">Available Fabric</span>
                <div class="text-sm font-bold text-accent tabular-nums mt-0.5">
                    {{ $totalFabricAvailableKg }} <span class="text-xs font-normal text-muted">kg</span>
                </div>
            </div>
            <span class="pill text-[10px] font-medium">{{ $totalAvailableRolls }} rolls</span>
        </div>

        <div class="flex items-center justify-between px-3 py-2 rounded-xl border border-line bg-surface shadow-2xs">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-muted block">Cut / Processed</span>
                <div class="text-sm font-bold text-ink tabular-nums mt-0.5">
                    {{ $totalFabricCutKg }} <span class="text-xs font-normal text-muted">kg</span>
                </div>
            </div>
            <span class="text-[10px] text-muted">{{ $totalExhaustedRolls }} cut</span>
        </div>

        <div class="flex items-center justify-between px-3 py-2 rounded-xl border border-line bg-surface shadow-2xs">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-muted block">Total Returned</span>
                <div class="text-sm font-bold text-ink tabular-nums mt-0.5">
                    {{ $totalFabricReturnedKg }} <span class="text-xs font-normal text-muted">kg</span>
                </div>
            </div>
            <x-icon name="roll" class="h-3.5 w-3.5 text-muted"/>
        </div>

        <div class="flex items-center justify-between px-3 py-2 rounded-xl border border-line bg-surface shadow-2xs">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-muted block">Active Lots</span>
                <div class="text-sm font-bold text-ink tabular-nums mt-0.5">
                    {{ $lotsCount }} <span class="text-xs font-normal text-muted">lots</span>
                </div>
            </div>
            <span class="pill font-mono font-bold text-[10px]">IN STOCK</span>
        </div>
    </div>

    <!-- Data Panel -->
    <div class="panel shadow-2xs border border-line bg-surface rounded-xl overflow-hidden">
        <!-- Toolbar & Filter -->
        <div class="px-3.5 py-2 border-b border-line/70 bg-gradient-to-r from-surface via-raised/30 to-surface flex flex-wrap items-center justify-between gap-2.5">
            <form method="GET" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="status" value="{{ $status }}">

                <div class="relative min-w-[220px]">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-muted">
                        <x-icon name="search" class="h-3.5 w-3.5"/>
                    </span>
                    <input type="text"
                           name="q"
                           value="{{ $search }}"
                           placeholder="Search lot #, house, color..."
                           class="w-full pl-8 pr-3 py-1 text-xs bg-surface border border-line rounded-lg focus:border-accent transition">
                </div>

                <div class="flex items-center gap-1.5">
                    <button type="submit" class="btn btn-secondary btn-xs">
                        <x-icon name="sliders" class="h-3 w-3 text-accent"/>
                        Filter
                    </button>

                    @if($search || $status !== 'all')
                        <a href="{{ route('fabric.index') }}" class="text-xs text-muted hover:text-ink font-medium px-1.5">Reset</a>
                    @endif
                </div>
            </form>

            <div class="flex items-center gap-1.5">
                <a href="{{ route('fabric.index', array_merge(request()->query(), ['status' => 'all'])) }}"
                   class="pill text-xs font-medium {{ $status === 'all' ? 'ring-1 ring-accent font-bold bg-raised' : 'hover:bg-raised' }}">
                    All Lots
                </a>
                <a href="{{ route('fabric.index', array_merge(request()->query(), ['status' => 'available'])) }}"
                   class="pill text-xs font-medium {{ $status === 'available' ? 'ring-1 ring-accent font-bold bg-raised' : 'hover:bg-raised' }}">
                    Available
                </a>
                <a href="{{ route('fabric.index', array_merge(request()->query(), ['status' => 'exhausted'])) }}"
                   class="pill text-xs font-medium {{ $status === 'exhausted' ? 'ring-1 ring-accent font-bold bg-raised' : 'hover:bg-raised' }}">
                    Fully Cut
                </a>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr class="border-b border-line">
                        <th class="w-10 text-center font-mono">#</th>
                        <th>Lot Identity</th>
                        <th>Dyeing House</th>
                        <th>Colors & DIAs</th>
                        <th class="number">Returned Fabric</th>
                        <th class="number">Cut Fabric</th>
                        <th class="number">Available Fabric</th>
                        <th>Rolls Status</th>
                        <th class="number pr-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e4e6ec]">
                    @forelse($lotSummaries as $index => $item)
                        @php
                            $lot = $item['lot'];
                        @endphp
                        <tr class="group transition-all duration-120 hover:bg-[#edf3dc] even:bg-[#f8f9fc]">
                            <!-- Row Index -->
                            <td class="text-center font-mono text-xs text-muted/80">
                                {{ str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) }}
                            </td>

                            <!-- Lot Identity -->
                            <td>
                                <div class="flex items-center gap-1.5">
                                    <a class="inline-flex items-center gap-1.5 font-mono font-bold text-xs px-2 py-0.5 rounded-lg bg-purple-50 border border-purple-200 text-purple-900 hover:bg-purple-100 transition" href="{{ route('fabric.show', $lot) }}">
                                        <x-icon name="roll" class="h-3 w-3 text-purple-600"/>
                                        {{ $lot->lot_number }}
                                    </a>
                                    @if($lot->product_type !== 'sando')
                                        <span class="pill text-[10px] font-bold uppercase bg-amber-50 text-amber-900 border border-amber-300">
                                            {{ ucfirst($lot->product_type) }}
                                        </span>
                                    @endif
                                    @if($lot->fabric_source === 'market_purchase')
                                        <span class="text-[10px] text-blue-700 font-semibold">Market</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Dyeing Partner -->
                            <td class="text-xs font-medium text-ink">
                                @if($item['dyeing_houses']->isNotEmpty())
                                    {{ $item['dyeing_houses']->implode(', ') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <!-- Colors & DIAs -->
                            <td>
                                <div class="flex flex-wrap items-center gap-1 max-w-sm">
                                    @foreach($item['colors'] as $c)
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded bg-surface border border-line text-[11px] font-medium text-ink">
                                            <span class="h-1.5 w-1.5 rounded-full border border-line shrink-0" style="background-color: {{ $c['hex'] }}"></span>
                                            {{ $c['name'] }}
                                        </span>
                                    @endforeach
                                    @foreach($item['dias'] as $dia)
                                        <span class="pill font-mono text-[10px] px-1 py-0.2">D{{ $dia }}</span>
                                    @endforeach
                                </div>
                            </td>

                            <!-- Returned Weight -->
                            <td class="number">
                                <span class="font-bold tabular-nums text-ink text-xs">{{ $item['returned_kg'] }}</span>
                                <span class="text-[10px] text-muted">kg</span>
                            </td>

                            <!-- Cut Weight -->
                            <td class="number">
                                <span class="font-medium tabular-nums text-muted text-xs">{{ $item['cut_weight_kg'] }}</span>
                                <span class="text-[10px] text-muted">kg</span>
                            </td>

                            <!-- Available Weight -->
                            <td class="number">
                                @if(! $item['is_exhausted'])
                                    <span class="font-bold tabular-nums text-accent text-xs">{{ $item['remaining_kg'] }}</span>
                                    <span class="text-[10px] text-muted">kg</span>
                                @else
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-zinc-100 text-zinc-600 border border-zinc-200">
                                        All Cut
                                    </span>
                                @endif
                            </td>

                            <!-- Rolls Status -->
                            <td>
                                @if($item['total_rolls'] > 0)
                                    <span class="text-xs">
                                        <strong class="{{ $item['available_rolls'] > 0 ? 'text-emerald-700' : 'text-zinc-500' }}">
                                            {{ $item['available_rolls'] }}
                                        </strong>
                                        <span class="text-muted text-[11px]">/{{ $item['total_rolls'] }} rolls</span>
                                        @if($item['partial_rolls'] > 0)
                                            <span class="text-[10px] text-amber-600">({{ $item['partial_rolls'] }} part)</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-xs text-muted">—</span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="number pr-4 whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    <a class="btn btn-secondary btn-xs" href="{{ route('fabric.show', $lot) }}">
                                        View Details
                                    </a>
                                    @if(! $item['is_exhausted'])
                                        @if($lot->product_type !== 'sando')
                                            <a class="btn btn-primary btn-xs" href="{{ route('cutting.cutting.create') }}">
                                                Cut ({{ ucfirst($lot->product_type) }})
                                            </a>
                                        @else
                                            <a class="btn btn-primary btn-xs" href="{{ route('cutting.body.create') }}">
                                                Cut Body
                                            </a>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-10 text-center text-muted">
                                <div class="max-w-sm mx-auto">
                                    <x-icon name="roll" class="h-8 w-8 mx-auto text-muted/40 mb-2"/>
                                    <h3 class="text-sm font-bold text-ink">No dyed fabric batches found</h3>
                                    <p class="text-xs text-muted mt-0.5">Batches appear here once fabric returns are recorded in Dyeing Jobs.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
