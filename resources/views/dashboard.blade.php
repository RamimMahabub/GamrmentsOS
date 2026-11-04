<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="eyebrow flex items-center gap-2 mb-1">
                    <span class="h-2 w-2 rounded-full bg-accent animate-pulse"></span>
                    Live Manufacturing Command Center
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-ink">Production Overview</h1>
                <p class="text-sm text-muted mt-1">Real-time tracking of yarn inventory, active knitting programs, dyeing lots, and cutting batches.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a class="btn btn-secondary shadow-sm" href="{{ route('yarns.create') }}">
                    <x-icon name="spool" class="h-4 w-4"/>
                    Add Yarn
                </a>
                <a class="btn btn-primary shadow-sm shadow-accent/20" href="{{ route('knitting-jobs.create') }}">
                    <x-icon name="plus" class="h-4 w-4"/>
                    Assign Knitting Job
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Manufacturing Stage Pipeline Flow Visualizer -->
    <section class="mb-8 rounded-3xl border border-line bg-surface p-6 shadow-sm" aria-label="Production pipeline status">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-muted">End-to-End Production Stages</span>
                <h3 class="text-lg font-bold text-ink mt-0.5">Floor Flow & Pipeline Health</h3>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-accent-soft text-accent-ink font-semibold">
                    <span class="h-1.5 w-1.5 rounded-full bg-accent"></span>
                    {{ $totalLotsCount }} Active Lots Tracked
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Stage 1: Warehouse / Yarn -->
            <a href="{{ route('yarns.index') }}" class="group relative block rounded-2xl border border-line bg-raised/50 p-4 transition-all duration-150 hover:-translate-y-0.5 hover:border-accent-line hover:bg-surface hover:shadow-md cursor-pointer focus-visible:outline-2 focus-visible:outline-accent">
                <div class="flex items-center justify-between mb-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-surface border border-line text-ink shadow-2xs group-hover:border-accent-line group-hover:text-accent">
                        <x-icon name="spool" class="h-4 w-4"/>
                    </span>
                    <span class="text-[11px] font-mono font-medium px-2 py-0.5 rounded bg-surface border border-line text-muted">Step 01</span>
                </div>
                <div class="text-xs font-semibold text-muted uppercase tracking-wider">Yarn Inventory</div>
                <div class="text-xl font-bold text-ink mt-1">{{ \App\Support\Decimal::display($totalKg) }} <span class="text-xs font-normal text-muted">kg</span></div>
                <div class="text-xs text-muted mt-1">{{ $yarns->count() }} items in warehouse stock</div>
                <div class="mt-4 pt-3 border-t border-line/60 flex items-center justify-between text-xs">
                    <span class="link group-hover:underline">View stock →</span>
                    <span class="text-muted">{{ \App\Support\Decimal::display(\App\Modules\Yarn\UnitConverter::fromKg($totalKg,'lb')) }} lb</span>
                </div>
            </a>

            <!-- Stage 2: Knitting -->
            <a href="{{ route('knitting-jobs.index') }}" class="group relative block rounded-2xl border border-line bg-raised/50 p-4 transition-all duration-150 hover:-translate-y-0.5 hover:border-accent-line hover:bg-surface hover:shadow-md cursor-pointer focus-visible:outline-2 focus-visible:outline-accent">
                <div class="flex items-center justify-between mb-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-surface border border-line text-ink shadow-2xs group-hover:border-accent-line group-hover:text-accent">
                        <x-icon name="layers" class="h-4 w-4"/>
                    </span>
                    <span class="text-[11px] font-mono font-medium px-2 py-0.5 rounded bg-surface border border-line text-muted">Step 02</span>
                </div>
                <div class="text-xs font-semibold text-muted uppercase tracking-wider">In Knitting</div>
                <div class="text-xl font-bold text-ink mt-1">{{ $counts['knitting'] ?? 0 }} <span class="text-xs font-normal text-muted">lots</span></div>
                <div class="text-xs text-muted mt-1">Machine allocations & DIA setups</div>
                <div class="mt-4 pt-3 border-t border-line/60 flex items-center justify-between text-xs">
                    <span class="link group-hover:underline">Knitting jobs →</span>
                    <span class="pill">Circular</span>
                </div>
            </a>

            <!-- Stage 3: Dyeing -->
            <a href="{{ route('dyeing-jobs.index') }}" class="group relative block rounded-2xl border border-line bg-raised/50 p-4 transition-all duration-150 hover:-translate-y-0.5 hover:border-accent-line hover:bg-surface hover:shadow-md cursor-pointer focus-visible:outline-2 focus-visible:outline-accent">
                <div class="flex items-center justify-between mb-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-surface border border-line text-ink shadow-2xs group-hover:border-accent-line group-hover:text-accent">
                        <x-icon name="drop" class="h-4 w-4"/>
                    </span>
                    <span class="text-[11px] font-mono font-medium px-2 py-0.5 rounded bg-surface border border-line text-muted">Step 03</span>
                </div>
                <div class="text-xs font-semibold text-muted uppercase tracking-wider">In Dyeing</div>
                <div class="text-xl font-bold text-ink mt-1">{{ $counts['dyeing'] ?? 0 }} <span class="text-xs font-normal text-muted">lots</span></div>
                <div class="text-xs text-muted mt-1">Color batches & finishing return</div>
                <div class="mt-4 pt-3 border-t border-line/60 flex items-center justify-between text-xs">
                    <span class="link group-hover:underline">Dyeing jobs →</span>
                    <span class="pill">Finishing</span>
                </div>
            </a>

            <!-- Stage 4: Cutting -->
            <a href="{{ route('cutting.body.index') }}" class="group relative block rounded-2xl border border-line bg-raised/50 p-4 transition-all duration-150 hover:-translate-y-0.5 hover:border-accent-line hover:bg-surface hover:shadow-md cursor-pointer focus-visible:outline-2 focus-visible:outline-accent">
                <div class="flex items-center justify-between mb-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-surface border border-line text-ink shadow-2xs group-hover:border-accent-line group-hover:text-accent">
                        <x-icon name="scissors" class="h-4 w-4"/>
                    </span>
                    <span class="text-[11px] font-mono font-medium px-2 py-0.5 rounded bg-surface border border-line text-muted">Step 04</span>
                </div>
                <div class="text-xs font-semibold text-muted uppercase tracking-wider">Cutting Section</div>
                <div class="text-xl font-bold text-ink mt-1">{{ $availableRollsCount }} <span class="text-xs font-normal text-muted">rolls ready</span></div>
                <div class="text-xs text-muted mt-1">{{ $cuttingJobsCount }} cut job executions logged</div>
                <div class="mt-4 pt-3 border-t border-line/60 flex items-center justify-between text-xs">
                    <span class="link group-hover:underline">Body / Neck / Rib →</span>
                    <span class="pill">Rolls</span>
                </div>
            </a>

            <!-- Stage 5: Sewing -->
            <a href="{{ route('sewing-jobs.index') }}" class="group relative block rounded-2xl border border-line bg-raised/50 p-4 transition-all duration-150 hover:-translate-y-0.5 hover:border-accent-line hover:bg-surface hover:shadow-md cursor-pointer focus-visible:outline-2 focus-visible:outline-accent">
                <div class="flex items-center justify-between mb-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-surface border border-line text-ink shadow-2xs group-hover:border-accent-line group-hover:text-accent">
                        <x-icon name="factory" class="h-4 w-4"/>
                    </span>
                    <span class="text-[11px] font-mono font-medium px-2 py-0.5 rounded bg-surface border border-line text-muted">Step 05</span>
                </div>
                <div class="text-xs font-semibold text-muted uppercase tracking-wider">Sewing Assembly</div>
                <div class="text-xl font-bold text-ink mt-1">{{ $counts['sewing'] ?? 0 }} <span class="text-xs font-normal text-muted">lots in sewing</span></div>
                <div class="text-xs text-muted mt-1">5-station production flow</div>
                <div class="mt-4 pt-3 border-t border-line/60 flex items-center justify-between text-xs">
                    <span class="link group-hover:underline">Stations & Handoffs →</span>
                    <span class="pill">Assembly</span>
                </div>
            </a>
        </div>
    </section>

    <!-- Key Metrics Grid -->
    <div class="mb-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Metric 1: Total Stock (Hero Lime) -->
        <a href="{{ route('yarns.index') }}" class="metric metric-hero block cursor-pointer transition-all duration-150 hover:brightness-105 hover:-translate-y-1 focus-visible:outline-2 focus-visible:outline-accent">
            <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-[#283000]">
                <span>Yarn Available</span>
                <span class="metric-icon"><x-icon name="spool" class="h-4 w-4"/></span>
            </div>
            <div class="metric-value">{{ \App\Support\Decimal::display($totalKg) }} <span class="text-sm font-normal text-[#404c00]">kg</span></div>
            <div class="mt-3 flex items-center justify-between text-xs text-[#353e00]">
                <span>{{ \App\Support\Decimal::display(\App\Modules\Yarn\UnitConverter::fromKg($totalKg,'lb')) }} lb</span>
                <span class="font-medium underline decoration-current/40 underline-offset-4">{{ $yarns->count() }} items &rarr;</span>
            </div>
        </a>

        <!-- Metric 2: Active Lots (Dark) -->
        <a href="{{ route('knitting-jobs.index') }}" class="metric metric-dark block cursor-pointer transition-all duration-150 hover:brightness-110 hover:-translate-y-1 focus-visible:outline-2 focus-visible:outline-accent">
            <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-neutral-400">
                <span>Active Lots</span>
                <span class="metric-icon"><x-icon name="layers" class="h-4 w-4"/></span>
            </div>
            <div class="metric-value">{{ $totalLotsCount }}</div>
            <div class="mt-3 flex items-center justify-between text-xs text-neutral-400">
                <span>{{ collect(['knitting', 'dyeing', 'cutting', 'sewing'])->map(fn($st) => ($counts[$st] ?? 0) . ' ' . ucfirst($st))->filter(fn($txt) => !str_starts_with($txt, '0 '))->join(' • ') ?: 'No active lots' }}</span>
                <span class="text-[#d5ff27] font-medium underline underline-offset-4">View lots &rarr;</span>
            </div>
        </a>

        <!-- Metric 3: Fabric Rolls -->
        <a href="{{ route('cutting.body.create') }}" class="metric block cursor-pointer transition-all duration-150 hover:border-accent-line hover:-translate-y-1 hover:shadow-lg focus-visible:outline-2 focus-visible:outline-accent">
            <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-muted">
                <span>Rolls In Stock</span>
                <span class="metric-icon"><x-icon name="drop" class="h-4 w-4"/></span>
            </div>
            <div class="metric-value">{{ $availableRollsCount }} <span class="text-xs font-normal text-muted">/ {{ $totalRollsCount }}</span></div>
            <div class="mt-3 flex items-center justify-between text-xs text-muted">
                <span>Available for cutting</span>
                <span class="link font-semibold">Cut now →</span>
            </div>
        </a>

        <!-- Metric 4: Mill Partners -->
        <a href="{{ route('companies.index') }}" class="metric block cursor-pointer transition-all duration-150 hover:border-accent-line hover:-translate-y-1 hover:shadow-lg focus-visible:outline-2 focus-visible:outline-accent">
            <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-muted">
                <span>Active Mills & Houses</span>
                <span class="metric-icon"><x-icon name="building" class="h-4 w-4"/></span>
            </div>
            <div class="metric-value">{{ $activePartnersCount }}</div>
            <div class="mt-3 flex items-center justify-between text-xs text-muted">
                <span>Knitting & Dyeing partners</span>
                <span class="link font-semibold">Directory →</span>
            </div>
        </a>
    </div>

    <!-- Data Tables Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Lots -->
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>Recent Production Lots</h2>
                    <p class="text-xs text-muted mt-0.5">Trace fabric progress from yarn input to rolls</p>
                </div>
                <a href="{{ route('knitting-jobs.index') }}" class="link text-xs">View all lots →</a>
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
                                        'sewing' => 'bg-emerald-100 text-emerald-800',
                                        'cutting' => 'bg-blue-100 text-blue-800',
                                        'dyeing' => 'bg-purple-100 text-purple-800',
                                        default => 'bg-lime-100 text-lime-900',
                                    };
                                    $dotClasses = match($lot->current_stage) {
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
