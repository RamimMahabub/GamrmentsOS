<x-app-layout>
    <x-slot name="header">
        <x-page-heading title="Packing" subtitle="Inspection, folding, poly bag, stickers, and 6-pc packets.">
            <div class="flex items-center gap-2">
                <a class="btn btn-secondary" href="{{ route('packing-materials.index') }}">
                    <x-icon name="package" class="h-4 w-4 mr-1 inline text-muted"/>Materials & Inventory
                </a>
                <a class="btn btn-primary" href="{{ route('packing-jobs.create') }}">
                    + Record Packing
                </a>
            </div>
        </x-page-heading>
    </x-slot>

    <div class="space-y-6">
        <!-- KPI Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="panel p-5">
                <span class="text-xs font-semibold uppercase tracking-wider text-muted">Total Packed Pieces</span>
                <div class="text-2xl font-bold text-ink mt-2">
                    {{ number_format($totalPieces) }} <span class="text-xs font-normal text-muted">pcs</span>
                </div>
                <span class="text-[11px] text-muted mt-1 block">Finished good output</span>
            </div>
            <div class="panel p-5">
                <span class="text-xs font-semibold uppercase tracking-wider text-muted">Packets (6 pcs/pkt)</span>
                <div class="text-2xl font-bold text-accent mt-2">
                    {{ number_format($totalPackets) }} <span class="text-xs font-normal text-muted">pkts</span>
                </div>
                <span class="text-[11px] text-muted mt-1 block">2 packets per dozen</span>
            </div>
            <div class="panel p-5">
                <span class="text-xs font-semibold uppercase tracking-wider text-muted">Total Dozens</span>
                <div class="text-2xl font-bold text-ink mt-2">
                    {{ $totalDozens }} <span class="text-xs font-normal text-muted">doz</span>
                </div>
                <span class="text-[11px] text-muted mt-1 block">12 pieces per dozen</span>
            </div>
            <div class="panel p-5 {{ $lowStockCount > 0 ? 'border-rose-300 bg-rose-50/50' : '' }}">
                <span class="text-xs font-semibold uppercase tracking-wider text-muted">Material Inventory</span>
                <div class="text-2xl font-bold mt-2 {{ $lowStockCount > 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                    {{ $materialsSummary->count() }} <span class="text-xs font-normal text-muted">items</span>
                </div>
                <span class="text-[11px] mt-1 block {{ $lowStockCount > 0 ? 'text-rose-600 font-medium' : 'text-muted' }}">
                    {{ $lowStockCount > 0 ? $lowStockCount.' items low in stock' : 'All items in stock' }}
                </span>
            </div>
        </div>

        <!-- Packing Execution Jobs List -->
        <section class="panel">
            <div class="panel-head flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2>Packing jobs</h2>
                    <p class="hint">Filter and review recorded packing runs.</p>
                </div>
                <form method="GET" class="flex flex-wrap items-center gap-2 text-xs">
                    <label class="flex items-center gap-1.5 text-muted">
                        From <input type="date" name="from" value="{{ request('from') }}" class="py-1 px-2 rounded-lg border border-line bg-surface text-ink text-xs">
                    </label>
                    <label class="flex items-center gap-1.5 text-muted">
                        To <input type="date" name="to" value="{{ request('to') }}" class="py-1 px-2 rounded-lg border border-line bg-surface text-ink text-xs">
                    </label>
                    <select name="staff_id" class="py-1 pl-2 pr-6 rounded-lg border border-line bg-surface text-ink text-xs font-medium">
                        <option value="">All operators</option>
                        @foreach($staffs as $staff)
                            <option value="{{ $staff->id }}" @selected(request('staff_id') == $staff->id)>{{ $staff->name }}</option>
                        @endforeach
                    </select>
                    <select name="lot_id" class="py-1 pl-2 pr-6 rounded-lg border border-line bg-surface text-ink text-xs font-medium">
                        <option value="">All lots</option>
                        @foreach($lots as $lot)
                            <option value="{{ $lot->id }}" @selected(request('lot_id') == $lot->id)>{{ $lot->lot_number }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-secondary text-xs py-1 px-3">Filter</button>
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
                            <th>Actions</th>
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
                                <td>
                                    <strong>{{ $job->packing_date->format('d/m/Y') }}</strong>
                                </td>
                                <td>
                                    <span class="font-medium text-ink">{{ $job->staff->name }}</span>
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-1.5 max-w-md">
                                        @foreach($job->rows->take(3) as $row)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-[#f0f2f5] text-[11px] text-ink font-medium">
                                                <span>{{ $row->lot->lot_number }}</span>
                                                <span class="text-faint">•</span>
                                                <span>{{ $row->color }}</span>
                                                <span class="text-faint">•</span>
                                                <span class="font-bold text-accent">{{ $row->size ?: 'DIA '.$row->dia }}</span>
                                                <span class="text-muted">({{ number_format($row->total_pieces) }} pcs)</span>
                                            </span>
                                        @endforeach
                                        @if($job->rows->count() > 3)
                                            <span class="text-[11px] text-muted self-center">+{{ $job->rows->count() - 3 }} more</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="number font-semibold text-accent">
                                    {{ number_format($jobPackets) }} pkts
                                </td>
                                <td class="number text-muted">
                                    {{ $jobDozens }} doz
                                </td>
                                <td class="number font-bold text-ink">
                                    {{ number_format($jobPieces) }} pcs
                                </td>
                                <td class="text-xs text-muted max-w-[200px] truncate">
                                    {{ $job->notes ?: '—' }}
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('packing-jobs.show', $job) }}" class="link text-xs">View &rarr;</a>
                                        <a href="{{ route('packing-jobs.edit', $job) }}" class="text-muted hover:text-ink text-xs">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-muted">
                                    No packing jobs recorded yet.
                                    <div class="mt-2">
                                        <a class="btn btn-secondary text-xs" href="{{ route('packing-jobs.create') }}">Record your first packing run &rarr;</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($jobs->hasPages())
                <div class="p-4 border-t border-line">
                    {{ $jobs->links() }}
                </div>
            @endif
        </section>

        <!-- Material Quick Balances Section -->
        <section class="panel">
            <div class="panel-head flex items-center justify-between">
                <div>
                    <h2>Packing materials status</h2>
                    <p class="hint">Poly bags, size stickers, other stickers, and packet materials inventory balances.</p>
                </div>
                <a class="btn btn-secondary text-xs" href="{{ route('packing-materials.index') }}">Manage materials &rarr;</a>
            </div>
            <div class="grid gap-3 p-6 grid-cols-2 sm:grid-cols-3 lg:grid-cols-6">
                @forelse($materialsSummary as $mat)
                    <div class="rounded-xl border p-3 {{ $mat['is_low'] ? 'border-rose-300 bg-rose-50/40' : 'border-line bg-raised/40' }}">
                        <div class="flex items-center justify-between text-[11px] font-semibold text-muted uppercase tracking-wider">
                            <span>{{ str_replace('_', ' ', $mat['category']) }}</span>
                            @if($mat['size_code'])
                                <span class="bg-[#e4e7ec] text-ink font-bold px-1.5 py-0.2 rounded">{{ $mat['size_code'] }}</span>
                            @endif
                        </div>
                        <strong class="block text-sm font-semibold text-ink mt-1 truncate" title="{{ $mat['name'] }}">{{ $mat['name'] }}</strong>
                        <div class="text-lg font-bold mt-1 {{ $mat['is_low'] ? 'text-rose-700' : 'text-ink' }}">
                            {{ number_format((float)$mat['balance']) }} <small class="text-xs font-normal text-muted">{{ $mat['unit'] }}</small>
                        </div>
                        @if($mat['is_low'])
                            <span class="text-[10px] text-rose-600 font-semibold block mt-0.5">Below alert ({{ $mat['min_stock_alert'] }})</span>
                        @endif
                    </div>
                @empty
                    <div class="col-span-full text-center text-muted py-4 text-xs">
                        No packing materials registered yet. <a class="link" href="{{ route('packing-materials.create') }}">Add packing materials</a> to track poly bags, stickers, and packet bags.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>

