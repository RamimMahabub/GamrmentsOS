<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2.5">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-ink">Production Overview</h1>
                <p class="text-xs text-muted">Real-time telemetry of yarn inventory, active knitting programs, dyeing lots, and cutting.</p>
            </div>
            <div class="flex items-center gap-2">
                <a class="btn btn-secondary btn-xs" href="{{ route('yarns.create') }}">
                    <x-icon name="spool" class="h-3 w-3"/>
                    Add Yarn
                </a>
                <a class="btn btn-primary btn-xs" href="{{ route('knitting-jobs.create') }}">
                    <x-icon name="plus" class="h-3 w-3"/>
                    Assign Knitting
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Manufacturing Stage Pipeline Flow Visualizer -->
    <section class="mb-3.5 rounded-xl border border-line bg-surface p-3.5 shadow-2xs" aria-label="Production pipeline status">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-2.5">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-muted">End-to-End Production Stages</span>
                <h3 class="text-sm font-bold text-ink">Floor Flow & Pipeline Health</h3>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-accent-soft text-accent-ink font-semibold text-[11px]">
                    <span class="h-1.5 w-1.5 rounded-full bg-accent"></span>
                    {{ $activeLotsCount }} Active Lots Tracked
                </span>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2">
            <!-- Stage 1: Warehouse / Yarn -->
            <a href="{{ route('yarns.index') }}" class="group relative block rounded-xl border border-line bg-raised/50 p-2.5 transition-all duration-120 hover:border-accent-line hover:bg-surface shadow-2xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-surface border border-line text-ink group-hover:text-accent">
                        <x-icon name="spool" class="h-3 w-3"/>
                    </span>
                    <span class="text-[10px] font-mono font-medium px-1.5 py-0.2 rounded bg-surface border border-line text-muted">01</span>
                </div>
                <div class="text-[10px] font-bold text-muted uppercase tracking-wider">Yarn Inventory</div>
                <div class="text-sm font-bold text-ink mt-0.5">{{ \App\Support\Decimal::display($totalKg) }} <span class="text-[10px] font-normal text-muted">kg</span></div>
                <div class="mt-2 pt-1.5 border-t border-line/60 flex items-center justify-between text-[11px]">
                    <span class="link text-[11px] group-hover:underline">Stock &rarr;</span>
                    <span class="text-muted">{{ \App\Support\Decimal::display(\App\Modules\Yarn\UnitConverter::fromKg($totalKg,'lb')) }} lb</span>
                </div>
            </a>

            <!-- Stage 2: Knitting -->
            <a href="{{ route('knitting-jobs.index') }}" class="group relative block rounded-xl border border-line bg-raised/50 p-2.5 transition-all duration-120 hover:border-accent-line hover:bg-surface shadow-2xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-surface border border-line text-ink group-hover:text-accent">
                        <x-icon name="layers" class="h-3 w-3"/>
                    </span>
                    <span class="text-[10px] font-mono font-medium px-1.5 py-0.2 rounded bg-surface border border-line text-muted">02</span>
                </div>
                <div class="text-[10px] font-bold text-muted uppercase tracking-wider">In Knitting</div>
                <div class="text-sm font-bold text-ink mt-0.5">{{ $counts['knitting'] ?? 0 }} <span class="text-[10px] font-normal text-muted">lots</span></div>
                <div class="mt-2 pt-1.5 border-t border-line/60 flex items-center justify-between text-[11px]">
                    <span class="link text-[11px] group-hover:underline">Knitting &rarr;</span>
                    <span class="pill text-[10px]">Circular</span>
                </div>
            </a>

            <!-- Stage 3: Dyeing -->
            <a href="{{ route('dyeing-jobs.index') }}" class="group relative block rounded-xl border border-line bg-raised/50 p-2.5 transition-all duration-120 hover:border-accent-line hover:bg-surface shadow-2xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-surface border border-line text-ink group-hover:text-accent">
                        <x-icon name="drop" class="h-3 w-3"/>
                    </span>
                    <span class="text-[10px] font-mono font-medium px-1.5 py-0.2 rounded bg-surface border border-line text-muted">03</span>
                </div>
                <div class="text-[10px] font-bold text-muted uppercase tracking-wider">In Dyeing</div>
                <div class="text-sm font-bold text-ink mt-0.5">{{ $counts['dyeing'] ?? 0 }} <span class="text-[10px] font-normal text-muted">lots</span></div>
                <div class="mt-2 pt-1.5 border-t border-line/60 flex items-center justify-between text-[11px]">
                    <span class="link text-[11px] group-hover:underline">Dyeing &rarr;</span>
                    <span class="pill text-[10px]">Finishing</span>
                </div>
            </a>

            <!-- Stage 4: Cutting -->
            <a href="{{ route('cutting.body.index') }}" class="group relative block rounded-xl border border-line bg-raised/50 p-2.5 transition-all duration-120 hover:border-accent-line hover:bg-surface shadow-2xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-surface border border-line text-ink group-hover:text-accent">
                        <x-icon name="scissors" class="h-3 w-3"/>
                    </span>
                    <span class="text-[10px] font-mono font-medium px-1.5 py-0.2 rounded bg-surface border border-line text-muted">04</span>
                </div>
                <div class="text-[10px] font-bold text-muted uppercase tracking-wider">Cutting Section</div>
                <div class="text-sm font-bold text-ink mt-0.5">{{ $availableRollsCount }} <span class="text-[10px] font-normal text-muted">rolls ready</span></div>
                <div class="mt-2 pt-1.5 border-t border-line/60 flex items-center justify-between text-[11px]">
                    <span class="link text-[11px] group-hover:underline">Cutting &rarr;</span>
                    <span class="pill text-[10px]">Rolls</span>
                </div>
            </a>

            <!-- Stage 5: Sewing -->
            <a href="{{ route('sewing-jobs.index') }}" class="group relative block rounded-xl border border-line bg-raised/50 p-2.5 transition-all duration-120 hover:border-accent-line hover:bg-surface shadow-2xs col-span-2 sm:col-span-1">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-surface border border-line text-ink group-hover:text-accent">
                        <x-icon name="factory" class="h-3 w-3"/>
                    </span>
                    <span class="text-[10px] font-mono font-medium px-1.5 py-0.2 rounded bg-surface border border-line text-muted">05</span>
                </div>
                <div class="text-[10px] font-bold text-muted uppercase tracking-wider">Sewing Assembly</div>
                <div class="text-sm font-bold text-ink mt-0.5">{{ $counts['sewing'] ?? 0 }} <span class="text-[10px] font-normal text-muted">lots</span></div>
                <div class="mt-2 pt-1.5 border-t border-line/60 flex items-center justify-between text-[11px]">
                    <span class="link text-[11px] group-hover:underline">Stations &rarr;</span>
                    <span class="pill text-[10px]">Assembly</span>
                </div>
            </a>
        </div>
    </section>

    <!-- Key Metrics Grid -->
    <div class="mb-3.5 grid gap-2.5 grid-cols-2 lg:grid-cols-4">
        <!-- Metric 1: Total Stock (Hero Lime) -->
        <a href="{{ route('yarns.index') }}" class="metric metric-hero block cursor-pointer transition-all duration-120 hover:brightness-105">
            <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-[#283000]">
                <span>Yarn Available</span>
                <span class="metric-icon"><x-icon name="spool" class="h-3.5 w-3.5"/></span>
            </div>
            <div class="metric-value">{{ \App\Support\Decimal::display($totalKg) }} <span class="text-xs font-normal text-[#404c00]">kg</span></div>
            <div class="mt-1 flex items-center justify-between text-[11px] text-[#353e00]">
                <span>{{ \App\Support\Decimal::display(\App\Modules\Yarn\UnitConverter::fromKg($totalKg,'lb')) }} lb</span>
                <span class="font-medium underline decoration-current/40">{{ $yarns->count() }} items &rarr;</span>
            </div>
        </a>

        <!-- Metric 2: Active Lots (Dark) -->
        <a href="{{ route('knitting-jobs.index') }}" class="metric metric-dark block cursor-pointer transition-all duration-120 hover:brightness-110">
            <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-neutral-400">
                <span>Active Lots</span>
                <span class="metric-icon"><x-icon name="layers" class="h-3.5 w-3.5"/></span>
            </div>
            <div class="metric-value">{{ $activeLotsCount }} <span class="text-xs font-normal text-neutral-400">/ {{ $totalLotsCount }}</span></div>
            <div class="mt-1 flex items-center justify-between text-[11px] text-neutral-400">
                <span class="truncate">{{ collect(['knitting', 'dyeing', 'cutting', 'sewing', 'packing'])->map(fn($st) => ($counts[$st] ?? 0) . ' ' . ucfirst($st))->filter(fn($txt) => !str_starts_with($txt, '0 '))->join(' • ') ?: 'No active lots' }}</span>
                <span class="text-[#d5ff27] font-medium underline shrink-0 ml-1">Lots &rarr;</span>
            </div>
        </a>

        <!-- Metric 3: Fabric Rolls -->
        <a href="{{ route('cutting.body.create') }}" class="metric block cursor-pointer transition-all duration-120 hover:border-accent-line">
            <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-muted">
                <span>Rolls In Stock</span>
                <span class="metric-icon"><x-icon name="drop" class="h-3.5 w-3.5"/></span>
            </div>
            <div class="metric-value">{{ $availableRollsCount }} <span class="text-xs font-normal text-muted">/ {{ $totalRollsCount }}</span></div>
            <div class="mt-1 flex items-center justify-between text-[11px] text-muted">
                <span>For cutting</span>
                <span class="link font-semibold">Cut now &rarr;</span>
            </div>
        </a>

        <!-- Metric 4: Mill Partners -->
        <a href="{{ route('companies.index') }}" class="metric block cursor-pointer transition-all duration-120 hover:border-accent-line">
            <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-muted">
                <span>Active Partners</span>
                <span class="metric-icon"><x-icon name="building" class="h-3.5 w-3.5"/></span>
            </div>
            <div class="metric-value">{{ $activePartnersCount }}</div>
            <div class="mt-1 flex items-center justify-between text-[11px] text-muted">
                <span>Knitting & Dyeing</span>
                <span class="link font-semibold">Directory &rarr;</span>
            </div>
        </a>
    </div>

    <!-- Data Tables Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3.5">
        <!-- Recent Lots -->
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>Recent Production Lots</h2>
                    <p class="text-[11px] text-muted">Trace fabric progress from yarn input to rolls</p>
                </div>
                <a href="{{ route('knitting-jobs.index') }}" class="link text-xs">View all &rarr;</a>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>LOT NUMBER</th>
                            <th>ENTRY DATE</th>
                            <th>PARTNER COMPANY</th>
                            <th>CURRENT STAGE</th>
                            <th class="number">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lots as $lot)
                            <tr>
                                <td>
                                    <a class="pill font-mono font-semibold" href="{{ route('lots.show',$lot) }}">
                                        {{ $lot->lot_number }}
                                    </a>
                                </td>
                                <td class="text-muted text-xs">{{ $lot->lot_date->format('d M Y') }}</td>
                                <td class="font-medium text-ink">{{ $lot->knittingJob?->company?->name ?? '—' }}</td>
                                <td>
                                    @php
                                    $stageClasses = match($lot->current_stage) {
                                        'dispatched' => 'bg-emerald-100 text-emerald-900 border border-emerald-300 font-bold',
                                        'packing' => 'bg-indigo-100 text-indigo-800',
                                        'sewing' => 'bg-emerald-100 text-emerald-800',
                                        'cutting' => 'bg-blue-100 text-blue-800',
                                        'dyeing' => 'bg-purple-100 text-purple-800',
                                        default => 'bg-lime-100 text-lime-900',
                                    };
                                    $dotClasses = match($lot->current_stage) {
                                        'dispatched' => 'bg-emerald-600',
                                        'packing' => 'bg-indigo-600',
                                        'sewing' => 'bg-emerald-600',
                                        'cutting' => 'bg-blue-600',
                                        'dyeing' => 'bg-purple-600',
                                        default => 'bg-lime-600',
                                    };
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $stageClasses }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $dotClasses }}"></span>
                                        {{ ucfirst($lot->current_stage) }}
                                    </span>
                                </td>
                                <td class="number">
                                    <a class="link text-xs inline-flex items-center gap-1" href="{{ route('lots.show',$lot) }}">
                                        Trace
                                        <x-icon name="arrow" class="h-3 w-3"/>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty">Your first lot starts with a knitting assignment.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Yarn on Hand -->
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>Warehouse Yarn Inventory</h2>
                    <p class="text-xs text-muted mt-0.5">Physical yarn counts and bag balances</p>
                </div>
                <a href="{{ route('yarns.index') }}" class="link text-xs">Open inventory →</a>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>YARN ITEM</th>
                            <th>SPECIFICATION</th>
                            <th class="number">AVAILABLE (KG)</th>
                            <th class="number">AVAILABLE (LB)</th>
                            <th class="number">BAGS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($yarns as $yarn)
                            <tr>
                                <td>
                                    <a class="link font-medium" href="{{ route('yarns.show',$yarn) }}">
                                        {{ $yarn->name }}
                                    </a>
                                </td>
                                <td class="text-muted text-xs">{{ $yarn->yarn_type }} &middot; {{ $yarn->country_of_origin }}</td>
                                <td class="number font-semibold text-ink">{{ \App\Support\Decimal::display((string)($yarn->available_kg ?? '0')) }}</td>
                                <td class="number text-muted">{{ \App\Support\Decimal::display(\App\Modules\Yarn\UnitConverter::fromKg((string)($yarn->available_kg ?? '0'),'lb')) }}</td>
                                <td class="number">
                                    <span class="pill font-mono">
                                        {{ \App\Support\Decimal::display(\App\Modules\Yarn\UnitConverter::bags((string)($yarn->available_kg ?? '0'),$yarn->bag_size_kg),2,true) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty">Add yarn to begin tracking your inventory.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
