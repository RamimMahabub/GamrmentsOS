<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2.5">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-ink">Yarn Inventory</h1>
                <p class="text-xs text-muted">Stock reserves, warehouse bag balances, and valuation.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('yarns.create') }}" class="btn btn-primary">
                    <x-icon name="plus" class="h-3.5 w-3.5"/>
                    Add New Yarn
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Compact Telemetry KPI Strip -->
    <section class="mb-3" aria-label="Warehouse Yarn Stock Telemetry">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
            
            <!-- Metric 1: Total Stock Weight -->
            <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-surface border border-line shadow-2xs">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-muted block">Physical Reserves</span>
                    <div class="text-sm font-bold text-ink tabular-nums leading-tight mt-0.5">
                        {{ \App\Support\Decimal::display($metrics['total_kg'] ?? '0') }} <span class="text-xs font-semibold text-accent">kg</span>
                        <span class="text-[11px] font-normal text-muted ml-1">({{ \App\Support\Decimal::display(\App\Modules\Yarn\UnitConverter::fromKg($metrics['total_kg'] ?? '0', 'lb')) }} lb)</span>
                    </div>
                </div>
                <span class="pill font-mono text-[10px]">{{ $metrics['total_varieties'] }} items</span>
            </div>

            <!-- Metric 2: Estimated Stock Valuation -->
            <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-surface border border-line shadow-2xs">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-muted block">Est. Valuation</span>
                    <div class="text-sm font-bold text-ink tabular-nums leading-tight mt-0.5">
                        <span class="text-xs text-muted font-normal mr-0.5">{{ $currency }}</span>{{ \App\Support\Decimal::display($metrics['total_valuation'] ?? '0', 2) }}
                    </div>
                </div>
                <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">ACTIVE</span>
            </div>

            <!-- Metric 3: Total Bags in Warehouse -->
            <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-surface border border-line shadow-2xs">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-muted block">Standard Bags</span>
                    <div class="text-sm font-bold text-ink tabular-nums leading-tight mt-0.5">
                        {{ \App\Support\Decimal::display($metrics['total_bags'] ?? '0', 1, true) }} <span class="text-xs font-normal text-muted">bags</span>
                    </div>
                </div>
                <span class="pill font-mono text-[10px]">FLOOR</span>
            </div>

            <!-- Metric 4: Stock Health & Low-Stock Alerts -->
            <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-surface border border-line shadow-2xs">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-muted block">Stock Health</span>
                    <div class="text-sm font-bold tabular-nums leading-tight mt-0.5 {{ ($metrics['low_stock_count'] ?? 0) > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                        {{ $metrics['low_stock_count'] ?? 0 }} <span class="text-xs font-normal text-muted">low / critical</span>
                    </div>
                </div>
                @if(($metrics['low_stock_count'] ?? 0) > 0)
                    <span class="text-[10px] font-bold text-amber-800 bg-amber-100 px-2 py-0.5 rounded-full">Reorder</span>
                @else
                    <span class="text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full">Optimal</span>
                @endif
            </div>

        </div>
    </section>

    <!-- Main Data Panel with High-End Futuristic Table & Filter Bar -->
    <div class="panel shadow-2xs border border-line bg-surface rounded-xl overflow-hidden">
        <!-- Compact Toolbar -->
        <div class="px-3.5 py-2 border-b border-line/70 bg-gradient-to-r from-surface via-raised/30 to-surface flex flex-wrap items-center justify-between gap-2.5">
            <form method="GET" class="flex flex-wrap items-center gap-2">
                <div class="relative min-w-[220px]">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-muted">
                        <x-icon name="search" class="h-3.5 w-3.5"/>
                    </span>
                    <input id="list-search" 
                           type="text" 
                           name="q" 
                           value="{{ request('q') }}" 
                           placeholder="Search yarn, spec, origin..." 
                           class="w-full pl-8 pr-3 py-1 text-xs bg-surface border border-line rounded-lg focus:border-accent transition">
                </div>
                
                <div class="flex items-center gap-1.5">
                    <select id="list-sort" name="sort" class="text-xs font-medium py-1 px-2 bg-surface border border-line rounded-lg text-ink focus:border-accent cursor-pointer">
                        <option value="name" @selected(request('sort','name')==='name')>Sort: Yarn Name</option>
                        <option value="yarn_type" @selected(request('sort')==='yarn_type')>Sort: Type / Spec</option>
                        <option value="country_of_origin" @selected(request('sort')==='country_of_origin')>Sort: Origin</option>
                        <option value="price_per_kg" @selected(request('sort')==='price_per_kg')>Sort: Price</option>
                    </select>

                    <select name="direction" aria-label="Sort direction" class="text-xs font-medium py-1 px-2 bg-surface border border-line rounded-lg text-ink focus:border-accent cursor-pointer">
                        <option value="asc" @selected(request('direction','asc')==='asc')>Asc ↑</option>
                        <option value="desc" @selected(request('direction')==='desc')>Desc ↓</option>
                    </select>

                    <button type="submit" class="btn btn-secondary btn-xs">
                        <x-icon name="sliders" class="h-3 w-3 text-accent"/>
                        Apply
                    </button>
                    @if(request()->hasAny(['q', 'sort', 'direction']))
                        <a href="{{ route('yarns.index') }}" class="text-xs text-muted hover:text-ink font-medium px-1.5">Reset</a>
                    @endif
                </div>
            </form>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-raised border border-line text-[11px] font-semibold text-ink">
                    <span class="h-1.5 w-1.5 rounded-full bg-accent animate-pulse"></span>
                    {{ $yarns->total() }} {{ Str::plural('Lot', $yarns->total()) }}
                </span>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr class="border-b border-line">
                        <th class="w-10 text-center font-mono">#</th>
                        <th>Yarn & Identification</th>
                        <th>Spec & Density</th>
                        <th>Origin</th>
                        <th class="number">Unit Price</th>
                        <th class="number">Stock Reserve ({{ strtoupper($defaultUnit) }})</th>
                        <th class="number">Standard Bags</th>
                        <th class="number pr-4">Quick Control</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e4e6ec]">
                    @forelse($yarns as $index => $yarn)
                        @php
                            $availableKg = (string)($yarn->available_kg ?? '0');
                            $isDepleted = \App\Support\Decimal::of($availableKg)->isZero() || \App\Support\Decimal::of($availableKg)->isNegative();
                            $isLow = !$isDepleted && \App\Support\Decimal::of($availableKg)->isLessThan('100');
                        @endphp
                        <tr class="group transition-all duration-120 hover:bg-[#edf3dc] even:bg-[#f8f9fc]">
                            <!-- Row Index -->
                            <td class="text-center font-mono text-xs text-muted/80">
                                {{ str_pad((string)($yarns->firstItem() + $index), 2, '0', STR_PAD_LEFT) }}
                            </td>

                            <!-- Yarn Name & Identity -->
                            <td>
                                <div class="flex items-center gap-2 py-0.5">
                                    <a class="font-bold text-ink text-xs hover:text-accent transition inline-flex items-center gap-1" href="{{ route('yarns.show', $yarn) }}">
                                        {{ $yarn->name }}
                                    </a>
                                    @if($isDepleted)
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Depleted</span>
                                    @elseif($isLow)
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Low Stock</span>
                                    @endif
                                    <span class="text-[10px] font-mono text-muted/60">#Y-{{ $yarn->id }}</span>
                                </div>
                            </td>

                            <!-- Spec / Type -->
                            <td>
                                <span class="pill text-[11px] font-medium">
                                    {{ $yarn->yarn_type }}
                                </span>
                            </td>

                            <!-- Origin Country -->
                            <td class="text-xs text-muted font-medium">
                                {{ $yarn->country_of_origin }}
                            </td>

                            <!-- Unit Price -->
                            <td class="number">
                                <span class="font-semibold tabular-nums text-ink text-xs">
                                    <span class="text-[10px] text-muted mr-0.5">{{ $currency }}</span>{{ \App\Support\Decimal::display(\App\Modules\Yarn\UnitConverter::priceFromKg($yarn->price_per_kg, $yarn->price_unit), 2) }}
                                </span>
                                <span class="text-[10px] text-muted">/{{ $yarn->price_unit }}</span>
                            </td>

                            <!-- Stock Reserve -->
                            <td class="number">
                                <span class="font-bold tabular-nums text-ink text-xs">
                                    {{ \App\Support\Decimal::display(\App\Modules\Yarn\UnitConverter::fromKg($availableKg, $defaultUnit)) }}
                                    <span class="text-[10px] font-normal text-muted">{{ $defaultUnit }}</span>
                                </span>
                                <span class="text-[10px] text-muted font-mono ml-1">
                                    ({{ \App\Support\Decimal::display($availableKg) }} kg)
                                </span>
                            </td>

                            <!-- Standard Bags -->
                            <td class="number">
                                <span class="pill font-mono font-bold text-[11px]">
                                    {{ \App\Support\Decimal::display(\App\Modules\Yarn\UnitConverter::bags($availableKg, $yarn->bag_size_kg), 2, true) }}
                                </span>
                            </td>

                            <!-- Action Controls -->
                            <td class="number whitespace-nowrap pr-4">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('yarns.show', $yarn) }}" class="btn btn-secondary btn-xs">
                                        View
                                    </a>
                                    <a href="{{ route('yarns.show', $yarn) }}#add-stock" class="btn btn-primary btn-xs">
                                        + Stock
                                    </a>
                                    <a href="{{ route('yarns.edit', $yarn) }}" title="Edit yarn" class="btn btn-secondary btn-xs !px-1.5 text-muted hover:text-ink">
                                        <x-icon name="pen" class="h-3 w-3"/>
                                    </a>
                                    <x-delete-button :action="route('yarns.destroy', $yarn)" label="Delete yarn" icon="trash" class="btn btn-danger btn-xs !px-1.5 cursor-pointer"/>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty py-12 text-center">
                                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-accent-soft text-accent-ink mb-2">
                                    <x-icon name="spool" class="h-5 w-5"/>
                                </div>
                                <h3 class="text-sm font-bold text-ink">No yarn inventory found</h3>
                                <p class="text-xs text-muted mt-0.5 max-w-sm mx-auto">Begin building your manufacturing floor catalog by recording your first raw material batch.</p>
                                <div class="mt-3">
                                    <a href="{{ route('yarns.create') }}" class="btn btn-primary">
                                        <x-icon name="plus" class="h-3.5 w-3.5"/>
                                        Add First Yarn
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-3.5 py-2 border-t border-line/60 bg-raised/30">
            {{ $yarns->links('components.pagination') }}
        </div>
    </div>
</x-app-layout>
