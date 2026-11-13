<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="eyebrow flex items-center gap-2 mb-1">
                    <span class="h-2 w-2 rounded-full bg-accent animate-pulse"></span>
                    Post-Dyeing Fabric Inventory
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-ink">Fabric & Rolls</h1>
                <p class="text-sm text-muted mt-1">Returned dyed fabric grouped by lot, color & DIA — ready for cutting.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a class="btn btn-secondary text-xs" href="{{ route('dyeing-jobs.index') }}">
                    <x-icon name="drop" class="h-3.5 w-3.5"/>
                    Dyeing Jobs
                </a>
                <a class="btn btn-secondary text-xs font-semibold text-ink" href="{{ route('fabric.market-lot.create') }}">
                    <x-icon name="plus" class="h-3.5 w-3.5"/>
                    + Market Rolls (Underpants)
                </a>
                <a class="btn btn-primary shadow-sm shadow-accent/25 text-xs" href="{{ route('cutting.body.create') }}">
                    <x-icon name="scissors" class="h-3.5 w-3.5"/>
                    Send Fabric to Cutting
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Top KPI Highlights -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="rounded-2xl border border-line bg-surface p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted uppercase tracking-wider">Available Fabric</span>
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
            </div>
            <div class="text-2xl font-bold text-accent mt-2">
                {{ $totalFabricAvailableKg }} <span class="text-xs font-normal text-muted">kg</span>
            </div>
            <div class="text-xs text-muted mt-1">
                {{ $totalAvailableRolls }} available / partial rolls
            </div>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted uppercase tracking-wider">Cut / Processed</span>
                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
            </div>
            <div class="text-2xl font-bold text-ink mt-2">
                {{ $totalFabricCutKg }} <span class="text-xs font-normal text-muted">kg</span>
            </div>
            <div class="text-xs text-muted mt-1">
                {{ $totalExhaustedRolls }} rolls fully cut
            </div>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted uppercase tracking-wider">Total Returned</span>
                <x-icon name="roll" class="h-4 w-4 text-muted"/>
            </div>
            <div class="text-2xl font-bold text-ink mt-2">
                {{ $totalFabricReturnedKg }} <span class="text-xs font-normal text-muted">kg</span>
            </div>
            <div class="text-xs text-muted mt-1">
                From completed dyeing returns
            </div>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted uppercase tracking-wider">Active Lots</span>
                <span class="pill font-mono font-bold text-xs bg-raised border border-line">Lots</span>
            </div>
            <div class="text-2xl font-bold text-ink mt-2">
                {{ $lotsCount }} <span class="text-xs font-normal text-muted">lots</span>
            </div>
            <div class="text-xs text-muted mt-1">
                With dyed fabric inventory
            </div>
        </div>
    </div>

    <!-- Futuristic Data Panel (Matching Dyeing Index Layout) -->
    <div class="panel shadow-sm border border-line bg-surface rounded-3xl overflow-hidden">
        <!-- Toolbar & Filter -->
        <div class="px-6 py-5 border-b border-line/70 bg-gradient-to-r from-surface via-raised/40 to-surface flex flex-wrap items-center justify-between gap-4">
            <form method="GET" class="flex flex-wrap items-center gap-3">
                <input type="hidden" name="status" value="{{ $status }}">

                <div class="relative min-w-[260px]">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-muted">
                        <x-icon name="search" class="h-4 w-4"/>
                    </span>
                    <input type="text"
                           name="q"
                           value="{{ $search }}"
                           placeholder="Search lot #, dyeing house, color..."
                           class="w-full pl-10 pr-4 py-2 text-sm bg-surface border border-line rounded-xl focus:border-accent focus:ring-2 focus:ring-accent/20 transition shadow-2xs">
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-ink text-surface hover:bg-neutral-800 transition shadow-sm">
                        <x-icon name="sliders" class="h-3.5 w-3.5 text-[#d5ff27]"/>
                        Filter
                    </button>

                    @if($search || $status !== 'all')
                        <a href="{{ route('fabric.index') }}" class="text-xs text-muted hover:text-ink font-medium px-2 py-1">Reset</a>
                    @endif
                </div>
            </form>

            <div class="flex items-center gap-2">
                <a href="{{ route('fabric.index', array_merge(request()->query(), ['status' => 'all'])) }}"
                   class="pill text-xs font-semibold {{ $status === 'all' ? 'ring-2 ring-accent font-bold bg-raised' : 'hover:bg-raised' }}">
                    All Lots
                </a>
                <a href="{{ route('fabric.index', array_merge(request()->query(), ['status' => 'available'])) }}"
                   class="pill text-xs font-semibold {{ $status === 'available' ? 'ring-2 ring-accent font-bold bg-raised' : 'hover:bg-raised' }}">
                    Available Fabric
                </a>
                <a href="{{ route('fabric.index', array_merge(request()->query(), ['status' => 'exhausted'])) }}"
                   class="pill text-xs font-semibold {{ $status === 'exhausted' ? 'ring-2 ring-accent font-bold bg-raised' : 'hover:bg-raised' }}">
                    Fully Cut
                </a>
            </div>
        </div>

        <!-- Table Container (Dyeing Type Overview) -->
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr class="border-b border-line">
                        <th class="w-12 text-center text-[10px] font-mono tracking-widest text-muted uppercase">#</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Lot Identity</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Dyeing House</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Colors & DIAs</th>
                        <th class="number text-[10px] font-bold tracking-widest text-muted uppercase">Returned Fabric</th>
                        <th class="number text-[10px] font-bold tracking-widest text-muted uppercase">Cut Fabric</th>
                        <th class="number text-[10px] font-bold tracking-widest text-muted uppercase">Available Fabric</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Rolls Status</th>
                        <th class="number pr-6 text-[10px] font-bold tracking-widest text-muted uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e2e4ea]">
                    @forelse($lotSummaries as $index => $item)
                        @php
                            $lot = $item['lot'];
                        @endphp
                        <tr class="group transition-all duration-150 hover:bg-[#edf3dc] even:bg-[#f7f9fc]">
                            <!-- Row Index -->
                            <td class="text-center font-mono text-xs text-muted/80">
                                {{ str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) }}
                            </td>

                            <!-- Lot Identity -->
                            <td>
                                <div class="flex items-center gap-2">
                                    <a class="inline-flex items-center gap-2 font-mono font-bold text-xs px-3 py-1.5 rounded-xl bg-purple-50 border border-purple-200 text-purple-900 hover:bg-purple-100 transition shadow-2xs" href="{{ route('fabric.show', $lot) }}">
                                        <x-icon name="roll" class="h-3.5 w-3.5 text-purple-600"/>
                                        {{ $lot->lot_number }}
                                    </a>
                                    @if($lot->product_type !== 'sando')
                                        <span class="pill text-[10px] font-bold uppercase bg-amber-50 text-amber-900 border border-amber-300">
                                            {{ ucfirst($lot->product_type) }}
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    @if($lot->fabric_source === 'market_purchase')
                                        <span class="text-[11px] text-blue-700 font-semibold">Market Rolls</span>
                                    @elseif($item['latest_return_date'])
                                        <span class="text-[11px] text-muted">Returned {{ $item['latest_return_date']->format('d M Y') }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Dyeing Partner -->
                            <td>
                                @if($item['dyeing_houses']->isNotEmpty())
                                    <div class="font-medium text-ink text-sm">{{ $item['dyeing_houses']->implode(', ') }}</div>
                                @else
                                    <span class="text-muted text-xs">—</span>
                                @endif
                            </td>

                            <!-- Colors & DIAs -->
                            <td>
                                <div class="flex flex-wrap items-center gap-1.5 max-w-xs">
                                    @foreach($item['colors'] as $c)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-surface border border-line text-xs font-medium text-ink shadow-2xs">
                                            <span class="h-2 w-2 rounded-full border border-line shrink-0" style="background-color: {{ $c['hex'] }}"></span>
                                            {{ $c['name'] }}
                                        </span>
                                    @endforeach
                                </div>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach($item['dias'] as $dia)
                                        <span class="pill font-mono text-[10px] px-1.5 py-0.2">DIA {{ $dia }}</span>
                                    @endforeach
                                </div>
                            </td>

                            <!-- Returned Weight -->
                            <td class="number">
                                <div class="font-bold tabular-nums text-ink text-sm">
                                    {{ $item['returned_kg'] }} <span class="text-xs font-normal text-muted">kg</span>
                                </div>
                            </td>

                            <!-- Cut Weight -->
                            <td class="number">
                                <div class="font-semibold tabular-nums text-muted text-sm">
                                    {{ $item['cut_weight_kg'] }} <span class="text-xs font-normal text-muted">kg</span>
                                </div>
                            </td>

                            <!-- Available Weight -->
                            <td class="number">
                                @if(! $item['is_exhausted'])
                                    <div class="font-bold tabular-nums text-accent text-sm">
                                        {{ $item['remaining_kg'] }} <span class="text-xs font-normal text-muted">kg</span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-zinc-100 text-zinc-600 border border-zinc-200">
                                        0.000 kg (All Cut)
                                    </span>
                                @endif
                            </td>

                            <!-- Rolls Status -->
                            <td>
                                @if($item['total_rolls'] > 0)
                                    <div class="text-xs">
                                        <strong class="{{ $item['available_rolls'] > 0 ? 'text-emerald-700' : 'text-zinc-500' }}">
                                            {{ $item['available_rolls'] }} available
                                        </strong>
                                        <span class="text-muted">/ {{ $item['total_rolls'] }} rolls</span>
                                        @if($item['partial_rolls'] > 0)
                                            <span class="block text-[11px] text-amber-600">({{ $item['partial_rolls'] }} partially cut)</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-muted">No rolls itemized</span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="number pr-6">
                                <div class="inline-flex items-center gap-2">
                                    <a class="inline-flex items-center px-3 py-1.5 rounded-xl border border-line bg-surface text-xs font-semibold text-ink hover:border-accent hover:text-accent shadow-2xs transition"
                                       href="{{ route('fabric.show', $lot) }}">
                                        View Details &rarr;
                                    </a>
                                    @if(! $item['is_exhausted'])
                                        @if($lot->product_type !== 'sando')
                                            <a class="inline-flex items-center px-3 py-1.5 rounded-xl border border-transparent bg-accent text-xs font-semibold text-black hover:opacity-90 shadow-2xs transition"
                                               href="{{ route('cutting.cutting.create') }}">
                                                Cut ({{ ucfirst($lot->product_type) }})
                                            </a>
                                        @else
                                            <a class="inline-flex items-center px-3 py-1.5 rounded-xl border border-transparent bg-accent text-xs font-semibold text-black hover:opacity-90 shadow-2xs transition"
                                               href="{{ route('cutting.body.create') }}">
                                                Cut Body
                                            </a>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-12 text-center text-muted">
                                <div class="max-w-sm mx-auto">
                                    <x-icon name="roll" class="h-10 w-10 mx-auto text-muted/40 mb-3"/>
                                    <h3 class="text-base font-bold text-ink">No dyed fabric batches found</h3>
                                    <p class="text-xs text-muted mt-1">Batches appear here once fabric returns are recorded in Dyeing Jobs.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
