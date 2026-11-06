<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="eyebrow flex items-center gap-2 mb-1">
                    <span class="h-2 w-2 rounded-full bg-accent animate-pulse"></span>
                    Knitting Mill Programs
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-ink">Knitting Jobs</h1>
                <p class="text-sm text-muted mt-1">Assign yarn shipments, track DIA setups, machine programs, and monitor output loss.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a class="btn btn-primary shadow-sm shadow-accent/25" href="{{ route('knitting-jobs.create') }}">
                    <x-icon name="plus" class="h-4 w-4"/>
                    Assign Knitting Shipment
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Futuristic Data Panel -->
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
                           placeholder="Search lot #, knitter mill..." 
                           class="w-full pl-10 pr-4 py-2 text-sm bg-surface border border-line rounded-xl focus:border-accent focus:ring-2 focus:ring-accent/20 transition shadow-2xs">
                </div>
                
                <div class="flex items-center gap-2">
                    <select id="list-sort" name="sort" class="text-xs font-semibold py-2 px-3 bg-surface border border-line rounded-xl text-ink focus:border-accent cursor-pointer">
                        <option value="id" @selected(request('sort','id')==='id')>Sort: Latest Lot</option>
                        <option value="entry_date" @selected(request('sort')==='entry_date')>Sort: Entry Date</option>
                        <option value="delivery_date" @selected(request('sort')==='delivery_date')>Sort: Delivery Date</option>
                    </select>

                    <select name="direction" aria-label="Sort direction" class="text-xs font-semibold py-2 px-3 bg-surface border border-line rounded-xl text-ink focus:border-accent cursor-pointer">
                        <option value="desc" @selected(request('direction','desc')==='desc')>Descending ↓</option>
                        <option value="asc" @selected(request('direction')==='asc')>Ascending ↑</option>
                    </select>

                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-ink text-surface hover:bg-neutral-800 transition shadow-sm">
                        <x-icon name="sliders" class="h-3.5 w-3.5 text-[#d5ff27]"/>
                        Apply
                    </button>
                    @if(request()->hasAny(['q', 'sort', 'direction']))
                        <a href="{{ route('knitting-jobs.index') }}" class="text-xs text-muted hover:text-ink font-medium px-2 py-1">Reset</a>
                    @endif
                </div>
            </form>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-raised border border-line text-xs font-semibold text-ink shadow-2xs">
                    <span class="h-2 w-2 rounded-full bg-accent animate-pulse"></span>
                    {{ $jobs->total() }} {{ Str::plural('Job', $jobs->total()) }} Logged
                </span>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr class="border-b border-line">
                        <th class="w-12 text-center text-[10px] font-mono tracking-widest text-muted uppercase">#</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Lot Identity</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Entry Date</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Knitting Partner & Yarn</th>
                        <th class="number text-[10px] font-bold tracking-widest text-muted uppercase">Input (KG)</th>
                        <th class="number text-[10px] font-bold tracking-widest text-muted uppercase">Output (KG)</th>
                        <th class="number text-[10px] font-bold tracking-widest text-muted uppercase">Process Waste</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Delivery Schedule</th>
                        <th class="number pr-6 text-[10px] font-bold tracking-widest text-muted uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e2e4ea]">
                    @forelse($jobs as $index => $job)
                        @php 
                            $totals = \App\Support\JobTotals::knitting($job); 
                            $hasDelivered = $job->delivery_date && $job->delivery_date->isPast();
                        @endphp
                        <tr class="group transition-all duration-150 hover:bg-[#edf3dc] even:bg-[#f7f9fc]">
                            <!-- Row Index -->
                            <td class="text-center font-mono text-xs text-muted/80">
                                {{ str_pad((string)($jobs->firstItem() + $index), 2, '0', STR_PAD_LEFT) }}
                            </td>

                            <!-- Lot Identity -->
                            <td>
                                <a class="inline-flex items-center gap-2 font-mono font-bold text-xs px-3 py-1.5 rounded-xl bg-[#d5ff27]/20 border border-[#ccd994] text-[#222800] hover:bg-[#d5ff27]/40 transition shadow-2xs" href="{{ route('knitting-jobs.show', $job) }}">
                                    <x-icon name="layers" class="h-3.5 w-3.5 text-[#526500]"/>
                                    {{ $job->lot->lot_number }}
                                </a>
                            </td>

                            <!-- Entry Date -->
                            <td class="whitespace-nowrap text-xs font-semibold text-ink">
                                {{ $job->entry_date->format('d M Y') }}
                            </td>

                            <!-- Partner Mill & Yarns -->
                            <td>
                                <div class="font-bold text-ink text-sm">{{ $job->company->name }}</div>
                                <div class="text-xs text-muted mt-0.5 flex flex-wrap gap-1">
                                    @foreach($job->yarnLinks as $link)
                                        <span class="inline-flex items-center gap-1 text-[11px] text-muted font-medium">
                                            <span class="h-1.5 w-1.5 rounded-full bg-accent"></span>
                                            {{ $link->yarn->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>

                            <!-- Input Weight -->
                            <td class="number">
                                <div class="font-bold tabular-nums text-ink text-sm">
                                    {{ $totals['input'] }} <span class="text-xs font-normal text-muted">kg</span>
                                </div>
                            </td>

                            <!-- Output Weight -->
                            <td class="number">
                                <div class="font-bold tabular-nums text-ink text-sm">
                                    {{ $totals['output'] }} <span class="text-xs font-normal text-muted">kg</span>
                                </div>
                            </td>

                            <!-- Waste Percentage -->
                            <td class="number">
                                @if($totals['waste'] !== null)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ (float)$totals['waste'] > 3.0 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-lime-50 text-lime-800 border border-lime-200' }}">
                                        {{ $totals['waste'] }}%
                                    </span>
                                @else
                                    <span class="text-muted text-xs">—</span>
                                @endif
                            </td>

                            <!-- Delivery Schedule -->
                            <td class="whitespace-nowrap text-xs">
                                @if($job->delivery_date)
                                    <span class="inline-flex items-center gap-1.5 font-medium {{ $hasDelivered ? 'text-muted' : 'text-accent font-semibold' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $hasDelivered ? 'bg-muted' : 'bg-accent animate-pulse' }}"></span>
                                        {{ $job->delivery_date->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-muted">Unscheduled</span>
                                @endif
                            </td>

                            <!-- Action Controls -->
                            <td class="number whitespace-nowrap pr-6">
                                <div class="inline-flex items-center gap-1.5">
                                    <a class="inline-flex items-center px-3 py-1.5 rounded-xl border border-line bg-surface text-xs font-semibold text-ink hover:border-accent hover:text-accent shadow-2xs transition" href="{{ route('knitting-jobs.show', $job) }}">
                                        View
                                    </a>
                                    <a class="inline-flex items-center justify-center h-8 w-8 rounded-xl border border-line bg-surface text-muted hover:text-ink hover:border-muted transition shadow-2xs" href="{{ route('knitting-jobs.edit', $job) }}" title="Edit job">
                                        <x-icon name="pen" class="h-3.5 w-3.5"/>
                                    </a>
                                    <x-delete-button :action="route('knitting-jobs.destroy', $job)" label="Delete job" icon="trash" class="inline-flex items-center justify-center h-8 w-8 rounded-xl border border-rose-200 bg-rose-50/70 text-rose-600 hover:bg-rose-100 hover:border-rose-300 transition shadow-2xs cursor-pointer"/>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="empty py-20 text-center">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-accent-soft text-accent-ink mb-4 shadow-sm">
                                    <x-icon name="layers" class="h-8 w-8"/>
                                </div>
                                <h3 class="text-lg font-bold text-ink">No knitting shipments logged</h3>
                                <p class="text-xs text-muted mt-1 max-w-sm mx-auto">Create a production lot by assigning raw yarn stock to a circular knitting partner.</p>
                                <div class="mt-5">
                                    <a href="{{ route('knitting-jobs.create') }}" class="btn btn-primary shadow-sm shadow-accent/25">
                                        <x-icon name="plus" class="h-4 w-4"/>
                                        Assign First Shipment
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-line/60 bg-raised/30">
            {{ $jobs->links('components.pagination') }}
        </div>
    </div>
</x-app-layout>
