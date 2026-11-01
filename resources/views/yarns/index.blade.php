<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="eyebrow flex items-center gap-2 mb-1">
                    <span class="h-2 w-2 rounded-full bg-accent animate-pulse"></span>
                    Raw Materials & Inventory Matrix
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-ink">Yarn Inventory</h1>
                <p class="text-sm text-muted mt-1">Real-time telemetry, physical bag balances, warehouse stock reserves, and lot issuance status.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('yarns.create') }}" class="btn btn-primary shadow-sm shadow-accent/25">
                    <x-icon name="plus" class="h-4 w-4"/>
                    Add New Yarn
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Futuristic Summary KPI Stat Cards Grid -->
    <section class="mb-8" aria-label="Warehouse Yarn Stock Telemetry">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            
            <!-- Metric 1: Total Stock Weight (Futuristic Hero Neon) -->
            <div class="relative overflow-hidden rounded-3xl p-6 bg-gradient-to-br from-[#d5ff27] via-[#c6f01c] to-[#a8da08] border border-[#d5ff27]/80 shadow-[0_12px_36px_rgba(213,255,39,0.28)] transition-all duration-200 hover:-translate-y-1 hover:shadow-[0_18px_44px_rgba(213,255,39,0.38)]">
                <!-- Decorative futuristic cyber grid overlay -->
                <div class="pointer-events-none absolute -right-6 -bottom-6 h-36 w-36 rounded-full bg-white/20 blur-xl"></div>
                <div class="pointer-events-none absolute top-0 right-0 p-4 opacity-15">
                    <svg class="h-20 w-20 text-[#1a2100]" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="50" cy="50" r="45" stroke-dasharray="6 6"/>
                        <circle cx="50" cy="50" r="28"/>
                        <path d="M50 5 L50 95 M5 50 L95 50"/>
                    </svg>
                </div>

                <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-[#242b00]">
                    <span class="flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-[#1c2200]"></span>
                        Physical Reserves
                    </span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-2xl bg-[#1a2100] text-[#d5ff27] shadow-sm">
                        <x-icon name="spool" class="h-4.5 w-4.5"/>
                    </span>
                </div>

                <div class="mt-4 font-bold text-[#141a00] tabular-nums tracking-tight" style="font-size: clamp(28px, 3vw, 38px); line-height: 1.1;">
                    {{ \App\Support\Decimal::display($metrics['total_kg'] ?? '0') }}
                    <span class="text-sm font-semibold text-[#323d00]">kg</span>
                </div>

                <div class="mt-4 pt-3 border-t border-[#1a2100]/15 flex items-center justify-between text-xs text-[#2c3500] font-medium">
                    <span>{{ \App\Support\Decimal::display(\App\Modules\Yarn\UnitConverter::fromKg($metrics['total_kg'] ?? '0', 'lb')) }} lb</span>
                    <span class="px-2 py-0.5 rounded-full bg-[#1a2100]/10 text-[#1a2100] font-semibold font-mono text-[11px]">
                        {{ $metrics['total_varieties'] }} items
                    </span>
                </div>
            </div>

            <!-- Metric 2: Estimated Stock Valuation (Futuristic Cyber Dark Matrix) -->
            <div class="relative overflow-hidden rounded-3xl p-6 bg-gradient-to-br from-[#1b1c22] via-[#22242c] to-[#17181d] border border-white/10 shadow-[0_12px_36px_rgba(0,0,0,0.35)] transition-all duration-200 hover:-translate-y-1 hover:border-[#d5ff27]/40 hover:shadow-[0_18px_44px_rgba(0,0,0,0.45)]">
                <div class="pointer-events-none absolute -right-6 -bottom-6 h-36 w-36 rounded-full bg-[#d5ff27]/10 blur-2xl"></div>
                <div class="pointer-events-none absolute top-3 right-3 flex gap-1">
                    <span class="h-1 w-1 rounded-full bg-[#d5ff27]"></span>
                    <span class="h-1 w-1 rounded-full bg-[#d5ff27]/40"></span>
                    <span class="h-1 w-1 rounded-full bg-[#d5ff27]/20"></span>
                </div>

                <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-neutral-400">
                    <span class="flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-[#d5ff27] animate-pulse"></span>
                        Est. Valuation
                    </span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-2xl bg-white/10 text-[#d5ff27] border border-white/10 shadow-inner">
                        <span class="font-mono text-xs font-bold">{{ $currency }}</span>
                    </span>
                </div>

                <div class="mt-4 font-bold text-white tabular-nums tracking-tight" style="font-size: clamp(26px, 2.7vw, 36px); line-height: 1.1;">
                    <span class="text-lg font-normal text-neutral-400 mr-0.5">{{ $currency }}</span>{{ \App\Support\Decimal::display($metrics['total_valuation'] ?? '0', 2) }}
                </div>

                <div class="mt-4 pt-3 border-t border-white/10 flex items-center justify-between text-xs text-neutral-400">
                    <span>Cost-based capital</span>
                    <span class="text-[#d5ff27] font-medium font-mono text-[11px]">ACTIVE ASSET</span>
                </div>
            </div>

            <!-- Metric 3: Total Bags in Warehouse (Frosted Glass Clean) -->
            <div class="relative overflow-hidden rounded-3xl p-6 bg-white border border-[#e4e4eb] shadow-sm transition-all duration-200 hover:-translate-y-1 hover:border-[#ccd994] hover:shadow-md">
                <div class="pointer-events-none absolute -top-8 -right-8 h-28 w-28 rounded-full bg-[#eff6cc] blur-xl opacity-70"></div>
                
                <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-muted">
                    <span class="flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-accent"></span>
                        Standard Bags
                    </span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-2xl bg-raised border border-line text-ink">
                        <x-icon name="layers" class="h-4.5 w-4.5"/>
                    </span>
                </div>

                <div class="mt-4 font-bold text-ink tabular-nums tracking-tight" style="font-size: clamp(28px, 3vw, 38px); line-height: 1.1;">
                    {{ \App\Support\Decimal::display($metrics['total_bags'] ?? '0', 1, true) }}
                    <span class="text-sm font-normal text-muted">bags</span>
                </div>

                <div class="mt-4 pt-3 border-t border-line/70 flex items-center justify-between text-xs text-muted">
                    <span>Floor packaging count</span>
                    <span class="pill font-mono text-[11px] font-semibold">BAG UNIT</span>
                </div>
            </div>

            <!-- Metric 4: Stock Health & Low-Stock Alerts -->
            <div class="relative overflow-hidden rounded-3xl p-6 bg-white border border-[#e4e4eb] shadow-sm transition-all duration-200 hover:-translate-y-1 hover:border-amber-300 hover:shadow-md">
                <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-muted">
                    <span class="flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full {{ ($metrics['low_stock_count'] ?? 0) > 0 ? 'bg-amber-500 animate-ping' : 'bg-emerald-500' }}"></span>
                        Inventory Health
                    </span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-2xl {{ ($metrics['low_stock_count'] ?? 0) > 0 ? 'bg-amber-50 text-amber-600 border border-amber-200' : 'bg-emerald-50 text-emerald-600 border border-emerald-200' }}">
                        @if(($metrics['low_stock_count'] ?? 0) > 0)
                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        @else
                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        @endif
                    </span>
                </div>

                <div class="mt-4 font-bold tabular-nums tracking-tight {{ ($metrics['low_stock_count'] ?? 0) > 0 ? 'text-amber-700' : 'text-emerald-700' }}" style="font-size: clamp(28px, 3vw, 38px); line-height: 1.1;">
                    {{ $metrics['low_stock_count'] ?? 0 }}
                    <span class="text-sm font-normal text-muted">low / critical</span>
                </div>

                <div class="mt-4 pt-3 border-t border-line/70 flex items-center justify-between text-xs">
                    <span class="text-muted">{{ $metrics['in_stock_count'] ?? 0 }} normal items</span>
                    @if(($metrics['low_stock_count'] ?? 0) > 0)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800">
                            Reorder needed
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                            Optimal Levels
                        </span>
                    @endif
                </div>
            </div>

        </div>
    </section>

    <!-- Main Data Panel with High-End Futuristic Table & Filter Bar -->
    <div class="panel shadow-sm border border-line bg-surface rounded-3xl overflow-hidden">
        <!-- Futuristic Toolbar -->
        <div class="px-6 py-5 border-b border-line/70 bg-gradient-to-r from-surface via-raised/40 to-surface flex flex-wrap items-center justify-between gap-4">
            <form method="GET" class="flex flex-wrap items-center gap-3">
                <div class="relative min-w-[260px]">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-muted">
                        <x-icon name="search" class="h-4 w-4"/>
                    </span>
                    <input id="list-search" 
                           type="text" 
                           name="q" 
                           value="{{ request('q') }}" 
                           placeholder="Search yarn, spec, country..." 
                           class="w-full pl-10 pr-4 py-2 text-sm bg-surface border border-line rounded-xl focus:border-accent focus:ring-2 focus:ring-accent/20 transition shadow-2xs">
                </div>
                
                <div class="flex items-center gap-2">
                    <select id="list-sort" name="sort" class="text-xs font-semibold py-2 px-3 bg-surface border border-line rounded-xl text-ink focus:border-accent cursor-pointer">
                        <option value="name" @selected(request('sort','name')==='name')>Sort: Yarn Name</option>
                        <option value="yarn_type" @selected(request('sort')==='yarn_type')>Sort: Type / Spec</option>
                        <option value="country_of_origin" @selected(request('sort')==='country_of_origin')>Sort: Origin</option>
                        <option value="price_per_kg" @selected(request('sort')==='price_per_kg')>Sort: Price</option>
                    </select>

                    <select name="direction" aria-label="Sort direction" class="text-xs font-semibold py-2 px-3 bg-surface border border-line rounded-xl text-ink focus:border-accent cursor-pointer">
                        <option value="asc" @selected(request('direction','asc')==='asc')>Ascending ↑</option>
                        <option value="desc" @selected(request('direction')==='desc')>Descending ↓</option>
                    </select>

                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-ink text-surface hover:bg-neutral-800 transition shadow-sm">
                        <x-icon name="sliders" class="h-3.5 w-3.5 text-[#d5ff27]"/>
                        Apply
                    </button>
                    @if(request()->hasAny(['q', 'sort', 'direction']))
                        <a href="{{ route('yarns.index') }}" class="text-xs text-muted hover:text-ink font-medium px-2 py-1">Reset</a>
                    @endif
                </div>
            </form>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-raised border border-line text-xs font-semibold text-ink shadow-2xs">
                    <span class="h-2 w-2 rounded-full bg-accent animate-pulse"></span>
                    {{ $yarns->total() }} {{ Str::plural('Lot', $yarns->total()) }} Registered
                </span>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr class="border-b border-line">
                        <th class="w-12 text-center text-[10px] font-mono tracking-widest text-muted uppercase">#</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Yarn & Identification</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Spec & Density</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Origin</th>
                        <th class="number text-[10px] font-bold tracking-widest text-muted uppercase">Unit Price</th>
                        <th class="number text-[10px] font-bold tracking-widest text-muted uppercase">Stock Reserve ({{ strtoupper($defaultUnit) }})</th>
                        <th class="number text-[10px] font-bold tracking-widest text-muted uppercase">Standard Bags</th>
                        <th class="number pr-6 text-[10px] font-bold tracking-widest text-muted uppercase">Quick Control</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#efeff3]">
                    @forelse($yarns as $index => $yarn)
                        @php
                            $availableKg = (string)($yarn->available_kg ?? '0');
                            $isDepleted = \App\Support\Decimal::of($availableKg)->isZero() || \App\Support\Decimal::of($availableKg)->isNegative();
                            $isLow = !$isDepleted && \App\Support\Decimal::of($availableKg)->isLessThan('100');
                        @endphp
                        <tr class="group transition-all duration-150 hover:bg-[#fafaf3]">
                            <!-- Row Index -->
                            <td class="text-center font-mono text-xs text-muted/80">
                                {{ str_pad((string)($yarns->firstItem() + $index), 2, '0', STR_PAD_LEFT) }}
                            </td>

                            <!-- Yarn Name & Identity -->
                            <td>
                                <div class="flex items-center gap-3.5 py-1">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-line bg-surface text-ink shadow-2xs group-hover:border-accent-line group-hover:shadow-[0_4px_12px_rgba(213,255,39,0.2)] group-hover:text-accent transition duration-150">
                                        <x-icon name="spool" class="h-5 w-5"/>
                                    </div>
                                    <div>
                                        <a class="font-bold text-ink text-sm tracking-tight hover:text-accent transition inline-flex items-center gap-1.5" href="{{ route('yarns.show', $yarn) }}">
                                            {{ $yarn->name }}
                                        </a>
                                        <div class="flex items-center gap-2 mt-1">
                                            @if($isDepleted)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500 animate-pulse"></span> Depleted
                                                </span>
                                            @elseif($isLow)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span> Low Stock
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-lime-50 text-lime-900 border border-lime-200">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-lime-600"></span> Available
                                                </span>
                                            @endif
                                            <span class="text-[11px] font-mono text-muted/70 tracking-wide">#Y-{{ $yarn->id }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Spec / Type -->
                            <td>
                                <span class="inline-flex items-center px-3 py-1 rounded-xl bg-surface border border-line font-medium text-xs text-ink shadow-2xs">
                                    {{ $yarn->yarn_type }}
                                </span>
                            </td>

                            <!-- Origin Country -->
                            <td>
                                <span class="inline-flex items-center gap-2 text-xs font-semibold text-muted">
                                    <span class="h-2 w-2 rounded-full bg-neutral-300 group-hover:bg-accent transition"></span>
                                    {{ $yarn->country_of_origin }}
                                </span>
                            </td>

                            <!-- Unit Price -->
                            <td class="number">
                                <div class="font-bold tabular-nums text-ink text-sm">
                                    <span class="text-xs font-normal text-muted mr-0.5">{{ $currency }}</span>{{ \App\Support\Decimal::display(\App\Modules\Yarn\UnitConverter::priceFromKg($yarn->price_per_kg, $yarn->price_unit), 2) }}
                                </div>
                                <div class="text-[11px] text-muted font-medium mt-0.5">per {{ $yarn->price_unit }}</div>
                            </td>

                            <!-- Stock Reserve -->
                            <td class="number">
                                <div class="font-bold tabular-nums text-ink text-base tracking-tight">
                                    {{ \App\Support\Decimal::display(\App\Modules\Yarn\UnitConverter::fromKg($availableKg, $defaultUnit)) }}
                                    <span class="text-xs font-normal text-muted">{{ $defaultUnit }}</span>
                                </div>
                                <div class="text-[11px] text-muted font-mono mt-0.5">
                                    {{ \App\Support\Decimal::display($availableKg) }} kg
                                </div>
                            </td>

                            <!-- Standard Bags -->
                            <td class="number">
                                <span class="inline-flex items-center px-3 py-1 rounded-xl bg-accent-soft border border-accent-line/60 text-accent-ink font-mono font-bold text-xs shadow-2xs">
                                    {{ \App\Support\Decimal::display(\App\Modules\Yarn\UnitConverter::bags($availableKg, $yarn->bag_size_kg), 2, true) }}
                                </span>
                            </td>

                            <!-- Action Controls -->
                            <td class="number whitespace-nowrap pr-6">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('yarns.show', $yarn) }}" class="inline-flex items-center px-3 py-1.5 rounded-xl border border-line bg-surface text-xs font-semibold text-ink hover:border-accent hover:text-accent hover:bg-surface shadow-2xs transition">
                                        View
                                    </a>
                                    <a href="{{ route('yarns.show', $yarn) }}#add-stock" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-[#d5ff27] text-[#222800] border border-[#c5ed17] text-xs font-bold hover:bg-[#e0ff64] shadow-2xs hover:-translate-y-0.5 transition">
                                        <x-icon name="plus" class="h-3 w-3"/>
                                        Stock
                                    </a>
                                    <a href="{{ route('yarns.edit', $yarn) }}" title="Edit yarn" class="inline-flex items-center justify-center h-8 w-8 rounded-xl border border-line bg-surface text-muted hover:text-ink hover:border-muted transition shadow-2xs">
                                        <x-icon name="pen" class="h-3.5 w-3.5"/>
                                    </a>
                                    <x-delete-button :action="route('yarns.destroy', $yarn)" label="Delete yarn" icon="trash" class="inline-flex items-center justify-center h-8 w-8 rounded-xl border border-rose-200 bg-rose-50/70 text-rose-600 hover:bg-rose-100 hover:border-rose-300 transition shadow-2xs cursor-pointer"/>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty py-20 text-center">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-accent-soft text-accent-ink mb-4 shadow-sm">
                                    <x-icon name="spool" class="h-8 w-8"/>
                                </div>
                                <h3 class="text-lg font-bold text-ink">No yarn inventory found</h3>
                                <p class="text-xs text-muted mt-1 max-w-sm mx-auto">Begin building your manufacturing floor catalog by recording your first raw material batch.</p>
                                <div class="mt-5">
                                    <a href="{{ route('yarns.create') }}" class="btn btn-primary shadow-sm shadow-accent/25">
                                        <x-icon name="plus" class="h-4 w-4"/>
                                        Add First Yarn
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-line/60 bg-raised/30">
            {{ $yarns->links('components.pagination') }}
        </div>
    </div>
</x-app-layout>
