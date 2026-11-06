@props(['job'])
@php
    $grand = \App\Support\JobTotals::dyeing($job->colorBlocks->flatMap->rows);
@endphp
<div class="panel dyeing-spreadsheet">
    <div class="panel-head">
        <div>
            <h2>Dyeing allocation and returned rolls</h2>
            <p class="hint mt-1">One row per color and DIA · all weights in kg</p>
        </div>
        <span class="hint">{{ $job->colorBlocks->count() }} colors</span>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>COLOR</th>
                    <th>DIA</th>
                    <th class="number">GIVEN KG</th>
                    <th class="number">RETURNED KG</th>
                    <th class="number">WASTE</th>
                    <th>ROLL NUMBERS</th>
                    <th>ROLL WEIGHTS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($job->colorBlocks as $blockIndex => $block)
                    @php $totals = \App\Support\JobTotals::dyeing($block->rows); @endphp
                    <tr class="dyeing-spreadsheet-group {{ $blockIndex % 2 === 0 ? 'dyeing-spreadsheet-group-even' : 'dyeing-spreadsheet-group-odd' }}">
                        <th colspan="7">
                            <span class="dyeing-spreadsheet-color-dot" style="background:{{ $block->color->hex ?? '#cbd9b6' }}"></span>
                            <span>Color {{ $blockIndex + 1 }} · {{ $block->color->name }}</span>
                        </th>
                    </tr>
                    @foreach($block->rows as $rowIndex => $row)
                        @php $rolls = collect($row->rolls ?? []); @endphp
                        <tr class="{{ $rowIndex % 2 === 1 ? 'row-even' : '' }}">
                            <td class="dyeing-spreadsheet-color">{{ $block->color->name }}</td>
                            <td><span class="pill">{{ $row->dia }}</span></td>
                            <td class="number">{{ $row->given_fabric_kg }}</td>
                            <td class="number">{{ $row->returned_fabric_kg ?? '—' }}</td>
                            <td class="number">{{ $row->waste_percent === null ? '—' : $row->waste_percent.'%' }}</td>
                            <td>
                                @if($rolls->isNotEmpty())
                                    <div class="dyeing-spreadsheet-lines">@foreach($rolls as $roll)<span>{{ $roll['number'] }}</span>@endforeach</div>
                                @else
                                    <span class="hint">Not recorded</span>
                                @endif
                            </td>
                            <td>
                                @if($rolls->isNotEmpty())
                                    <div class="dyeing-spreadsheet-lines">@foreach($rolls as $roll)<span>{{ $roll['weight'] }} {{ $roll['unit'] }} ({{ $roll['weight_kg'] }} kg)</span>@endforeach</div>
                                @else
                                    <span class="hint">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    <tr class="dyeing-spreadsheet-subtotal">
                        <td colspan="2">Subtotal · {{ $block->color->name }}</td>
                        <td class="number">{{ $totals['given'] }}</td>
                        <td class="number">{{ $totals['returned'] ?? '—' }}</td>
                        <td class="number">{{ $totals['waste'] === null ? '—' : $totals['waste'].'%' }}</td>
                        <td colspan="2"></td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">Grand total</td>
                    <td class="number">{{ $grand['given'] }}</td>
                    <td class="number">{{ $grand['returned'] === null ? '—' : $grand['returned'] }}</td>
                    <td class="number">{{ $grand['waste'] === null ? '—' : $grand['waste'].'%' }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
