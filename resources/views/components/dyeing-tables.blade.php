@props(['job'])
<div class="space-y-5">
@foreach($job->colorBlocks as $block)
@php $totals=\App\Support\JobTotals::dyeing($block->rows); @endphp
<section class="panel"><div class="panel-head"><h2 class="flex items-center gap-3"><span class="h-3 w-3 rounded-full border border-line" style="background:{{ $block->color->hex ?? '#cbd9b6' }}"></span>{{ $block->color->name }}</h2><span class="hint">All weights in kg</span></div><div class="table-wrap"><table class="data-table"><thead><tr><th>DIA</th><th class="number">GIVEN FABRIC</th><th class="number">RETURNED FABRIC</th><th class="number">WASTE</th></tr></thead><tbody>
@foreach($block->rows as $row)<tr><td><span class="pill">{{ $row->dia }}</span></td><td class="number">{{ $row->given_fabric_kg }}</td><td class="number">{{ $row->returned_fabric_kg ?? '—' }}</td><td class="number">{{ $row->waste_percent===null ? '—' : $row->waste_percent.'%' }}</td></tr>@endforeach
</tbody><tfoot><tr><td>Color subtotal</td><td class="number">{{ $totals['given'] }}</td><td class="number">{{ $totals['returned'] ?? '—' }}</td><td class="number">{{ $totals['waste']===null ? '—' : $totals['waste'].'%' }}</td></tr></tfoot></table></div>
<div class="space-y-4 p-6"><h3 class="font-semibold">Returned rolls for cutting</h3>
@foreach($block->rows as $row)
@if($row->rolls)
<div><p class="mb-2 font-medium">DIA {{ $row->dia }} &middot; {{ count($row->rolls) }} rolls</p><div class="table-wrap"><table class="data-table"><thead><tr><th>ROLL NUMBER</th><th>COLOR</th><th class="number">WEIGHT</th><th class="number">WEIGHT, KG</th></tr></thead><tbody>
@foreach($row->rolls as $roll)<tr><td>{{ $roll['number'] }}</td><td>{{ $block->color->name }}</td><td class="number">{{ $roll['weight'] }} {{ $roll['unit'] }}</td><td class="number">{{ $roll['weight_kg'] }}</td></tr>@endforeach
</tbody></table></div></div>
@elseif($row->returned_fabric_kg !== null)
<p class="hint">DIA {{ $row->dia }}: individual rolls have not been recorded.</p>
@endif
@endforeach</div></section>
@endforeach
@php $grand=\App\Support\JobTotals::dyeing($job->colorBlocks->flatMap->rows); @endphp
<div class="grid gap-5 rounded-xl border border-accent-line bg-accent-soft p-6 sm:grid-cols-3"><div><div class="hint">TOTAL GIVEN</div><h2 class="mt-2">{{ $grand['given'] }} kg</h2></div><div><div class="hint">TOTAL RETURNED</div><h2 class="mt-2">{{ $grand['returned']===null ? '—' : $grand['returned'].' kg' }}</h2></div><div><div class="hint">OVERALL WASTE</div><h2 class="mt-2">{{ $grand['waste']===null ? 'Pending returns' : $grand['waste'].'%' }}</h2></div></div>
</div>
