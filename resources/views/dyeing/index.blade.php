<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2.5">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-ink">Dyeing Jobs</h1>
                <p class="text-xs text-muted">Track fabric allocations, color recipes, batch returns, and itemized rolls.</p>
            </div>
            <div class="flex items-center gap-2">
                <a class="btn btn-primary" href="{{ route('dyeing-jobs.create') }}">
                    <x-icon name="plus" class="h-3.5 w-3.5"/>
                    Send Fabric to Dyeing
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
                           placeholder="Search lot #, dyeing house..." 
                           class="w-full pl-8 pr-3 py-1 text-xs bg-surface border border-line rounded-lg focus:border-accent transition">
                </div>
                
                <div class="flex items-center gap-1.5">
                    <select id="list-sort" name="sort" class="text-xs font-medium py-1 px-2 bg-surface border border-line rounded-lg text-ink focus:border-accent cursor-pointer">
                        <option value="id" @selected(request('sort','id')==='id')>Sort: Latest Job</option>
                        <option value="entry_date" @selected(request('sort')==='entry_date')>Sort: Entry Date</option>
                        <option value="return_date" @selected(request('sort')==='return_date')>Sort: Return Date</option>
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
                        <a href="{{ route('dyeing-jobs.index') }}" class="text-xs text-muted hover:text-ink font-medium px-1.5">Reset</a>
                    @endif
                </div>
            </form>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-raised border border-line text-[11px] font-semibold text-ink">
                    <span class="h-1.5 w-1.5 rounded-full bg-accent animate-pulse"></span>
                    {{ $jobs->total() }} {{ Str::plural('Lot', $jobs->total()) }} In Dyeing
                </span>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr class="border-b border-line">
                        <th class="w-10 text-center text-[10px] font-mono tracking-widest text-muted uppercase">#</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Lot Identity</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Dyeing Partner</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Color Allocations</th>
                        <th class="number text-[10px] font-bold tracking-widest text-muted uppercase">Given (KG)</th>
                        <th class="number text-[10px] font-bold tracking-widest text-muted uppercase">Returned (KG)</th>
                        <th class="number text-[10px] font-bold tracking-widest text-muted uppercase">Process Waste</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Schedule Timeline</th>
                        <th class="number pr-4 text-[10px] font-bold tracking-widest text-muted uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e2e4ea]">
                    @forelse($jobs as $index => $job)
                        @php 
                            $totals = \App\Support\JobTotals::dyeing($job->colorBlocks->flatMap->rows); 
                            $isComplete = $job->return_date !== null;
                        @endphp
                        <tr class="group transition-all duration-150 hover:bg-[#edf3dc] even:bg-[#f7f9fc]">
                            <!-- Row Index -->
                            <td class="text-center font-mono text-[11px] text-muted">
                                {{ str_pad((string)($jobs->firstItem() + $index), 2, '0', STR_PAD_LEFT) }}
                            </td>

                            <!-- Lot Identity -->
                            <td>
                                <a class="inline-flex items-center gap-1.5 font-mono font-bold text-xs px-2 py-0.5 rounded bg-purple-50 border border-purple-200 text-purple-900 hover:bg-purple-100 transition" href="{{ route('dyeing-jobs.show', $job) }}">
                                    <x-icon name="drop" class="h-3 w-3 text-purple-600"/>
                                    {{ $job->lot->lot_number }}
                                </a>
                            </td>

                            <!-- Partner Company -->
                            <td class="font-semibold text-ink text-xs">
                                {{ $job->company->name }}
                            </td>

                            <!-- Color Allocations -->
                            <td>
                                <div class="flex flex-wrap gap-1 max-w-sm">
                                    @foreach($job->colorBlocks as $block)
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded bg-surface border border-line text-[11px] font-medium text-ink">
                                            {{ $block->color->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>

                            <!-- Given Weight -->
                            <td class="number">
                                <span class="font-bold tabular-nums text-ink text-xs">{{ $totals['given'] }}</span>
                                <span class="text-[10px] text-muted">kg</span>
                            </td>

                            <!-- Returned Weight -->
                            <td class="number">
                                @if($totals['returned'] !== null)
                                    <span class="font-bold tabular-nums text-ink text-xs">{{ $totals['returned'] }}</span>
                                    <span class="text-[10px] text-muted">kg</span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium text-amber-700 bg-amber-50 px-1.5 py-0.2 rounded border border-amber-200">
                                        <span class="h-1 w-1 rounded-full bg-amber-500 animate-pulse"></span> At Mill
                                    </span>
                                @endif
                            </td>

                            <!-- Process Waste -->
                            <td class="number">
                                @if($totals['waste'] !== null)
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[11px] font-semibold {{ (float)$totals['waste'] > 4.5 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-lime-50 text-lime-800 border border-lime-200' }}">
                                        {{ $totals['waste'] }}%
                                    </span>
                                @else
                                    <span class="text-muted text-xs">—</span>
                                @endif
                            </td>

                            <!-- Timeline -->
                            <td class="whitespace-nowrap text-xs">
                                <span class="font-medium text-ink">{{ $job->entry_date->format('d M') }}</span>
                                @if($job->return_date)
                                    <span class="text-emerald-700 font-semibold text-[11px]">&rarr; {{ $job->return_date->format('d M') }}</span>
                                @else
                                    <span class="text-amber-600 text-[10px]">(Pending)</span>
                                @endif
                            </td>

                            <!-- Action Controls -->
                            <td class="number whitespace-nowrap pr-4">
                                <div class="inline-flex items-center gap-1">
                                    <a class="btn btn-secondary btn-xs" href="{{ route('dyeing-jobs.show', $job) }}">
                                        View
                                    </a>
                                    <a class="btn btn-secondary btn-xs" href="{{ route('dyeing-jobs.edit', $job) }}">
                                        Edit
                                    </a>
                                    <x-delete-button :action="route('dyeing-jobs.destroy', $job)" label="Delete job" icon="trash" class="btn btn-danger btn-xs cursor-pointer"/>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="empty py-10 text-center">
                                <h3 class="text-sm font-bold text-ink">No dyeing jobs found</h3>
                                <p class="text-xs text-muted mt-1">Send knitted fabric rolls to your dyeing partner to begin the color finishing stage.</p>
                                <div class="mt-3">
                                    <a href="{{ route('dyeing-jobs.create') }}" class="btn btn-primary btn-xs">
                                        <x-icon name="plus" class="h-3 w-3"/>
                                        Send Fabric to Dyeing
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 border-t border-line/60 bg-raised/30">
            {{ $jobs->links('components.pagination') }}
        </div>
    </div>
</x-app-layout>
