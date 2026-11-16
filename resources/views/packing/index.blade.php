<x-app-layout>
    <x-slot name="header">
        <x-page-heading title="Packing" subtitle="Inspection, folding, poly bag, stickers, and 6-pc packets.">
            <div class="flex items-center gap-2">
                <a class="btn btn-secondary btn-xs" href="{{ route('lots.packing-materials.index') }}">
                    <x-icon name="layers" class="h-3.5 w-3.5 mr-1 inline text-muted"/>Lot Materials
                </a>
                <a class="btn btn-secondary btn-xs" href="{{ route('packing-materials.index') }}">
                    <x-icon name="package" class="h-3.5 w-3.5 mr-1 inline text-muted"/>Materials
                </a>
                <a class="btn btn-primary btn-xs" href="{{ route('packing-jobs.create') }}">
                    + Record Packing
                </a>
            </div>
        </x-page-heading>
    </x-slot>

    <!-- Lots Ready for Dispatch / Dispatched Pipeline Ribbon -->
    @php
        $lotsForDispatch = $lots->filter(fn($l) => $l->packingRows()->exists());
    @endphp
    @if($lotsForDispatch->isNotEmpty())
        <section class="mb-3 rounded-xl border border-line bg-surface p-3 shadow-2xs">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                <div>
                    <h3 class="text-xs font-bold text-ink uppercase tracking-wider flex items-center gap-1.5">
                        <x-icon name="check" class="h-3.5 w-3.5 text-accent"/>
                        Lot Dispatch & Production Completion Status
                    </h3>
                    <p class="text-[11px] text-muted">Lots automatically mark as Dispatched when all finished pieces are packed and all rolls cut.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach($lotsForDispatch as $dLot)
                    @php
                        $isAutoEligible = $dLot->isEligibleForAutoDispatch();
                        $hasRemainingRolls = $dLot->hasRemainingBodyRolls();
                        $hasUnpacked = $dLot->hasUnpackedFinishedPieces();
                    @endphp
                    <div class="rounded-lg border p-2.5 {{ $dLot->is_dispatched ? 'border-emerald-200 bg-emerald-50/40' : 'border-line bg-raised/30' }} flex flex-col justify-between gap-2">
                        <div class="flex items-center justify-between">
                            <a href="{{ route('lots.show', $dLot) }}" class="font-mono font-bold text-xs text-ink hover:underline">
                                {{ $dLot->lot_number }}
                            </a>
                            @if($dLot->is_dispatched)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                                    Dispatched
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 text-[10px] font-semibold">
                                    <span class="h-1.5 w-1.5 rounded-full bg-indigo-600"></span>
                                    In Packing
                                </span>
                            @endif
                        </div>
                        <div class="text-[11px] text-muted space-y-0.5">
                            <div class="flex items-center justify-between">
                                <span>Uncut rolls:</span>
                                <strong class="{{ $hasRemainingRolls ? 'text-amber-700' : 'text-emerald-700' }}">
                                    {{ $hasRemainingRolls ? 'Remaining' : 'None (All cut)' }}
                                </strong>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Finished pieces:</span>
                                <strong class="{{ $hasUnpacked ? 'text-blue-700' : 'text-emerald-700' }}">
                                    {{ $hasUnpacked ? 'Unpacked pieces left' : 'All packed' }}
                                </strong>
                            </div>
                        </div>
                        <div class="pt-1.5 border-t border-line/60 flex items-center justify-between gap-2">
                            @if($dLot->is_dispatched)
                                <span class="text-[10px] text-emerald-800 font-medium">Finished: {{ $dLot->dispatched_at?->format('d M Y') ?? 'Done' }}</span>
                                <form method="POST" action="{{ route('lots.undispatch', $dLot) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-[10px] text-muted hover:text-ink font-semibold underline" onclick="return confirm('Reopen lot {{ $dLot->lot_number }}?')">
                                        Reopen
                                    </button>
                                </form>
                            @else
                                <span class="text-[10px] text-muted">{{ $isAutoEligible ? 'Ready to auto-dispatch' : 'Packing in progress' }}</span>
                                <form method="POST" action="{{ route('lots.dispatch', $dLot) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-xs !py-0.5 !px-2 text-[10px]" onclick="return confirm('Dispatch lot {{ $dLot->lot_number }} and mark production finished?')">
                                        Dispatch &rarr;
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Compact KPI Telemetry Ribbon -->
    <div class="mb-3 flex flex-wrap items-center gap-2 rounded-xl border border-line bg-surface px-3 py-1.5 shadow-2xs">
        <div class="flex items-center gap-1.5 text-xs">
            <span class="text-muted">Total Packed:</span>
            <strong class="font-bold text-ink">{{ number_format($totalPieces) }} pcs</strong>
            <span class="text-[10px] text-muted">({{ $totalDozens }} doz)</span>
        </div>
        <span class="text-line">•</span>
        <div class="flex items-center gap-1.5 text-xs">
            <span class="text-muted">Packets (6 pcs/pkt):</span>
            <strong class="font-bold text-accent">{{ number_format($totalPackets) }} pkts</strong>
        </div>
        <span class="text-line">•</span>
        <div class="flex items-center gap-1.5 text-xs">
            <span class="text-muted">Material Inventory:</span>
            <strong class="font-bold {{ $lowStockCount > 0 ? 'text-rose-700' : 'text-emerald-700' }}">{{ $materialsSummary->count() }} items</strong>
            @if($lowStockCount > 0)
                <span class="text-[10px] text-rose-600 font-medium">({{ $lowStockCount }} low stock)</span>
            @endif
        </div>
    </div>

    <!-- Packing Execution Jobs List -->
    <section class="panel mb-3 shadow-2xs border border-line bg-surface rounded-xl overflow-hidden">
        <div class="panel-head flex flex-wrap items-center justify-between gap-2.5 px-3.5 py-2">
            <div>
                <h2>Packing jobs</h2>
                <p class="hint">Filter and review recorded packing runs.</p>
            </div>
            <form method="GET" class="flex flex-wrap items-center gap-1.5 text-xs">
                <label class="flex items-center gap-1 text-muted text-[11px]">
                    From <input type="date" name="from" value="{{ request('from') }}" class="py-0.5 px-1.5 rounded border border-line bg-surface text-ink text-xs h-7">
                </label>
                <label class="flex items-center gap-1 text-muted text-[11px]">
                    To <input type="date" name="to" value="{{ request('to') }}" class="py-0.5 px-1.5 rounded border border-line bg-surface text-ink text-xs h-7">
                </label>
                <select name="staff_id" class="py-0.5 pl-2 pr-6 rounded border border-line bg-surface text-ink text-xs font-medium h-7">
                    <option value="">All operators</option>
                    @foreach($staffs as $staff)
                        <option value="{{ $staff->id }}" @selected(request('staff_id') == $staff->id)>{{ $staff->name }}</option>
                    @endforeach
                </select>
                <select name="lot_id" class="py-0.5 pl-2 pr-6 rounded border border-line bg-surface text-ink text-xs font-medium h-7">
                    <option value="">All lots</option>
                    @foreach($lots as $lot)
                        <option value="{{ $lot->id }}" @selected(request('lot_id') == $lot->id)>{{ $lot->lot_number }}</option>
                    @endforeach
                </select>
                <button class="btn btn-secondary btn-xs">Filter</button>
                @if(request()->hasAny(['from', 'to', 'staff_id', 'lot_id']))
                    <a href="{{ route('packing-jobs.index') }}" class="text-xs text-muted hover:text-accent font-medium ml-1">Clear</a>
                @endif
            </form>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Operator</th>
                        <th>Batches packed</th>
                        <th class="number">Packets</th>
                        <th class="number">Dozens + Pcs</th>
                        <th class="number font-bold">Total Pieces</th>
                        <th>Notes</th>
                        <th class="number pr-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jobs as $job)
                        @php
                            $jobPieces = $job->rows->sum('total_pieces');
                            $jobPackets = $job->rows->sum('packets');
                            $jobDozens = \App\Support\Decimal::scale(\App\Support\Decimal::sum($job->rows->pluck('dozens')));
                        @endphp
                        <tr>
                            <td class="font-medium text-ink text-xs">
                                {{ $job->packing_date->format('d/m/Y') }}
                            </td>
                            <td>
                                <span class="font-medium text-ink text-xs">{{ $job->staff->name }}</span>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1 max-w-md">
                                    @foreach($job->rows->take(3) as $row)
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded bg-[#f0f2f5] text-[11px] text-ink font-medium">
                                            <span>{{ $row->lot->lot_number }}</span>
                                            <span class="text-faint">•</span>
                                            <span>{{ $row->color }}</span>
                                            <span class="text-faint">•</span>
                                            <span class="font-bold text-accent">{{ $row->size ?: 'DIA '.$row->dia }}</span>
                                            <span class="text-muted">({{ number_format($row->total_pieces) }} pcs)</span>
                                        </span>
                                    @endforeach
                                    @if($job->rows->count() > 3)
                                        <span class="text-[10px] text-muted self-center">+{{ $job->rows->count() - 3 }} more</span>
                                    @endif
                                </div>
                            </td>
                            <td class="number font-semibold text-accent text-xs">
                                {{ number_format($jobPackets) }} pkts
                            </td>
                            <td class="number text-muted text-xs">
                                {{ $jobDozens }} doz
                            </td>
                            <td class="number font-bold text-ink text-xs">
                                {{ number_format($jobPieces) }} pcs
                            </td>
                            <td class="text-xs text-muted max-w-[180px] truncate">
                                {{ $job->notes ?: '—' }}
                            </td>
                            <td class="number pr-4 whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('packing-jobs.show', $job) }}" class="btn btn-secondary btn-xs">View &rarr;</a>
                                    <a href="{{ route('packing-jobs.edit', $job) }}" class="btn btn-secondary btn-xs">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-6 text-center text-muted text-xs">
                                No packing jobs recorded yet.
                                <div class="mt-1.5">
                                    <a class="btn btn-secondary btn-xs" href="{{ route('packing-jobs.create') }}">Record your first packing run &rarr;</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($jobs->hasPages())
            <div class="p-2.5 border-t border-line">
                {{ $jobs->links() }}
            </div>
        @endif
    </section>

    <!-- Material Quick Balances Section -->
    <section class="panel shadow-2xs border border-line bg-surface rounded-xl overflow-hidden">
        <div class="panel-head flex items-center justify-between px-3.5 py-2">
            <div>
                <h2>Packing materials status</h2>
                <p class="hint">Poly bags, size stickers, other stickers, and packet materials inventory balances.</p>
            </div>
            <a class="btn btn-secondary btn-xs" href="{{ route('packing-materials.index') }}">Manage &rarr;</a>
        </div>
        <div class="grid gap-2 p-3 grid-cols-2 sm:grid-cols-3 lg:grid-cols-6">
            @forelse($materialsSummary as $mat)
                <div class="rounded-lg border p-2 {{ $mat['is_low'] ? 'border-rose-300 bg-rose-50/40' : 'border-line bg-raised/40' }}">
                    <div class="flex items-center justify-between text-[10px] font-semibold text-muted uppercase tracking-wider">
                        <span>{{ str_replace('_', ' ', $mat['category']) }}</span>
                        @if($mat['size_code'])
                            <span class="bg-[#e4e7ec] text-ink font-bold px-1 py-0.2 rounded text-[10px]">{{ $mat['size_code'] }}</span>
                        @endif
                    </div>
                    <strong class="block text-xs font-semibold text-ink mt-0.5 truncate" title="{{ $mat['name'] }}">{{ $mat['name'] }}</strong>
                    <div class="text-sm font-bold mt-0.5 {{ $mat['is_low'] ? 'text-rose-700' : 'text-ink' }}">
                        {{ number_format((float)$mat['balance']) }} <small class="text-[10px] font-normal text-muted">{{ $mat['unit'] }}</small>
                    </div>
                    @if($mat['is_low'])
                        <span class="text-[10px] text-rose-600 font-semibold block">Low (&lt; {{ $mat['min_stock_alert'] }})</span>
                    @endif
                </div>
            @empty
                <div class="col-span-full text-center text-muted py-2 text-xs">
                    No packing materials registered yet. <a class="link" href="{{ route('packing-materials.create') }}">Add packing materials</a>
                </div>
            @endforelse
        </div>
    </section>
</x-app-layout>
