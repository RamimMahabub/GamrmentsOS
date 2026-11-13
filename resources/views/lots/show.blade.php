<x-app-layout>
<x-slot name="header">
    <x-page-heading :title="$lot->lot_number" subtitle="The complete journey of this lot, from yarn to sewn garments.">
        <div class="flex flex-wrap items-center gap-3">
            @if(isset($allLots) && $allLots->count() > 1)
                <div class="flex items-center gap-2">
                    <span class="text-xs text-muted">Switch lot:</span>
                    <div class="flex flex-wrap gap-1">
                        @foreach($allLots as $otherLot)
                            <a href="{{ route('lots.show', $otherLot) }}"
                               class="pill text-xs font-mono {{ $otherLot->id === $lot->id ? 'ring-2 ring-accent font-bold bg-raised' : 'hover:bg-raised' }}">
                                {{ $otherLot->lot_number }}
                                <span class="text-[10px] text-muted">({{ ucfirst($otherLot->current_stage) }})</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
            @php
            $stageBadgeClasses = match($lot->current_stage) {
                'packing' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
                'sewing' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                'cutting' => 'bg-blue-100 text-blue-800 border-blue-300',
                'dyeing' => 'bg-purple-100 text-purple-800 border-purple-300',
                default => 'bg-lime-100 text-lime-900 border-lime-300',
            };
            $stageDotClasses = match($lot->current_stage) {
                'packing' => 'bg-indigo-600',
                'sewing' => 'bg-emerald-600',
                'cutting' => 'bg-blue-600',
                'dyeing' => 'bg-purple-600',
                default => 'bg-lime-600',
            };
            @endphp
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border {{ $stageBadgeClasses }}">
                <span class="h-1.5 w-1.5 rounded-full {{ $stageDotClasses }}"></span>
                {{ ucfirst($lot->current_stage) }}
            </span>
        </div>
    </x-page-heading>
</x-slot>

@if($lot->current_stage === 'knitting' && isset($allLots) && $allLots->contains(fn($l) => in_array($l->current_stage, ['cutting', 'sewing'])))
    @php
        $activeLot = $allLots->first(fn($l) => in_array($l->current_stage, ['cutting', 'sewing']));
    @endphp
    <div class="mb-6 rounded-xl border border-line bg-surface/90 p-4 flex flex-wrap items-center justify-between gap-3 text-xs">
        <span class="text-muted">
            Viewing <strong>{{ $lot->lot_number }}</strong> (currently in Knitting stage; dyeing, cutting & sewing have not started for this lot yet).
        </span>
        @if($activeLot)
            <a href="{{ route('lots.show', $activeLot) }}" class="btn btn-secondary text-xs">
                Switch to {{ $activeLot->lot_number }} ({{ ucfirst($activeLot->current_stage) }} stage) &rarr;
            </a>
        @endif
    </div>
@endif

<div class="mb-7 flex flex-wrap items-center gap-3 md:gap-4 rounded-xl border border-line bg-surface px-6 py-5 text-sm">
    @if($lot->fabric_source === 'market_purchase')
        <a class="flex items-center gap-2 font-semibold text-accent hover:underline" href="#market-rolls-section"><x-icon name="roll"/>Market Ready Rolls</a>
        <x-icon name="arrow" class="text-faint"/>
        @if($lot->cuttingRows->isNotEmpty())
            <a class="flex items-center gap-2 font-semibold text-accent hover:underline" href="#cutting-section"><x-icon name="scissors"/>Cutting</a>
        @else
            <span class="flex items-center gap-2 text-muted"><x-icon name="scissors"/>Cutting</span>
        @endif
        <x-icon name="arrow" class="text-faint"/>
        @if($lot->sewingRows->isNotEmpty())
            <a class="flex items-center gap-2 font-semibold text-accent hover:underline" href="#sewing-section"><x-icon name="factory"/>Sewing</a>
        @else
            <span class="flex items-center gap-2 text-muted"><x-icon name="factory"/>Sewing</span>
        @endif
        <x-icon name="arrow" class="text-faint"/>
        @if($lot->packingRows->isNotEmpty())
            <a class="flex items-center gap-2 font-semibold text-accent hover:underline" href="#packing-section"><x-icon name="package"/>Packing</a>
        @else
            <span class="flex items-center gap-2 text-muted"><x-icon name="package"/>Packing</span>
        @endif
    @else
        <span class="flex items-center gap-2 font-semibold text-accent"><x-icon name="spool"/>Yarn</span>
        <x-icon name="arrow" class="text-faint"/>
        <a class="flex items-center gap-2 font-semibold text-accent hover:underline" href="{{ route('knitting-jobs.show',$lot->knittingJob) }}"><x-icon name="layers"/>Knitting</a>
        <x-icon name="arrow" class="text-faint"/>
        @if($lot->dyeingJobs->isNotEmpty())
            <a class="flex items-center gap-2 font-semibold text-accent hover:underline" href="#dyeing-section"><x-icon name="drop"/>Dyeing</a>
        @else
            <span class="flex items-center gap-2 text-muted"><x-icon name="drop"/>Dyeing</span>
        @endif
        <x-icon name="arrow" class="text-faint"/>
        @if(count($dyed))
            <a class="flex items-center gap-2 font-semibold text-accent hover:underline" href="#fabric-section"><x-icon name="roll"/>Dyed Fabric</a>
        @else
            <span class="flex items-center gap-2 text-muted"><x-icon name="roll"/>Dyed Fabric</span>
        @endif
        <x-icon name="arrow" class="text-faint"/>
        @if($lot->cuttingRows->isNotEmpty())
            <a class="flex items-center gap-2 font-semibold text-accent hover:underline" href="#cutting-section"><x-icon name="scissors"/>Cutting</a>
        @else
            <span class="flex items-center gap-2 text-muted"><x-icon name="scissors"/>Cutting</span>
        @endif
        <x-icon name="arrow" class="text-faint"/>
        @if($lot->sewingRows->isNotEmpty())
            <a class="flex items-center gap-2 font-semibold text-accent hover:underline" href="#sewing-section"><x-icon name="factory"/>Sewing</a>
        @else
            <span class="flex items-center gap-2 text-muted"><x-icon name="factory"/>Sewing</span>
        @endif
        <x-icon name="arrow" class="text-faint"/>
        @if($lot->packingRows->isNotEmpty())
            <a class="flex items-center gap-2 font-semibold text-accent hover:underline" href="#packing-section"><x-icon name="package"/>Packing</a>
        @else
            <span class="flex items-center gap-2 text-muted"><x-icon name="package"/>Packing</span>
        @endif
    @endif
</div>

@if(isset($costSummary))
    <div class="mb-7 rounded-2xl border border-line bg-surface p-5 flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-6">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-muted">Cost Per Finished Dozen</span>
                <div class="text-xl font-black text-accent mt-0.5">
                    @if($costSummary['cost_per_dozen'] !== null)
                        {{ $currency }} {{ number_format((float)$costSummary['cost_per_dozen'], 2) }}
                        <span class="text-xs font-normal text-muted">({{ $currency }} {{ number_format((float)$costSummary['cost_per_piece'], 2) }} / pc)</span>
                    @else
                        <span class="text-muted text-sm font-semibold">In production</span>
                    @endif
                </div>
            </div>
            <div class="hidden sm:block h-8 w-px bg-line"></div>
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-muted">Total Production Spent</span>
                <div class="text-lg font-bold text-ink mt-0.5">
                    {{ $currency }} {{ number_format((float)$costSummary['total_cost'], 2) }}
                </div>
            </div>
            @if(!$costSummary['is_complete'])
                <span class="pill bg-amber-50 text-amber-800 border-amber-300 text-xs">
                    ⚠️ {{ count($costSummary['missing_rates']) }} rate(s) missing
                </span>
            @else
                <span class="pill bg-emerald-50 text-emerald-800 border-emerald-300 text-xs">
                    ✓ Rates verified
                </span>
            @endif
        </div>
        <a href="{{ route('lots.cost-sheet', $lot) }}" class="btn btn-secondary text-xs font-semibold">
            View Full Cost Sheet &rarr;
        </a>
    </div>
@endif

@if($lot->fabric_source === 'market_purchase')
    <div class="mb-6 panel" id="market-rolls-section">
        <div class="panel-head flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2>Market Ready Rolls Fabric Source</h2>
                <span class="hint">Lot created directly from ready market rolls · {{ $lot->lot_date->format('d/m/Y') }}</span>
            </div>
            <a class="link text-xs" href="{{ route('fabric.index', ['lot_id' => $lot->id]) }}">View in Fabric Inventory &rarr;</a>
        </div>
        <div class="grid gap-6 p-6 sm:grid-cols-4 bg-[#fafafc] border-b border-line">
            <div>
                <p class="hint">PRODUCT TYPE</p>
                <p class="mt-1 text-base font-bold capitalize text-ink">{{ $lot->product_type }}</p>
            </div>
            <div>
                <p class="hint">SUPPLIER</p>
                <p class="mt-1 text-base font-bold text-ink">{{ $lot->returnedRolls->first()?->supplier_name ?: 'Market Supplier' }}</p>
            </div>
            <div>
                <p class="hint">PURCHASE PRICE</p>
                <p class="mt-1 text-base font-bold text-accent">{{ $currency }} {{ number_format((float)($lot->returnedRolls->first()?->purchase_price_per_kg ?? 0), 2) }} / kg</p>
            </div>
            <div>
                <p class="hint">TOTAL FABRIC</p>
                <p class="mt-1 text-base font-bold text-ink">{{ $pipeline['roll_weight'] }} kg <span class="text-xs font-normal text-muted">({{ $pipeline['roll_count'] }} rolls)</span></p>
            </div>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Roll #</th>
                        <th>Color</th>
                        <th>DIA</th>
                        <th class="number">Weight, KG</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lot->returnedRolls as $roll)
                        <tr>
                            <td class="font-mono font-bold">{{ $roll->number }}</td>
                            <td>{{ $roll->color?->name ?? '—' }}</td>
                            <td><span class="pill">DIA {{ $roll->dia }}</span></td>
                            <td class="number font-bold">{{ $roll->weight_kg }} kg</td>
                            <td>
                                @if($roll->is_available)
                                    <span class="pill bg-emerald-50 text-emerald-800 border-emerald-300 text-xs">Ready for Cutting</span>
                                @else
                                    <span class="pill bg-gray-100 text-gray-700 border-gray-300 text-xs">Cut / In Production</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="mb-6 panel"><div class="panel-head"><h2>Yarn & knitting partner</h2><span class="hint">{{ $lot->lot_date->format('d/m/Y') }}</span></div><div class="grid gap-6 p-6 md:grid-cols-2"><div><p class="hint">YARNS USED</p>@foreach($lot->knittingJob->yarnLinks as $link)<a class="link mt-2 block" href="{{ route('yarns.show',$link->yarn) }}">{{ $link->yarn->name }} · {{ $link->yarn->yarn_type }}</a>@endforeach</div><div><p class="hint">KNITTING COMPANY</p><p class="mt-2 font-medium">{{ $lot->knittingJob->company->name }}</p><p class="hint mt-1">{{ $lot->knittingJob->company->address }}</p></div></div></div>
    <section class="panel mb-6"><div class="panel-head"><h2>Weight through the stages</h2><span class="hint">All balances in kg</span></div><div class="grid gap-5 p-6 sm:grid-cols-3">
    @foreach(['input' => 'Yarn issued', 'knitting_loss' => 'Knitting loss', 'output' => 'Knitted fabric', 'given' => 'Sent to dyeing', 'undyed' => 'Grey fabric remaining', 'pending' => 'At dyeing (given weight)', 'dyeing_loss' => 'Dyeing loss / negative = gain', 'returned' => 'Returned dyed fabric', 'roll_weight' => 'Recorded roll weight'] as $key => $label)
    <div><p class="hint">{{ $label }}</p><p class="mt-2 text-xl font-semibold">{{ $pipeline[$key] }} kg</p></div>
    @endforeach
    </div><div class="border-t p-6 text-sm"><p>{{ $pipeline['roll_count'] }} rolls recorded for the next stage.</p><p>Returned weight without roll details: {{ $pipeline['unitemized'] }} kg.</p><p class="hint mt-2">Yarn issued = knitting loss + grey fabric remaining + fabric at dyeing + dyeing loss + returned fabric. Dyeing loss uses only rows with recorded returns.</p></div></section>
    <x-knitting-table :job="$lot->knittingJob"/>
    <div class="panel my-6"><div class="panel-head"><h2>Fabric balance by DIA</h2><a class="link text-xs" href="{{ route('dyeing-jobs.create',['lot_id'=>$lot->id]) }}">Send available fabric to dyeing →</a></div><div class="table-wrap"><table class="data-table"><thead><tr><th>DIA</th><th class="number">KNITTED OUTPUT, KG</th><th class="number">GIVEN TO DYEING, KG</th><th class="number">AVAILABLE, KG</th></tr></thead><tbody>@foreach($fabric as $row)<tr><td><span class="pill">{{ $row['dia'] }}</span></td><td class="number">{{ $row['output_kg'] }}</td><td class="number">{{ $row['consumed_kg'] }}</td><td class="number font-semibold text-accent">{{ $row['available_kg'] }}</td></tr>@endforeach</tbody></table></div></div>
    <div id="dyeing-section"></div>
    @forelse($lot->dyeingJobs as $job)<div class="mb-6 mt-8 flex items-center justify-between"><div><h2>Dyeing · {{ $job->company->name }}</h2><p class="hint mt-1">Sent {{ $job->entry_date->format('d/m/Y') }} · Returned {{ $job->return_date?->format('d/m/Y') ?? 'pending' }}</p></div><a class="link text-xs" href="{{ route('dyeing-jobs.show',$job) }}">Job details ↗</a></div><x-dyeing-tables :job="$job"/>@empty<div class="panel p-10 text-center text-muted">No dyeing allocations yet.</div>@endforelse
    @if(count($dyed))
    <div class="panel mt-7" id="fabric-section">
        <div class="panel-head flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2>Fabric Stock (Dyed Fabric)</h2>
                <span class="hint">Balances by color and DIA after dyeing</span>
            </div>
            <a class="link text-xs" href="{{ route('fabric.index', ['lot_id' => $lot->id]) }}">View in Fabric Inventory &rarr;</a>
        </div>
        <div class="table-wrap"><table class="data-table"><thead><tr><th>COLOR</th><th>DIA</th><th class="number">RETURNED, KG</th></tr></thead><tbody>@foreach($dyed as $row)<tr><td>{{ $row['color'] }}</td><td>{{ $row['dia'] }}</td><td class="number">{{ $row['quantity_kg'] }}</td></tr>@endforeach</tbody></table></div>
    </div>
    @endif
@endif

<section class="panel mt-7" id="cutting-section">
    <div class="panel-head">
        <h2>Cutting</h2>
        <span class="hint">
            @if($lot->product_type !== 'sando')
                Underpants cutting step: Cut pieces flow directly into sewing assembly.
            @else
                Body pieces flow into neck cutting with their original lot, color, DIA and dozens.
            @endif
        </span>
    </div>
    @php
        $cuttingRows = $lot->cuttingRows;
        $isUnderpants = $lot->product_type !== 'sando';
    @endphp
    @if($cuttingRows->isEmpty())
        <div class="p-10 text-center text-muted">
            No cutting operations recorded for this lot yet.
            @if(count($dyed) || $lot->fabric_source === 'market_purchase')
                <div class="mt-3">
                    @if($isUnderpants)
                        <a class="btn btn-secondary text-xs" href="{{ route('cutting.cutting.create') }}">Send rolls to Cutting &rarr;</a>
                    @else
                        <a class="btn btn-secondary text-xs" href="{{ route('cutting.body.create') }}">Send returned rolls to Cutting &rarr;</a>
                    @endif
                </div>
            @else
                <p class="hint mt-2 text-xs">This lot is currently at {{ ucfirst($lot->current_stage) }} stage and must complete dyeing before cutting.</p>
            @endif
        </div>
    @else
        @if(!$isUnderpants)
            <div class="p-6"><x-cutting-flow/></div>
        @endif
        @php
            $sections = $isUnderpants
                ? ($lot->has_rib || $cuttingRows->where('cutting_type', 'rib')->isNotEmpty() ? ['cutting', 'rib'] : ['cutting'])
                : ['body', 'neck', 'rib'];
            $bodyTotals = \App\Support\CuttingTotals::sum($cuttingRows->where('cutting_type', $isUnderpants ? 'cutting' : 'body'));
        @endphp
        @foreach($sections as $section)
        @php $sectionRows=$cuttingRows->where('cutting_type',$section); $linkedRows=$section === 'neck' ? $sectionRows->whereNotNull('source_body_row_id') : $sectionRows; $cuttingTotals=\App\Support\CuttingTotals::sum($linkedRows); $inputWeight=$section === 'neck' ? $bodyTotals['cut_weight_kg'] : $pipeline['returned']; $assignedWeight=$section === 'neck' ? \App\Support\Decimal::sum($linkedRows->pluck('roll_weight_kg')) : $cuttingTotals['roll_weight_kg']; $uncut=\App\Support\Decimal::scale(\App\Support\Decimal::of($inputWeight)->minus($assignedWeight)); @endphp
        <div class="border-t p-6"><h3 class="mb-3">{{ ucfirst($section) }}</h3>
        @if($section === 'neck')
        @php $assignedPieces=\App\Support\Decimal::sum($linkedRows->map(fn($row) => \App\Support\CuttingQuantity::pieces($row))); $assignedDozens=\App\Support\CuttingQuantity::dozens($assignedPieces); $readyPieces=(string) \App\Support\Decimal::of($bodyTotals['pieces'])->minus($assignedPieces); $readyDozens=\App\Support\CuttingQuantity::dozens($readyPieces); @endphp
        <p class="font-semibold">{{ \App\Support\CuttingQuantity::display($readyPieces) }} ready for neck cutting</p><p><strong>{{ $readyDozens }} dozen ready for neck cutting</strong> · {{ $assignedDozens }} dozen assigned from {{ $bodyTotals['dozens'] }} dozen body-cut pieces.</p>
        @if($sectionRows->count() !== $linkedRows->count())<p class="text-warning">Older neck rows need their body source reviewed; they are excluded from this flow balance.</p>@endif
        @endif
        @if($cuttingTotals['pending_count'])<p class="hint">{{ $cuttingTotals['pending_count'] }} reserved {{ $section === 'neck' ? 'body batches' : 'rolls' }} ({{ $cuttingTotals['pending_weight_kg'] }} kg) await cutting results.</p>@endif<p>{{ $inputWeight }} kg {{ $section === 'neck' ? 'body-cut input' : 'returned' }} = {{ $cuttingTotals['cut_weight_kg'] }} kg cut + {{ $cuttingTotals['waste_kg'] }} kg {{ strtolower(config('cutting.labels.waste_kg')) }}@if($section === 'neck' && $cuttingTotals['pending_count']) + {{ $cuttingTotals['pending_weight_kg'] }} kg pending neck cutting @endif + {{ $uncut }} kg {{ $section === 'neck' ? 'awaiting neck cutting' : 'still uncut' }}.</p><p class="hint">{{ config('cutting.labels.waste_kg') }} includes {{ $cuttingTotals['cutting_scrap_kg'] }} kg {{ strtolower(config('cutting.labels.cutting_scrap_kg')) }} + {{ $cuttingTotals['remaining_fabric_kg'] }} kg {{ strtolower(config('cutting.labels.remaining_fabric_kg')) }}.</p>
        <div class="mt-4"><x-cutting-batches :rows="$sectionRows"/></div></div>
        <x-cutting-summary :rows="$sectionRows" :type="$section"/>
        @endforeach
    @endif
</section>

<section class="panel mt-7" id="sewing-section">
    <div class="panel-head flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2>Sewing</h2>
            <p class="hint mt-1">
                @if($lot->product_type !== 'sando')
                    Underpants assembly: Over Lock &rarr; Lock &rarr; Elastic Join &rarr; QC 1 &rarr; Iron &rarr; QC 2.
                @else
                    Multi-station assembly: Fold &rarr; Lock &rarr; Down &rarr; Neck Join &rarr; Neck Finishing.
                @endif
            </p>
        </div>
        <a class="link text-xs" href="{{ route('sewing-jobs.index', ['lot_id' => $lot->id]) }}">Open Sewing Module &rarr;</a>
    </div>

    @php
        $sewingRows = $lot->sewingRows;
        if ($lot->product_type !== 'sando') {
            $sewingSteps = [];
            foreach (\App\Support\UnderpantsSewingConfig::allSubSteps() as $s) {
                $sewingSteps[$s['key']] = $s['label'];
            }
            $sewingSteps['qc_phase_1'] = 'QC Phase 1';
            $sewingSteps['iron'] = 'Iron';
            $sewingSteps['qc_phase_2'] = 'QC Phase 2';
        } else {
            $sewingSteps = [
                'fold' => 'Fold',
                'lock' => 'Lock',
                'down' => 'Down',
                'neck_join' => 'Neck Join',
                'neck_finishing' => 'Neck Finishing',
                'qc_phase_1' => 'QC Phase 1',
                'iron' => 'Iron',
                'qc_phase_2' => 'QC Phase 2',
            ];
        }

        $stepTotals = [];
        foreach ($sewingSteps as $stepKey => $stepLabel) {
            $colDozens = $stepKey . '_dozens';
            $colPieces = $stepKey . '_pieces';
            $totalPieces = \App\Support\Decimal::sum(
                $sewingRows->map(function ($r) use ($stepKey, $colDozens, $colPieces) {
                    if (isset($r->stage_data['total_pieces']) && ($r->stage_data['step'] ?? null) === $stepKey) {
                        return (string) $r->stage_data['total_pieces'];
                    }
                    if ($r->job?->current_step === $stepKey && isset($r->stage_data['dozens'])) {
                        return (string) \App\Support\Decimal::of($r->stage_data['dozens'])->multipliedBy(12)->plus($r->stage_data['pieces'] ?? 0);
                    }
                    if (isset($r->{$colDozens})) {
                        return (string) \App\Support\Decimal::of($r->{$colDozens})->multipliedBy(12)->plus($r->{$colPieces} ?? 0);
                    }
                    return '0';
                })
            );
            $totalDozens = \App\Support\CuttingQuantity::dozens($totalPieces);
            $stepTotals[$stepKey] = [
                'pieces' => $totalPieces,
                'dozens' => $totalDozens,
            ];
        }

        $totalAltered = (int) \App\Support\Decimal::sum($sewingRows->map(fn($r) => (int) $r->qc_phase_1_altered_pieces + (int) $r->qc_phase_2_altered_pieces));
        $totalRejected = (int) \App\Support\Decimal::sum($sewingRows->map(fn($r) => (int) $r->rejected_pieces));

        $qc2DirectPassed = (int) ($stepTotals['qc_phase_2']['pieces'] ?? 0);
        $finishedPieces = (string) $qc2DirectPassed;
        $finishedDozens = \App\Support\CuttingQuantity::dozens($finishedPieces);
    @endphp

    @if($sewingRows->isEmpty())
        <div class="p-10 text-center text-muted">
            No sewing operations recorded for this lot yet.
            <div class="mt-3">
                <a class="btn btn-secondary text-xs" href="{{ route('sewing-jobs.index', ['lot_id' => $lot->id]) }}">Go to Sewing &rarr;</a>
            </div>
        </div>
    @else
        <div class="grid gap-3 p-6 grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 border-b border-line">
            @foreach($sewingSteps as $stepKey => $stepLabel)
                <div class="rounded-xl border border-line bg-raised/40 p-4">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-muted">{{ $stepLabel }}</span>
                    <div class="text-lg font-bold text-ink mt-1">
                        {{ \App\Support\Decimal::display($stepTotals[$stepKey]['pieces'], 0, true) }} <span class="text-xs font-normal text-muted">pcs</span>
                    </div>
                    <div class="text-xs text-muted mt-0.5">
                        {{ $stepTotals[$stepKey]['dozens'] }} doz
                    </div>
                </div>
            @endforeach
        </div>

        <div class="p-6 bg-surface/50 border-b border-line flex flex-wrap items-center justify-between gap-4 text-sm">
            <div class="flex flex-wrap items-center gap-3">
                <div>
                    <span class="font-semibold text-ink">Finished Garments (QC 2 Passed):</span>
                    <span class="font-bold text-accent ml-1">{{ \App\Support\Decimal::display($finishedPieces, 0, true) }} pcs</span>
                    <span class="text-muted text-xs">({{ $finishedDozens }} dozen)</span>
                </div>
                @if($totalAltered > 0)
                    <span class="text-blue-700 font-semibold">&bull; {{ \App\Support\Decimal::display($totalAltered, 0, true) }} pcs altered</span>
                @endif
                @if($totalRejected > 0)
                    <span class="text-rose-700 font-semibold">&bull; {{ \App\Support\Decimal::display($totalRejected, 0, true) }} pcs rejected</span>
                @endif
            </div>
            <div class="text-xs text-muted">
                {{ $sewingRows->pluck('sewing_job_id')->unique()->count() }} sewing execution jobs logged
            </div>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Station</th>
                        <th>Operator (Staff)</th>
                        <th>Batch / Color / DIA</th>
                        <th class="number">Completed / Passed</th>
                        <th class="number">Alter / Reject Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sewingRows->sortByDesc(fn($r) => $r->job?->sewing_date?->timestamp ?? 0) as $row)
                        @php
                            $stepName = $row->job?->current_step ?? 'fold';
                            $rowCompletedPieces = (string) ($row->stage_data['total_pieces'] ?? (isset($row->{$stepName . '_dozens'}) ? \App\Support\Decimal::of($row->{$stepName . '_dozens'})->multipliedBy(12)->plus($row->{$stepName . '_pieces'} ?? 0) : '0'));
                            $rowCompletedDozens = $row->stage_data['dozens'] ?? $row->{$stepName . '_dozens'} ?? 0;
                            $rowCompletedPiecesRemainder = $row->stage_data['pieces'] ?? $row->{$stepName . '_pieces'} ?? 0;
                        @endphp
                        <tr>
                            <td>{{ $row->job?->sewing_date?->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                <span class="pill font-medium uppercase text-xs">
                                    {{ str_replace('_', ' ', $stepName) }}
                                </span>
                            </td>
                            <td class="font-medium text-ink">{{ $row->job?->staff?->name ?? '—' }}</td>
                            <td>
                                <strong>{{ $row->color }}</strong> · DIA {{ $row->dia }}
                                @if($row->sourceBodyRow || $row->sourceNeckRow)
                                    <span class="hint block text-[11px]">Body #{{ $row->source_body_row_id }} · Neck #{{ $row->source_neck_row_id }}</span>
                                @endif
                            </td>
                            <td class="number font-semibold">
                                {{ $rowCompletedDozens }} doz {{ $rowCompletedPiecesRemainder }} pc
                                <span class="hint block text-[11px]">({{ \App\Support\Decimal::display($rowCompletedPieces, 0, true) }} pcs)</span>
                            </td>
                            <td class="number text-xs">
                                @if(in_array($stepName, ['qc_phase_1', 'qc_phase_2'], true))
                                    <div class="flex flex-col items-end gap-0.5">
                                        @if($row->{$stepName.'_altered_pieces'} > 0)
                                            <span class="text-blue-700 font-medium">Altered: {{ $row->{$stepName.'_altered_pieces'} }} pcs</span>
                                        @endif
                                        @if($row->{$stepName.'_rejected_pieces'} > 0)
                                            <span class="text-rose-700 font-semibold">Rejected: {{ $row->{$stepName.'_rejected_pieces'} }} pcs</span>
                                        @endif
                                        @if(!$row->{$stepName.'_altered_pieces'} && !$row->{$stepName.'_rejected_pieces'})
                                            <span class="text-emerald-700 font-medium">100% Passed</span>
                                        @endif
                                    </div>
                                @elseif($stepName === 'neck_finishing' && $row->rejected_pieces > 0)
                                    <span class="text-rose-700 font-semibold">{{ $row->rejected_pieces }} pcs rejected</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($row->job)
                                    <a class="link text-xs" href="{{ route('sewing-jobs.show', $row->job) }}">View job &nearr;</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

<section class="panel mb-6" id="packing-section">
    <div class="panel-head">
        <div>
            <h2>Packing & Finished Goods</h2>
            <span class="hint">Poly bag, stickers, packets (6 pcs/pkt), and cartons</span>
        </div>
        @if($lot->packingRows->isNotEmpty())
            <a class="btn btn-secondary text-xs" href="{{ route('packing-jobs.create') }}">+ Record packing</a>
        @endif
    </div>

    @php
        $packingRows = $lot->packingRows;
        $packedPieces = (string) $packingRows->sum('total_pieces');
        $packedPackets = $packingRows->sum('packets');
        $packedDozens = \App\Support\Decimal::scale(\App\Support\Decimal::sum($packingRows->pluck('dozens')));
        $packingRejects = $packingRows->sum('rejected_pieces');
    @endphp

    @if($packingRows->isEmpty())
        <div class="p-10 text-center text-muted">
            No packing operations recorded for this lot yet.
            <div class="mt-3">
                <a class="btn btn-secondary text-xs" href="{{ route('packing-jobs.create') }}">Go to Packing &rarr;</a>
            </div>
        </div>
    @else
        <div class="grid gap-3 p-6 grid-cols-2 sm:grid-cols-4 border-b border-line bg-[#fafafc]">
            <div class="rounded-xl border border-line bg-surface p-4">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-muted">Packed Pieces</span>
                <div class="text-xl font-bold text-ink mt-1">
                    {{ \App\Support\Decimal::display($packedPieces, 0, true) }} <span class="text-xs font-normal text-muted">pcs</span>
                </div>
            </div>
            <div class="rounded-xl border border-line bg-surface p-4">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-muted">Packets (6 pcs/pkt)</span>
                <div class="text-xl font-bold text-accent mt-1">
                    {{ number_format($packedPackets) }} <span class="text-xs font-normal text-muted">pkts</span>
                </div>
            </div>
            <div class="rounded-xl border border-line bg-surface p-4">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-muted">Dozens</span>
                <div class="text-xl font-bold text-ink mt-1">
                    {{ $packedDozens }} <span class="text-xs font-normal text-muted">doz</span>
                </div>
            </div>
            <div class="rounded-xl border border-line bg-surface p-4">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-muted">Rejects / Damage</span>
                <div class="text-xl font-bold text-rose-700 mt-1">
                    {{ number_format($packingRejects) }} <span class="text-xs font-normal text-muted">pcs</span>
                </div>
            </div>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Color</th>
                        <th>DIA</th>
                        <th>Size</th>
                        <th>Operator</th>
                        <th class="number">Packets</th>
                        <th class="number">Dozens + Pcs</th>
                        <th class="number font-bold">Total Pcs</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($packingRows->sortByDesc(fn($r) => $r->job?->packing_date?->timestamp ?? 0) as $row)
                        <tr>
                            <td>{{ $row->job?->packing_date?->format('d/m/Y') ?? '—' }}</td>
                            <td><strong>{{ $row->color }}</strong></td>
                            <td><span class="pill">DIA {{ $row->dia }}</span></td>
                            <td><span class="font-bold text-xs uppercase bg-[#eee] px-2 py-0.5 rounded">{{ $row->size ?: '—' }}</span></td>
                            <td>{{ $row->job?->staff?->name ?? '—' }}</td>
                            <td class="number font-semibold text-accent">{{ number_format($row->packets) }} pkts</td>
                            <td class="number">{{ (int)$row->dozens }} doz {{ $row->pieces ? '+ '.$row->pieces.' pcs' : '' }}</td>
                            <td class="number font-bold">{{ number_format($row->total_pieces) }} pcs</td>
                            <td>
                                @if($row->job)
                                    <a class="link text-xs" href="{{ route('packing-jobs.show', $row->job) }}">View job &nearr;</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
</x-app-layout>
