<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2.5">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-ink">
                    @if($type === 'rib' && ($product ?? '') === 'underpants')
                        Underpants Rib Cutting
                    @elseif($type === 'rib')
                        Sando Rib Cutting
                    @elseif($type === 'cutting')
                        Underpants Cutting
                    @else
                        Cutting ({{ ucfirst($type) }})
                    @endif
                </h1>
                <p class="text-xs text-muted">Transform fabric rolls into precision panels, necks, and rib batches.</p>
            </div>
            <a class="btn btn-primary btn-xs" href="{{ route('cutting.'.$type.'.create', ($type === 'rib' && ($product ?? '') === 'underpants') ? ['product' => 'underpants'] : []) }}">
                <x-icon name="plus" class="h-3 w-3"/>
                Add {{ ($type === 'rib' && ($product ?? '') === 'underpants') ? 'Underpants Rib' : ucfirst($type) }} Job
            </a>
        </div>
    </x-slot>

    <!-- Cutting Workflow Interactive Step Tabs -->
    <x-cutting-flow :type="$type"/>

    <!-- Data Panel -->
    <div class="panel shadow-2xs border border-line bg-surface rounded-xl overflow-hidden">
        <!-- Advanced Filter Toolbar -->
        <div class="px-3.5 py-2 border-b border-line/70 bg-gradient-to-r from-surface via-raised/30 to-surface">
            <form class="flex flex-wrap items-center gap-2" method="GET">
                @if(request('product'))
                    <input type="hidden" name="product" value="{{ request('product') }}">
                @endif
                <div class="flex items-center gap-1.5">
                    <div class="relative">
                        <label class="sr-only" for="from-date">From Date</label>
                        <input id="from-date" type="date" name="from" value="{{ request('from') }}" class="text-xs py-1 px-2 bg-surface border border-line rounded-lg text-ink focus:border-accent">
                    </div>
                    <span class="text-xs text-muted">to</span>
                    <div class="relative">
                        <label class="sr-only" for="to-date">To Date</label>
                        <input id="to-date" type="date" name="to" value="{{ request('to') }}" class="text-xs py-1 px-2 bg-surface border border-line rounded-lg text-ink focus:border-accent">
                    </div>
                </div>

                <select name="staff_id" class="text-xs font-medium py-1 px-2 bg-surface border border-line rounded-lg text-ink focus:border-accent cursor-pointer">
                    <option value="">All Cutting Masters</option>
                    @foreach($staffs as $staff)
                        <option value="{{ $staff->id }}" @selected(request('staff_id')==$staff->id)>{{ $staff->name }}</option>
                    @endforeach
                </select>

                <select name="lot_id" class="text-xs font-medium py-1 px-2 bg-surface border border-line rounded-lg text-ink focus:border-accent cursor-pointer">
                    <option value="">All Lots</option>
                    @foreach($lots as $lot)
                        <option value="{{ $lot->id }}" @selected(request('lot_id')==$lot->id)>{{ $lot->lot_number }}</option>
                    @endforeach
                </select>

                <select name="color" class="text-xs font-medium py-1 px-2 bg-surface border border-line rounded-lg text-ink focus:border-accent cursor-pointer">
                    <option value="">All Colors</option>
                    @foreach($colors as $color)
                        <option value="{{ $color }}" @selected(request('color')===$color)>{{ $color }}</option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-secondary btn-xs">
                    <x-icon name="sliders" class="h-3 w-3 text-accent"/>
                    Filter
                </button>
                @if(request()->hasAny(['from', 'to', 'staff_id', 'lot_id', 'color']))
                    <a href="{{ route('cutting.'.$type.'.index') }}" class="text-xs text-muted hover:text-ink font-medium px-1.5">Reset</a>
                @endif
            </form>
            <p class="text-[10px] text-muted mt-1.5 flex items-center gap-1">
                <span class="h-1.5 w-1.5 rounded-full bg-accent"></span>
                Totals aggregate all rolls in selected cut executions. Remaining fabric is counted towards total process scrap.
            </p>
        </div>

        <!-- Table Container -->
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr class="border-b border-line">
                        <th class="w-10 text-center font-mono">#</th>
                        <th>Execution Date</th>
                        <th>Cutting Master</th>
                        <th>Lots & Colors</th>
                        <th class="number">Rolls</th>
                        <th class="number">Dozens Cut</th>
                        <th class="number">Cut Weight (KG)</th>
                        <th class="number">Scrap (KG)</th>
                        <th class="number">Waste (KG)</th>
                        <th class="number pr-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e4e6ec]">
                    @forelse($jobs as $index => $job)
                        @php $totals = \App\Support\CuttingTotals::sum($job->rows); @endphp
                        <tr class="group transition-all duration-120 hover:bg-[#edf3dc] even:bg-[#f8f9fc]">
                            <!-- Index -->
                            <td class="text-center font-mono text-xs text-muted/80">
                                {{ str_pad((string)($jobs->firstItem() + $index), 2, '0', STR_PAD_LEFT) }}
                            </td>

                            <!-- Date -->
                            <td class="whitespace-nowrap">
                                <a class="font-bold text-ink text-xs hover:text-accent transition inline-flex items-center gap-1" href="{{ route('cutting.'.$type.'.show', $job) }}">
                                    {{ $job->cutting_date->format('d M Y') }}
                                </a>
                            </td>

                            <!-- Staff -->
                            <td>
                                <a class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink hover:text-accent transition" href="{{ route('staffs.show', $job->staff) }}">
                                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-raised text-muted font-bold text-[10px]">
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
                                <span class="pill font-mono text-xs font-bold">
                                    {{ $job->rows->count() }}
                                </span>
                                @if($totals['pending_count'])
                                    <span class="text-[10px] text-amber-600 font-medium ml-1">({{ $totals['pending_count'] }} pend)</span>
                                @endif
                            </td>

                            <!-- Dozens -->
                            <td class="number">
                                <span class="font-bold tabular-nums text-ink text-xs">
                                    {{ $totals['completed_count'] ? $totals['dozens'] : '—' }}
                                </span>
                            </td>

                            <!-- Cut Weight -->
                            <td class="number">
                                <span class="font-bold tabular-nums text-ink text-xs">
                                    {{ $totals['completed_count'] ? $totals['cut_weight_kg'] : '—' }}
                                </span>
                                @if($totals['completed_count'])<span class="text-[10px] text-muted">kg</span>@endif
                            </td>

                            <!-- Scrap -->
                            <td class="number">
                                <span class="tabular-nums text-muted text-xs">
                                    {{ $totals['completed_count'] ? $totals['cutting_scrap_kg'] : '—' }}
                                </span>
                            </td>

                            <!-- Total Waste -->
                            <td class="number">
                                <span class="tabular-nums font-semibold text-rose-700 text-xs">
                                    {{ $totals['completed_count'] ? $totals['waste_kg'] : '—' }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="number whitespace-nowrap pr-4">
                                <div class="inline-flex items-center gap-1">
                                    <a class="btn btn-secondary btn-xs" href="{{ route('cutting.'.$type.'.show', $job) }}">
                                        View
                                    </a>
                                    <a class="btn btn-secondary btn-xs !px-1.5 text-muted hover:text-ink" href="{{ route('cutting.'.$type.'.edit', $job) }}" title="Edit job">
                                        <x-icon name="pen" class="h-3 w-3"/>
                                    </a>
                                    <x-delete-button :action="route('cutting.'.$type.'.destroy', $job)" label="Delete job" icon="trash" class="btn btn-danger btn-xs !px-1.5 cursor-pointer"/>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="empty py-12 text-center">
                                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-accent-soft text-accent-ink mb-2">
                                    <x-icon name="scissors" class="h-5 w-5"/>
                                </div>
                                <h3 class="text-sm font-bold text-ink">No cutting jobs found.</h3>
                                <p class="text-xs text-muted mt-0.5">Start cutting body panels from available returned fabric rolls.</p>
                                <div class="mt-3">
                                    <a href="{{ route('cutting.'.$type.'.create') }}" class="btn btn-primary">
                                        <x-icon name="plus" class="h-3.5 w-3.5"/>
                                        Record First Cut
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
