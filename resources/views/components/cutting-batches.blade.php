@props(['rows', 'compact' => false])
@if($compact)<details class="cutting-batch-details"><summary class="link">{{ $rows->pluck('lot.lot_number')->unique()->join(', ') }} <span class="hint">· View color / DIA / dozens</span></summary>@endif
<div class="table-wrap cutting-spreadsheet {{ $compact ? 'mt-3' : '' }}">
    <table class="data-table">
        <thead><tr><th>LOT</th><th>COLOR</th><th>DIA</th><th class="number">BATCHES</th><th class="number">QUANTITY</th><th class="number">PIECES</th><th>STATUS</th></tr></thead>
        <tbody>
        @forelse($rows->groupBy(fn ($row) => $row->lot_id.'|'.$row->color.'|'.$row->dia) as $group)
            @php
                $first = $group->first();
                $completed = $group->whereNotNull('cut_weight_kg');
                $pieces = \App\Support\Decimal::sum($group->map(fn ($row) => \App\Support\CuttingQuantity::pieces($row)));
                $quantity = \App\Support\CuttingQuantity::dozens($pieces);
            @endphp
            <tr>
                <td><a class="link" href="{{ route('lots.show', $first->lot_id) }}">{{ $first->lot->lot_number }}</a></td>
                <td><span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full border border-line" style="background-color: {{ $first->roll->dyeingRow->colorBlock->color->hex }}"></span>{{ $first->color }}</span></td>
                <td><span class="pill">{{ $first->dia }}</span></td>
                <td class="number">{{ $group->count() }}</td>
                <td class="number">@if($group->whereNotNull('dozens')->isNotEmpty()){{ $first->cutting_type === 'neck' ? \App\Support\CuttingQuantity::display($pieces) : $quantity.' dozen' }}@else—@endif</td>
                <td class="number">{{ \App\Support\Decimal::display($pieces, 3, true) }}</td>
                <td>@if($group->count() !== $completed->count())<span class="hint">{{ $group->count() - $completed->count() }} pending</span>@else<span class="cutting-batch-status">Recorded</span>@endif</td>
            </tr>
        @empty
            <tr><td colspan="7" class="hint">No batches in this section.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@if($compact)</details>@endif
