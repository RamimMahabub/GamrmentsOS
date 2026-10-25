@props(['rows', 'compact' => false])
@if($compact)<details class="cutting-batch-details"><summary class="link">{{ $rows->pluck('lot.lot_number')->unique()->join(', ') }} <span class="hint">· View color / DIA / dozens</span></summary>@endif
<div class="cutting-batches {{ $compact ? 'mt-3' : '' }}">
@forelse($rows->groupBy('lot_id') as $lotRows)
    <section class="cutting-lot">
        <div class="cutting-lot-title"><div><p class="eyebrow">Lot origin</p><a class="link" href="{{ route('lots.show', $lotRows->first()->lot_id) }}">{{ $lotRows->first()->lot->lot_number }}</a></div><span class="pill">{{ $lotRows->count() }} batches</span></div>
        @foreach($lotRows->groupBy('color') as $color => $colorRows)
        <div class="cutting-color"><h3><span class="h-3 w-3 rounded-full border border-line" style="background-color: {{ $colorRows->first()->roll->dyeingRow->colorBlock->color->hex }}"></span>{{ $color }}</h3><div class="cutting-dia-grid">
            @foreach($colorRows->groupBy('dia')->sortKeys() as $dia => $diaRows)
            @php $completed = $diaRows->whereNotNull('cut_weight_kg'); $batchPieces = \App\Support\Decimal::sum($diaRows->map(fn($row) => \App\Support\CuttingQuantity::pieces($row))); $batchDozens = \App\Support\CuttingQuantity::dozens($batchPieces); @endphp
            <div class="cutting-dia-card"><span class="eyebrow">DIA {{ $dia }}</span>@if($diaRows->whereNotNull('dozens')->isNotEmpty())<p class="cutting-batch-quantity">@if($diaRows->first()->cutting_type === 'neck'){{ \App\Support\CuttingQuantity::display($batchPieces) }}@else{{ $batchDozens }} <span>dozen</span>@endif</p><p class="hint">{{ \App\Support\Decimal::display($batchPieces, 3, true) }} pieces</p>@else<p class="my-2 font-semibold">Dozens pending</p>@endif
                @if($diaRows->count() !== $completed->count())<span class="hint">{{ $diaRows->count() - $completed->count() }} awaiting results</span>@else<span class="cutting-batch-status">Results recorded</span>@endif
                @foreach($diaRows->whereNotNull('source_body_row_id')->pluck('sourceBodyRow.cutting_job_id')->filter()->unique() as $bodyJobId)<a class="link block text-xs" href="{{ route('cutting.body.show', $bodyJobId) }}">From body #{{ $bodyJobId }} → neck</a>@endforeach
            </div>
            @endforeach
        </div></div>
        @endforeach
    </section>
@empty
    <p class="hint p-6">No batches in this section.</p>
@endforelse
</div>
@if($compact)</details>@endif
