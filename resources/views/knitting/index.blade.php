<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2.5">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-ink">Knitting Jobs</h1>
                <p class="text-xs text-muted">Assign yarn shipments, track DIA setups, and monitor output loss.</p>
            </div>
            <div class="flex items-center gap-2">
                <a class="btn btn-primary" href="{{ route('knitting-jobs.create') }}">
                    <x-icon name="plus" class="h-3.5 w-3.5"/>
                    Assign Knitting Shipment
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Compact Data Panel -->
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
                           placeholder="Search lot #, knitter mill..." 
                           class="w-full pl-8 pr-3 py-1 text-xs bg-surface border border-line rounded-lg focus:border-accent transition">
                </div>
                
                <div class="flex items-center gap-1.5">
                    <select id="list-sort" name="sort" class="text-xs font-medium py-1 px-2 bg-surface border border-line rounded-lg text-ink focus:border-accent cursor-pointer">
                        <option value="id" @selected(request('sort','id')==='id')>Sort: Latest Lot</option>
                        <option value="entry_date" @selected(request('sort')==='entry_date')>Sort: Entry Date</option>
                        <option value="delivery_date" @selected(request('sort')==='delivery_date')>Sort: Delivery Date</option>
                    </select>

                    <select name="direction" aria-label="Sort direction" class="text-xs font-medium py-1 px-2 bg-surface border border-line rounded-lg text-ink focus:border-accent cursor-pointer">
                        <option value="desc" @selected(request('direction','desc')==='desc')>Desc ↓</option>
                        <option value="asc" @selected(request('direction')==='asc')>Asc ↑</option>
                    </select>

                    <button type="submit" class="btn btn-secondary btn-xs">
                        <x-icon name="sliders" class="h-3 w-3 text-accent"/>
                        Apply
                    </button>
                    @if(request()->hasAny(['q', 'sort', 'direction']))
                        <a href="{{ route('knitting-jobs.index') }}" class="text-xs text-muted hover:text-ink font-medium px-1.5">Reset</a>
                    @endif
                </div>
            </form>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-raised border border-line text-[11px] font-semibold text-ink">
                    <span class="h-1.5 w-1.5 rounded-full bg-accent animate-pulse"></span>
                    {{ $jobs->total() }} {{ Str::plural('Job', $jobs->total()) }}
                </span>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr class="border-b border-line">
                        <th class="w-10 text-center font-mono">#</th>
                        <th>Lot Identity</th>
                        <th>Entry Date</th>
                        <th>Knitting Partner & Yarn</th>
                        <th class="number">Input (KG / LB)</th>
                        <th class="number">Output (KG / LB)</th>
                        <th class="number">Process Waste</th>
                        <th>Delivery Schedule</th>
                        <th class="number pr-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e4e6ec]">
                    @forelse($jobs as $index => $job)
                        @php 
                            $totals = \App\Support\JobTotals::knitting($job); 
                            $hasDelivered = $job->delivery_date && $job->delivery_date->isPast();
                        @endphp
                        <tr class="group transition-all duration-120 hover:bg-[#edf3dc] even:bg-[#f8f9fc]">
                            <!-- Row Index -->
                            <td class="text-center font-mono text-xs text-muted/80">
                                {{ str_pad((string)($jobs->firstItem() + $index), 2, '0', STR_PAD_LEFT) }}
                            </td>

                            <!-- Lot Identity -->
                            <td>
                                <a class="inline-flex items-center gap-1.5 font-mono font-bold text-xs px-2 py-0.5 rounded-lg bg-[#d5ff27]/20 border border-[#ccd994] text-[#222800] hover:bg-[#d5ff27]/40 transition" href="{{ route('knitting-jobs.show', $job) }}">
                                    <x-icon name="layers" class="h-3 w-3 text-[#526500]"/>
                                    {{ $job->lot->lot_number }}
                                </a>
                            </td>

                            <!-- Entry Date -->
                            <td class="whitespace-nowrap text-xs font-medium text-ink">
                                {{ $job->entry_date->format('d M Y') }}
                            </td>

                            <!-- Partner Mill & Yarns -->
                            <td>
                                <span class="font-semibold text-ink text-xs">{{ $job->company->name }}</span>
                                <span class="text-xs text-muted ml-1.5">
                                    @foreach($job->yarnLinks as $link)
                                        <span class="inline-flex items-center gap-1 text-[11px] text-muted">
                                            &bull; {{ $link->yarn->name }}
                                        </span>
                                    @endforeach
                                </span>
                            </td>

                            <!-- Input Weight -->
                            <td class="number">
                                <x-weight-display :kg="$totals['input']"/>
                            </td>

                            <!-- Output Weight -->
                            <td class="number">
                                <x-weight-display :kg="$totals['output']"/>
                            </td>

                            <!-- Waste Percentage -->
                            <td class="number">
                                @if($totals['waste'] !== null)
                                    <span class="inline-flex items-center px-2 py-0.2 rounded text-[11px] font-semibold {{ (float)$totals['waste'] > 3.0 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-lime-50 text-lime-800 border border-lime-200' }}">
                                        {{ $totals['waste'] }}%
                                    </span>
                                @else
                                    <span class="text-muted text-xs">—</span>
                                @endif
                            </td>

                            <!-- Delivery Schedule -->
                            <td class="whitespace-nowrap text-xs">
                                @if($job->delivery_date)
                                    <span class="inline-flex items-center gap-1 font-medium {{ $hasDelivered ? 'text-muted' : 'text-accent font-semibold' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $hasDelivered ? 'bg-muted' : 'bg-accent animate-pulse' }}"></span>
                                        {{ $job->delivery_date->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-muted text-[11px]">Unscheduled</span>
                                @endif
                            </td>

                            <!-- Action Controls -->
                            <td class="number whitespace-nowrap pr-4">
                                <div class="inline-flex items-center gap-1">
                                    <a class="btn btn-secondary btn-xs" href="{{ route('knitting-jobs.show', $job) }}">
                                        View
                                    </a>
                                    <a class="btn btn-secondary btn-xs !px-1.5 text-muted hover:text-ink" href="{{ route('knitting-jobs.edit', $job) }}" title="Edit job">
                                        <x-icon name="pen" class="h-3 w-3"/>
                                    </a>
                                    <x-delete-button :action="route('knitting-jobs.destroy', $job)" label="Delete job" icon="trash" class="btn btn-danger btn-xs !px-1.5 cursor-pointer"/>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="empty py-12 text-center">
                                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-accent-soft text-accent-ink mb-2">
                                    <x-icon name="layers" class="h-5 w-5"/>
                                </div>
                                <h3 class="text-sm font-bold text-ink">No knitting shipments logged</h3>
                                <p class="text-xs text-muted mt-0.5 max-w-sm mx-auto">Create a production lot by assigning raw yarn stock to a circular knitting partner.</p>
                                <div class="mt-3">
                                    <a href="{{ route('knitting-jobs.create') }}" class="btn btn-primary">
                                        <x-icon name="plus" class="h-3.5 w-3.5"/>
                                        Assign First Shipment
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-3.5 py-2 border-t border-line/60 bg-raised/30">
            {{ $jobs->links('components.pagination') }}
        </div>
    </div>
</x-app-layout>
