<x-app-layout>
    <x-slot name="header">
        <x-page-heading :title="'Lot '.$lot->lot_number.' · Production Cost Sheet'" subtitle="Comprehensive cost breakdown, component analysis, and cost per finished dozen.">
            <div class="flex items-center gap-2">
                <a class="btn btn-secondary text-xs" href="{{ route('lots.show', $lot) }}">
                    &larr; Back to Lot Journey
                </a>
                <button type="button" class="btn btn-secondary text-xs" onclick="window.print()">
                    Print Cost Sheet
                </button>
            </div>
        </x-page-heading>
    </x-slot>

    <!-- Incomplete Warning Banner if rates missing -->
    @if(!$costSheet['is_complete'])
        <div class="mb-6 rounded-2xl border border-amber-300 bg-amber-50 p-4 text-amber-900 flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2">
                <span class="text-base font-bold">⚠️</span>
                <div>
                    <strong>Cost Sheet Incomplete ({{ count($costSheet['missing_rates']) }} inputs missing):</strong>
                    <span class="text-amber-800 ml-1">{{ implode(' · ', $costSheet['missing_rates']) }}</span>
                </div>
            </div>
            <a href="{{ route('settings.edit') }}" class="btn btn-secondary btn-xs font-semibold">
                Configure Rates in Settings &rarr;
            </a>
        </div>
    @endif

    <!-- Top Executive KPIs -->
    <div class="grid gap-4 mb-7 grid-cols-2 lg:grid-cols-4">
        <div class="panel p-5">
            <span class="text-[11px] font-bold uppercase tracking-wider text-muted">Total Production Cost</span>
            <div class="text-2xl font-black text-ink mt-1">
                {{ $currency }} {{ number_format((float)$costSheet['total_cost'], 2) }}
            </div>
            <span class="hint block mt-1">Cumulative material, mill &amp; factory costs</span>
        </div>
        <div class="panel p-5">
            <span class="text-[11px] font-bold uppercase tracking-wider text-muted">Finished Goods (Good pieces)</span>
            <div class="text-2xl font-black text-ink mt-1">
                {{ number_format((int)$costSheet['finished_pieces']) }} <span class="text-xs font-normal text-muted">pcs</span>
            </div>
            <span class="hint block mt-1">{{ $costSheet['finished_dozens'] }} dozen finished &amp; passed</span>
        </div>
        <div class="panel p-5 border-l-4 border-l-accent bg-accent-soft/30">
            <span class="text-[11px] font-bold uppercase tracking-wider text-accent">Cost Per Finished Dozen</span>
            <div class="text-2xl font-black text-accent mt-1">
                @if($costSheet['cost_per_dozen'] !== null)
                    {{ $currency }} {{ number_format((float)$costSheet['cost_per_dozen'], 2) }}
                @else
                    <span class="text-muted text-base">In production</span>
                @endif
            </div>
            <span class="hint block mt-1">Total Cost &divide; Net Good Dozens</span>
        </div>
        <div class="panel p-5">
            <span class="text-[11px] font-bold uppercase tracking-wider text-muted">Cost Per Finished Piece</span>
            <div class="text-2xl font-black text-ink mt-1">
                @if($costSheet['cost_per_piece'] !== null)
                    {{ $currency }} {{ number_format((float)$costSheet['cost_per_piece'], 2) }}
                @else
                    <span class="text-muted text-base">—</span>
                @endif
            </div>
            <span class="hint block mt-1">Cost per dozen &divide; 12</span>
        </div>
    </div>

    <!-- Component Cost Breakdown Table -->
    <section class="panel mb-7">
        <div class="panel-head flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2>Component Cost Breakdown</h2>
                <span class="hint">Includes all raw materials, partner mill costs, labor, and overhead allocations.</span>
            </div>
            <div class="text-xs font-semibold text-muted">
                Stage: <span class="pill uppercase font-mono">{{ $lot->current_stage }}</span>
            </div>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Cost Component</th>
                        <th>Basis / Volume Processed</th>
                        <th class="number">Rate / Unit Cost</th>
                        <th class="number font-bold">Total Cost ({{ $currency }})</th>
                        <th class="number">% of Total</th>
                        <th class="number">Cost / Doz</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $tot = (float)$costSheet['total_cost'];
                        $finDoz = (float)$costSheet['finished_dozens'];
                    @endphp

                    @if($lot->fabric_source === 'market_purchase')
                        <!-- Market Ready Fabric Rolls -->
                        @php
                            $mFab = $costSheet['components']['market_fabric'] ?? ['cost' => '0', 'details' => []];
                            $mFabCost = (float)$mFab['cost'];
                            $mFabPct = $tot > 0 ? ($mFabCost / $tot * 100) : 0;
                            $mFabPerDoz = $finDoz > 0 ? ($mFabCost / $finDoz) : 0;
                            $mFabKg = \App\Support\Decimal::sum(collect($mFab['details'])->pluck('weight_kg'));
                            $supplier = $lot->returnedRolls->first()?->supplier_name ?? 'Market Supplier';
                        @endphp
                        <tr>
                            <td>
                                <strong class="text-ink">1. Market Fabric · Ready Rolls</strong>
                                <span class="hint block text-[11px]">{{ $supplier }} · {{ count($mFab['details']) }} roll(s)</span>
                            </td>
                            <td>
                                {{ $mFabKg }} kg ready fabric
                            </td>
                            <td class="number text-xs text-muted">Market purchase</td>
                            <td class="number font-bold text-ink">{{ number_format($mFabCost, 2) }}</td>
                            <td class="number font-semibold text-muted">{{ number_format($mFabPct, 1) }}%</td>
                            <td class="number font-semibold text-accent">{{ number_format($mFabPerDoz, 2) }}</td>
                        </tr>
                    @else
                        <!-- 1. Raw Yarn -->
                        @php
                            $yarn = $costSheet['components']['yarn'];
                            $yarnCost = (float)$yarn['cost'];
                            $yarnPct = $tot > 0 ? ($yarnCost / $tot * 100) : 0;
                            $yarnPerDoz = $finDoz > 0 ? ($yarnCost / $finDoz) : 0;
                        @endphp
                        <tr>
                            <td>
                                <strong class="text-ink">1. Raw Material · Yarn</strong>
                                <span class="hint block text-[11px]">{{ count($yarn['details']) }} yarn DIA consumption line(s)</span>
                            </td>
                            <td>
                                {{ \App\Support\Decimal::sum(collect($yarn['details'])->pluck('quantity_kg')) }} kg yarn issued
                            </td>
                            <td class="number text-xs text-muted">Varied by yarn</td>
                            <td class="number font-bold text-ink">{{ number_format($yarnCost, 2) }}</td>
                            <td class="number font-semibold text-muted">{{ number_format($yarnPct, 1) }}%</td>
                            <td class="number font-semibold text-accent">{{ number_format($yarnPerDoz, 2) }}</td>
                        </tr>

                        <!-- 2. Knitting Labor -->
                        @php
                            $knit = $costSheet['components']['knitting'];
                            $knitCost = (float)$knit['cost'];
                            $knitPct = $tot > 0 ? ($knitCost / $tot * 100) : 0;
                            $knitPerDoz = $finDoz > 0 ? ($knitCost / $finDoz) : 0;
                            $knitKg = \App\Support\Decimal::sum(collect($knit['details'])->pluck('output_kg'));
                        @endphp
                        <tr>
                            <td>
                                <strong class="text-ink">2. Mill Labor · Knitting</strong>
                                <span class="hint block text-[11px]">{{ $lot->knittingJob?->company?->name ?? 'Knitting Partner' }}</span>
                            </td>
                            <td>{{ $knitKg }} kg fabric produced</td>
                            <td class="number text-xs text-muted">
                                {{ $lot->knittingJob?->rate_per_kg ?: ($lot->knittingJob?->company?->default_knitting_rate_per_kg ?: \App\Models\Setting::getValue('default_knitting_rate_per_kg', '0')) }}/kg
                            </td>
                            <td class="number font-bold text-ink">{{ number_format($knitCost, 2) }}</td>
                            <td class="number font-semibold text-muted">{{ number_format($knitPct, 1) }}%</td>
                            <td class="number font-semibold text-accent">{{ number_format($knitPerDoz, 2) }}</td>
                        </tr>

                        <!-- 3. Dyeing Labor -->
                        @php
                            $dye = $costSheet['components']['dyeing'];
                            $dyeCost = (float)$dye['cost'];
                            $dyePct = $tot > 0 ? ($dyeCost / $tot * 100) : 0;
                            $dyePerDoz = $finDoz > 0 ? ($dyeCost / $finDoz) : 0;
                            $dyeKg = \App\Support\Decimal::sum(collect($dye['details'])->pluck('returned_kg'));
                        @endphp
                        <tr>
                            <td>
                                <strong class="text-ink">3. Mill Labor · Dyeing</strong>
                                <span class="hint block text-[11px]">{{ $lot->dyeingJobs->first()?->company?->name ?? 'Dyeing Partner' }} ({{ count($dye['details']) }} roll lines)</span>
                            </td>
                            <td>{{ $dyeKg }} kg dyed returned</td>
                            <td class="number text-xs text-muted">
                                {{ $lot->dyeingJobs->first()?->rate_per_kg ?: ($lot->dyeingJobs->first()?->company?->default_dyeing_rate_per_kg ?: \App\Models\Setting::getValue('default_dyeing_rate_per_kg', '0')) }}/kg
                            </td>
                            <td class="number font-bold text-ink">{{ number_format($dyeCost, 2) }}</td>
                            <td class="number font-semibold text-muted">{{ number_format($dyePct, 1) }}%</td>
                            <td class="number font-semibold text-accent">{{ number_format($dyePerDoz, 2) }}</td>
                        </tr>
                    @endif

                    <!-- 4. Cutting Labor -->
                    @php
                        $cut = $costSheet['components']['cutting'];
                        $cutCost = (float)$cut['cost'];
                        $cutPct = $tot > 0 ? ($cutCost / $tot * 100) : 0;
                        $cutPerDoz = $finDoz > 0 ? ($cutCost / $finDoz) : 0;
                    @endphp
                    <tr>
                        <td>
                            <strong class="text-ink">4. Factory Labor · Cutting</strong>
                            <span class="hint block text-[11px]">
                                @if($lot->isUnderpants())
                                    Underpants Cutting {{ $lot->has_rib ? '& Rib' : '' }} steps
                                @else
                                    Body, Neck &amp; Rib cutting steps
                                @endif
                            </span>
                        </td>
                        <td>
                            @foreach($cut['details'] as $c)
                                <span class="pill text-[10px] mr-1">{{ ucfirst($c['type']) }}: {{ $c['dozens'] }} doz</span>
                            @endforeach
                        </td>
                        <td class="number text-xs text-muted">Step rates/doz</td>
                        <td class="number font-bold text-ink">{{ number_format($cutCost, 2) }}</td>
                        <td class="number font-semibold text-muted">{{ number_format($cutPct, 1) }}%</td>
                        <td class="number font-semibold text-accent">{{ number_format($cutPerDoz, 2) }}</td>
                    </tr>

                    <!-- 5. Sewing Assembly -->
                    @php
                        $sew = $costSheet['components']['sewing'];
                        $sewCost = (float)$sew['cost'];
                        $sewPct = $tot > 0 ? ($sewCost / $tot * 100) : 0;
                        $sewPerDoz = $finDoz > 0 ? ($sewCost / $finDoz) : 0;
                    @endphp
                    <tr>
                        <td>
                            <strong class="text-ink">5. Factory Labor · Sewing Stations</strong>
                            <span class="hint block text-[11px]">
                                @if($lot->isUnderpants())
                                    Underpants stations: Over Lock, Lock, Elastic Join, QC 1, Iron, QC 2
                                @else
                                    8 stations: Fold, Lock, Down, Neck Join/Finishing, QC 1, Iron, QC 2
                                @endif
                            </span>
                        </td>
                        <td>
                            {{ count($sew['details']) }} station operations recorded
                        </td>
                        <td class="number text-xs text-muted">Station rates/doz</td>
                        <td class="number font-bold text-ink">{{ number_format($sewCost, 2) }}</td>
                        <td class="number font-semibold text-muted">{{ number_format($sewPct, 1) }}%</td>
                        <td class="number font-semibold text-accent">{{ number_format($sewPerDoz, 2) }}</td>
                    </tr>

                    <!-- 6. Sewing Thread -->
                    @php
                        $thr = $costSheet['components']['thread'];
                        $thrCost = (float)$thr['cost'];
                        $thrPct = $tot > 0 ? ($thrCost / $tot * 100) : 0;
                        $thrPerDoz = $finDoz > 0 ? ($thrCost / $finDoz) : 0;
                    @endphp
                    <tr>
                        <td>
                            <strong class="text-ink">6. Direct Trim · Sewing Thread</strong>
                            <span class="hint block text-[11px]">Overlock &amp; singer thread consumption</span>
                        </td>
                        <td>{{ $finDoz }} finished dozen</td>
                        <td class="number text-xs text-muted">{{ $thr['rate_per_dozen'] }}/doz</td>
                        <td class="number font-bold text-ink">{{ number_format($thrCost, 2) }}</td>
                        <td class="number font-semibold text-muted">{{ number_format($thrPct, 1) }}%</td>
                        <td class="number font-semibold text-accent">{{ number_format($thrPerDoz, 2) }}</td>
                    </tr>

                    <!-- 7. Packing Materials -->
                    @php
                        $pck = $costSheet['components']['packing'];
                        $pckCost = (float)$pck['cost'];
                        $pckPct = $tot > 0 ? ($pckCost / $tot * 100) : 0;
                        $pckPerDoz = $finDoz > 0 ? ($pckCost / $finDoz) : 0;
                    @endphp
                    <tr>
                        <td>
                            <strong class="text-ink">7. Packaging Materials</strong>
                            <span class="hint block text-[11px]">Poly bags, size stickers, barcode stickers, packet master bags</span>
                        </td>
                        <td>
                            {{ count($pck['details']) }} material lines consumed
                        </td>
                        <td class="number text-xs text-muted">Item unit costs</td>
                        <td class="number font-bold text-ink">{{ number_format($pckCost, 2) }}</td>
                        <td class="number font-semibold text-muted">{{ number_format($pckPct, 1) }}%</td>
                        <td class="number font-semibold text-accent">{{ number_format($pckPerDoz, 2) }}</td>
                    </tr>

                    <!-- 8. Carrying & Logistics -->
                    @php
                        $car = $costSheet['components']['carrying'];
                        $carCost = (float)$car['cost'];
                        $carPct = $tot > 0 ? ($carCost / $tot * 100) : 0;
                        $carPerDoz = $finDoz > 0 ? ($carCost / $finDoz) : 0;
                    @endphp
                    <tr>
                        <td>
                            <strong class="text-ink">8. Handling &amp; Logistics</strong>
                            <span class="hint block text-[11px]">Mill transport, cartage, and internal carrying</span>
                        </td>
                        <td>{{ $finDoz }} finished dozen</td>
                        <td class="number text-xs text-muted">{{ $car['rate_per_dozen'] }}/doz</td>
                        <td class="number font-bold text-ink">{{ number_format($carCost, 2) }}</td>
                        <td class="number font-semibold text-muted">{{ number_format($carPct, 1) }}%</td>
                        <td class="number font-semibold text-accent">{{ number_format($carPerDoz, 2) }}</td>
                    </tr>

                    <!-- 9. Factory Overhead & Expenses -->
                    @php
                        $ovh = $costSheet['components']['overhead'];
                        $ovhCost = (float)$ovh['cost'];
                        $ovhPct = $tot > 0 ? ($ovhCost / $tot * 100) : 0;
                        $ovhPerDoz = $finDoz > 0 ? ($ovhCost / $finDoz) : 0;
                    @endphp
                    <tr>
                        <td>
                            <strong class="text-ink">9. Factory Overhead &amp; Shared Expenses</strong>
                            <span class="hint block text-[11px]">Rent, electricity, water, salaries for {{ $ovh['period_month'] ?: 'lot cycle' }}</span>
                        </td>
                        <td>
                            Direct: {{ $currency }} {{ number_format((float)$ovh['direct'], 2) }} &middot; Pro-rata: {{ $currency }} {{ number_format((float)$ovh['allocated'], 2) }}
                        </td>
                        <td class="number text-xs text-muted">Pro-rata volume</td>
                        <td class="number font-bold text-ink">{{ number_format($ovhCost, 2) }}</td>
                        <td class="number font-semibold text-muted">{{ number_format($ovhPct, 1) }}%</td>
                        <td class="number font-semibold text-accent">{{ number_format($ovhPerDoz, 2) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="font-bold text-sm bg-[#f2f7dc]">
                        <td>TOTAL PRODUCTION COST</td>
                        <td colspan="2">{{ $finDoz }} finished dozens ({{ number_format((int)$costSheet['finished_pieces']) }} pcs)</td>
                        <td class="number font-black text-ink">{{ $currency }} {{ number_format($tot, 2) }}</td>
                        <td class="number">100.0%</td>
                        <td class="number font-black text-accent text-base">
                            @if($costSheet['cost_per_dozen'] !== null)
                                {{ $currency }} {{ number_format((float)$costSheet['cost_per_dozen'], 2) }}
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </section>

    <!-- Color & DIA Yield and Cost Matrix -->
    @if(!empty($costSheet['color_dia_breakdown']))
        <section class="panel mb-7">
            <div class="panel-head">
                <div>
                    <h2>Color &amp; DIA Yield &middot; Cost Matrix</h2>
                    <span class="hint">Cutting scrap and reject penalty are absorbed into each finished batch's unit cost.</span>
                </div>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Color</th>
                            <th>DIA</th>
                            <th class="number">Cut Pieces</th>
                            <th class="number font-bold">Good Finished Pcs</th>
                            <th class="number">Good Finished Doz</th>
                            <th class="number">Scrap Fabric</th>
                            <th class="number font-bold">Batch Attributed Cost</th>
                            <th class="number font-bold text-accent">Cost / Finished Doz</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($costSheet['color_dia_breakdown'] as $b)
                            <tr>
                                <td><strong>{{ $b['color'] }}</strong></td>
                                <td><span class="pill">DIA {{ $b['dia'] }}</span></td>
                                <td class="number">{{ number_format($b['cut_pieces']) }} pcs</td>
                                <td class="number font-bold text-ink">{{ number_format($b['finished_pieces']) }} pcs</td>
                                <td class="number">{{ $b['finished_dozens'] }} doz</td>
                                <td class="number text-muted">{{ $b['scrap_kg'] }} kg</td>
                                <td class="number font-bold text-ink">{{ $currency }} {{ number_format((float)$b['batch_cost'], 2) }}</td>
                                <td class="number font-bold text-accent">
                                    @if($b['cost_per_dozen'] !== null)
                                        {{ $currency }} {{ number_format((float)$b['cost_per_dozen'], 2) }}
                                    @else
                                        <span class="text-muted text-xs">In progress</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <!-- Custom Lot Overrides Form -->
    <section class="panel p-6 max-w-2xl">
        <h3 class="mb-2">Lot Specific Cost Overrides</h3>
        <p class="hint mb-4">Override standard thread or carrying rates specifically for Lot {{ $lot->lot_number }}.</p>
        <form method="POST" action="{{ route('lots.cost-rates', $lot) }}" class="grid gap-4 sm:grid-cols-2">
            @csrf
            @method('PUT')
            <label class="field">
                <span>Thread Cost / Dozen ({{ $currency }})</span>
                <input type="number" step="0.01" min="0" name="thread_cost_per_dozen" value="{{ old('thread_cost_per_dozen', $lot->thread_cost_per_dozen ?? \App\Models\Setting::getValue('thread_cost_per_dozen', '0')) }}">
            </label>
            <label class="field">
                <span>Carrying Cost / Dozen ({{ $currency }})</span>
                <input type="number" step="0.01" min="0" name="carrying_cost_per_dozen" value="{{ old('carrying_cost_per_dozen', $lot->carrying_cost_per_dozen ?? \App\Models\Setting::getValue('carrying_cost_per_dozen', '0')) }}">
            </label>
            <div class="sm:col-span-2 flex justify-end">
                <button class="btn btn-secondary">Update Lot Rates</button>
            </div>
        </form>
    </section>
</x-app-layout>
