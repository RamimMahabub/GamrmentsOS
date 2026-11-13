<x-app-layout>
    <x-slot name="header">
        <x-page-heading :title="'Packing Job #'.$job->id.' · '.$job->packing_date->format('d/m/Y')" subtitle="Review packed batches and packaging material consumption.">
            <div class="flex items-center gap-2">
                <a class="btn btn-secondary" href="{{ route('packing-jobs.index') }}">Back to list</a>
                <a class="btn btn-secondary" href="{{ route('packing-jobs.edit', $job) }}">Edit</a>
                <form method="POST" action="{{ route('packing-jobs.destroy', $job) }}" onsubmit="return confirm('Are you sure you want to delete this packing job? Allocated packing materials will be returned to stock.')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger">Delete</button>
                </form>
            </div>
        </x-page-heading>
    </x-slot>

    @php
        $totalPieces = $job->rows->sum('total_pieces');
        $totalPackets = $job->rows->sum('packets');
        $totalDozens = \App\Support\Decimal::scale(\App\Support\Decimal::sum($job->rows->pluck('dozens')));
        $totalRejects = $job->rows->sum('rejected_pieces');
    @endphp

    <div class="space-y-6">
        <!-- Overview Metadata & KPIs -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="panel p-5">
                <span class="text-xs font-semibold uppercase tracking-wider text-muted">Operator</span>
                <div class="text-xl font-bold text-ink mt-1">
                    {{ $job->staff->name }}
                </div>
                <span class="text-[11px] text-muted mt-1 block">{{ $job->staff->phone ?: 'Active staff' }}</span>
            </div>
            <div class="panel p-5">
                <span class="text-xs font-semibold uppercase tracking-wider text-muted">Packed Pieces</span>
                <div class="text-xl font-bold text-ink mt-1">
                    {{ number_format($totalPieces) }} <span class="text-xs font-normal text-muted">pcs</span>
                </div>
                <span class="text-[11px] text-muted mt-1 block">{{ $totalDozens }} dozens</span>
            </div>
            <div class="panel p-5">
                <span class="text-xs font-semibold uppercase tracking-wider text-muted">Packets (6 pcs/pkt)</span>
                <div class="text-xl font-bold text-accent mt-1">
                    {{ number_format($totalPackets) }} <span class="text-xs font-normal text-muted">pkts</span>
                </div>
                <span class="text-[11px] text-muted mt-1 block">2 packets per dozen</span>
            </div>
            <div class="panel p-5">
                <span class="text-xs font-semibold uppercase tracking-wider text-muted">Packing Rejects</span>
                <div class="text-xl font-bold text-rose-700 mt-1">
                    {{ number_format($totalRejects) }} <span class="text-xs font-normal text-muted">pcs</span>
                </div>
                <span class="text-[11px] text-muted mt-1 block">Damaged / soiled</span>
            </div>
        </div>

        @if($job->notes)
            <div class="panel p-4 text-xs">
                <strong class="text-ink">Notes:</strong> <span class="text-muted">{{ $job->notes }}</span>
            </div>
        @endif

        <!-- Packed Batches Table -->
        <section class="panel">
            <div class="panel-head">
                <div>
                    <h2>Batches packed in this run</h2>
                    <p class="hint">Detailed breakdown by lot, color, DIA, and assigned size.</p>
                </div>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Lot</th>
                            <th>Color</th>
                            <th>DIA</th>
                            <th>Size</th>
                            <th class="number font-bold text-accent">Packets (6pc)</th>
                            <th class="number">Dozens + Loose</th>
                            <th class="number font-bold">Total Pieces</th>
                            <th class="number text-rose-700">Rejects</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($job->rows as $row)
                            <tr>
                                <td>
                                    <a class="link font-bold" href="{{ route('lots.show', $row->lot) }}">
                                        {{ $row->lot->lot_number }}
                                    </a>
                                </td>
                                <td><strong>{{ $row->color }}</strong></td>
                                <td><span class="pill">DIA {{ $row->dia }}</span></td>
                                <td><span class="font-bold text-xs uppercase bg-[#eee] px-2 py-0.5 rounded">{{ $row->size ?: '—' }}</span></td>
                                <td class="number font-bold text-accent">{{ number_format($row->packets) }} pkts</td>
                                <td class="number">{{ (int)$row->dozens }} doz {{ $row->pieces ? '+ '.$row->pieces.' pcs' : '' }}</td>
                                <td class="number font-bold">{{ number_format($row->total_pieces) }} pcs</td>
                                <td class="number text-rose-700">{{ $row->rejected_pieces ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Packing Materials Ledger Movements -->
        <section class="panel">
            <div class="panel-head">
                <div>
                    <h2>Packaging materials consumed</h2>
                    <p class="hint">Inventory deducted automatically from warehouse stock for this run.</p>
                </div>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Material item</th>
                            <th>Category</th>
                            <th>Size</th>
                            <th class="number font-bold">Deducted quantity</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($job->materialMovements as $mv)
                            <tr>
                                <td>
                                    <strong>{{ $mv->material->name }}</strong>
                                </td>
                                <td>
                                    <span class="pill text-xs uppercase">{{ str_replace('_', ' ', $mv->material->category) }}</span>
                                </td>
                                <td>
                                    {{ $mv->material->size_code ?: '—' }}
                                </td>
                                <td class="number font-bold text-rose-700">
                                    {{ abs((float) $mv->quantity) }} {{ $mv->material->unit }}
                                </td>
                                <td class="text-xs text-muted">
                                    {{ $mv->note }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-muted">
                                    No material movements logged for this job (materials may not have been registered when this job was saved).
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>

