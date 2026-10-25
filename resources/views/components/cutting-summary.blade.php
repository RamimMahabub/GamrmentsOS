@props(['rows', 'type' => 'body'])
@php $groups=\App\Support\CuttingTotals::grouped($rows); $groups['Grand total']=\App\Support\CuttingTotals::sum($rows); @endphp
<div class="table-wrap"><table class="data-table"><thead><tr><th>Lot / Color / DIA</th>@foreach(config('cutting.labels') as $key=>$label)<th class="number">{{ $type === 'neck' && $key === 'roll_weight_kg' ? 'Body input weight' : $label }}{{ str_ends_with($key,'_kg') ? ', kg' : '' }}</th>@endforeach</tr></thead><tbody>
@foreach($groups as $label=>$totals)<tr><td>{{ $label }}@if($totals['pending_count'])<p class="hint">{{ $totals['pending_count'] }} rolls awaiting results ({{ $totals['pending_weight_kg'] }} kg)</p>@endif</td>@foreach(config('cutting.labels') as $key=>$text)<td class="number">{{ $totals['completed_count'] ? $totals[$key] : '-' }}</td>@endforeach</tr>@endforeach
</tbody></table></div>
