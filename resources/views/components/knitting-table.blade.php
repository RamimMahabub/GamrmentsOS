@props(['job'])
@php 
$totals=\App\Support\JobTotals::knitting($job); 
$totalLoss = \App\Support\Decimal::scale(\App\Support\Decimal::of($totals['input'])->minus($totals['output']));
@endphp
<div class="panel"><div class="panel-head"><h2>Knitting output by DIA</h2><span class="hint">Weights shown in both kg and lb</span></div><div class="table-wrap"><table class="data-table"><thead><tr><th>DIA</th>@foreach($job->yarnLinks as $link)<th class="number">{{ strtoupper($link->yarn->name) }} INPUT</th>@endforeach<th class="number">TOTAL INPUT</th><th class="number">FABRIC OUTPUT</th><th class="number">WASTE</th></tr></thead><tbody>
@foreach($job->diaRows as $rowIndex => $row)
@php
$rowLoss = \App\Support\Decimal::scale(\App\Support\Decimal::of($row->input_kg)->minus($row->output_fabric_kg));
@endphp
<tr class="{{ $rowIndex % 2 === 1 ? 'row-even' : '' }}"><td><span class="pill">{{ $row->dia }}</span></td>@foreach($job->yarnLinks as $link)<td class="number"><x-weight-display :kg="$row->yarnLines->firstWhere('yarn_id',$link->yarn_id)?->quantity_kg ?? '0.000'"/></td>@endforeach<td class="number"><x-weight-display :kg="$row->input_kg"/></td><td class="number font-medium"><x-weight-display :kg="$row->output_fabric_kg"/></td><td class="number"><span>{{ $row->waste_percent }}%</span><span class="block text-[11px] text-muted">({{ \App\Support\Decimal::formatWeight($rowLoss) }})</span></td></tr>@endforeach
</tbody><tfoot><tr><td colspan="{{ $job->yarnLinks->count()+1 }}">Grand total</td><td class="number"><x-weight-display :kg="$totals['input']"/></td><td class="number"><x-weight-display :kg="$totals['output']"/></td><td class="number"><span>{{ $totals['waste']===null ? '—' : $totals['waste'].'%' }}</span><span class="block text-[11px] text-muted">({{ \App\Support\Decimal::formatWeight($totalLoss) }})</span></td></tr></tfoot></table></div></div>
