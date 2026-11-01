<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="eyebrow flex items-center gap-2 mb-1">
                    <span class="h-2 w-2 rounded-full bg-accent animate-pulse"></span>
                    Precision Garment Cutting Execution
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-ink">Cutting ({{ ucfirst($type) }})</h1>
                <p class="text-sm text-muted mt-1">Transform returned fabric rolls into precision body panels, necks, and rib batches.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a class="btn btn-primary shadow-sm shadow-accent/25" href="{{ route('cutting.'.$type.'.create') }}">
                    <x-icon name="plus" class="h-4 w-4"/>
                    Add Cutting Job
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Cutting Workflow Interactive Step Tabs -->
    <x-cutting-flow :type="$type"/>

    <!-- Futuristic Data Panel -->
    <div class="panel shadow-sm border border-line bg-surface rounded-3xl overflow-hidden">
        <!-- Advanced Filter Toolbar -->
        <div class="px-6 py-5 border-b border-line/70 bg-gradient-to-r from-surface via-raised/40 to-surface">
            <form class="flex flex-wrap items-center gap-3" method="GET">
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <label class="sr-only" for="from-date">From Date</label>
                        <input id="from-date" type="date" name="from" value="{{ request('from') }}" class="text-xs font-semibold py-2 px-3 bg-surface border border-line rounded-xl text-ink focus:border-accent">
                    </div>
                    <span class="text-xs text-muted">to</span>
                    <div class="relative">
                        <label class="sr-only" for="to-date">To Date</label>
                        <input id="to-date" type="date" name="to" value="{{ request('to') }}" class="text-xs font-semibold py-2 px-3 bg-surface border border-line rounded-xl text-ink focus:border-accent">
                    </div>
                </div>

                <select name="staff_id" class="text-xs font-semibold py-2 px-3 bg-surface border border-line rounded-xl text-ink focus:border-accent cursor-pointer">
                    <option value="">All Cutting Masters</option>
                    @foreach($staffs as $staff)
                        <option value="{{ $staff->id }}" @selected(request('staff_id')==$staff->id)>{{ $staff->name }}</option>
                    @endforeach
                </select>

                <select name="lot_id" class="text-xs font-semibold py-2 px-3 bg-surface border border-line rounded-xl text-ink focus:border-accent cursor-pointer">
                    <option value="">All Lots</option>
                    @foreach($lots as $lot)
                        <option value="{{ $lot->id }}" @selected(request('lot_id')==$lot->id)>{{ $lot->lot_number }}</option>
                    @endforeach
                </select>

                <select name="color" class="text-xs font-semibold py-2 px-3 bg-surface border border-line rounded-xl text-ink focus:border-accent cursor-pointer">
                    <option value="">All Colors</option>
                    @foreach($colors as $color)
                        <option value="{{ $color }}" @selected(request('color')===$color)>{{ $color }}</option>
                    @endforeach
                </select>

                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-ink text-surface hover:bg-neutral-800 transition shadow-sm">
                    <x-icon name="sliders" class="h-3.5 w-3.5 text-[#d5ff27]"/>
                    Filter
                </button>
                @if(request()->hasAny(['from', 'to', 'staff_id', 'lot_id', 'color']))
                    <a href="{{ route('cutting.'.$type.'.index') }}" class="text-xs text-muted hover:text-ink font-medium px-2 py-1">Reset</a>
                @endif
            </form>
            <p class="text-[11px] text-muted mt-3 flex items-center gap-1.5">
                <span class="h-1.5 w-1.5 rounded-full bg-accent"></span>
                Totals aggregate all rolls in selected cut executions. Remaining fabric is counted towards total process scrap.
            </p>
        </div>

        <!-- Table Container -->
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr class="border-b border-line">
                        <th class="w-12 text-center text-[10px] font-mono tracking-widest text-muted uppercase">#</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Execution Date</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Cutting Master</th>
                        <th class="text-[10px] font-bold tracking-widest text-muted uppercase">Lots & Colors</th>
                        <th class="number text-[10px] font-bold tracking-widest text-muted uppercase">Rolls</th>
                        <th class="number text-[10px] font-bold tracking-widest text-muted uppercase">Dozens Cut</th>
                        <th class="number text-[10px] font-bold tracking-widest text-muted uppercase">Cut Weight (KG)</th>
                        <th class="number text-[10px] font-bold tracking-widest text-muted uppercase">Scrap (KG)</th>
                        <th class="number text-[10px] font-bold tracking-widest text-muted uppercase">Waste (KG)</th>
                        <th class="number pr-6 text-[10px] font-bold tracking-widest text-muted uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#efeff3]">
                    @forelse($jobs as $index => $job)
                        @php $totals = \App\Support\CuttingTotals::sum($job->rows); @endphp
                        <tr class="group transition-all duration-150 hover:bg-[#fafaf3]">
                            <!-- Index -->
                            <td class="text-center font-mono text-xs text-muted/80">
                                {{ str_pad((string)($jobs->firstItem() + $index), 2, '0', STR_PAD_LEFT) }}
                            </td>

                            <!-- Date -->
                            <td class="whitespace-nowrap">
                                <a class="font-bold text-ink text-xs hover:text-accent transition inline-flex items-center gap-1.5" href="{{ route('cutting.'.$type.'.show', $job) }}">
                                    {{ $job->cutting_date->format('d M Y') }}
                                </a>
                            </td>

                            <!-- Staff -->
                            <td>
                                <a class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink hover:text-accent transition" href="{{ route('staffs.show', $job->staff) }}">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-raised text-muted font-bold text-[10px]">
                                        {{ strtoupper(substr($job->staff->name, 0, 1)) }}
                                    </span>
                                    {{ $job->staff->name }}
                                </a>
                            </td>

                            <!-- Batches / Lots -->
                            <td>
                                <x-cutting-batches :rows="$job->rows" compact/>
                            </td>

                            <!-- Rolls -->
                            <td class="number">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-surface border border-line font-mono text-xs font-bold text-ink shadow-2xs">
                                    {{ $job->rows->count() }}
                                </span>
                                @if($totals['pending_count'])
                                    <div class="text-[10px] text-amber-600 font-medium mt-0.5">{{ $totals['pending_count'] }} pending</div>
                                @endif
                            </td>

                            <!-- Dozens -->
                            <td class="number">
                                <div class="font-bold tabular-nums text-ink text-sm">
                                    {{ $totals['completed_count'] ? $totals['dozens'] : '—' }}
                                </div>
                            </td>

                            <!-- Cut Weight -->
                            <td class="number">
                                <div class="font-bold tabular-nums text-ink text-sm">
                                    {{ $totals['completed_count'] ? $totals['cut_weight_kg'] : '—' }}
                                    @if($totals['completed_count'])<span class="text-[10px] font-normal text-muted">kg</span>@endif
                                </div>
                            </td>

                            <!-- Scrap -->
                            <td class="number">
                                <div class="tabular-nums text-muted text-xs">
                                    {{ $totals['completed_count'] ? $totals['cutting_scrap_kg'] : '—' }}
                                </div>
                            </td>

                            <!-- Total Waste -->
                            <td class="number">
                                <div class="tabular-nums font-semibold text-rose-700 text-xs">
                                    {{ $totals['completed_count'] ? $totals['waste_kg'] : '—' }}
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="number whitespace-nowrap pr-6">
                                <div class="inline-flex items-center gap-1.5">
                                    <a class="inline-flex items-center px-3 py-1.5 rounded-xl border border-line bg-surface text-xs font-semibold text-ink hover:border-accent hover:text-accent shadow-2xs transition" href="{{ route('cutting.'.$type.'.show', $job) }}">
                                        View
                                    </a>
                                    <a class="inline-flex items-center justify-center h-8 w-8 rounded-xl border border-line bg-surface text-muted hover:text-ink hover:border-muted transition shadow-2xs" href="{{ route('cutting.'.$type.'.edit', $job) }}" title="Edit cutting job">
                                        <x-icon name="pen" class="h-3.5 w-3.5"/>
                                    </a>
                                    <x-delete-button :action="route('cutting.'.$type.'.destroy', $job)" label="Delete job" icon="trash" class="inline-flex items-center justify-center h-8 w-8 rounded-xl border border-rose-200 bg-rose-50/70 text-rose-600 hover:bg-rose-100 hover:border-rose-300 transition shadow-2xs cursor-pointer"/>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="empty py-20 text-center">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-accent-soft text-accent-ink mb-4 shadow-sm">
                                    <x-icon name="scissors" class="h-8 w-8"/>
                                </div>
                                <h3 class="text-lg font-bold text-ink">No cutting jobs found</h3>
                                <p class="text-xs text-muted mt-1 max-w-sm mx-auto">Select returned fabric rolls from your inventory to begin your first cutting batch execution.</p>
                                <div class="mt-5">
                                    <a href="{{ route('cutting.'.$type.'.create') }}" class="btn btn-primary shadow-sm shadow-accent/25">
                                        <x-icon name="plus" class="h-4 w-4"/>
                                        Add First Cutting Job
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
