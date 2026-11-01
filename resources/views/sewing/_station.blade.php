<form method="POST" action="{{ route('sewing-jobs.store') }}" x-data="sewingForm(@js(['available' => $available, 'rows' => $rows, 'step' => $step]))" class="sewing-inline-form">
    @csrf
    <input type="hidden" name="current_step" value="{{ $step }}">
    <div class="sewing-inline-meta">
        <label class="field">Date<input type="date" required name="sewing_date" value="{{ now()->toDateString() }}"></label>
        <label class="field">Staff<select required name="staff_id"><option value="">Choose staff</option>@foreach($staffs as $staff)<option value="{{ $staff->id }}">{{ $staff->name }}</option>@endforeach</select></label>
        <label class="field sewing-inline-notes">Notes<input type="text" name="notes" placeholder="Optional handoff note"></label>
    </div>
    <section class="panel sewing-stage-panel">
        <div class="sewing-stage-header"><div class="sewing-stage-icon">→</div><div><div class="sewing-eyebrow">Current station</div><h2>{{ strtoupper(str_replace('_', ' ', $step)) }}</h2><p class="hint">Select batches and enter the quantity completed at this station.</p></div></div>
        <div class="table-wrap"><table class="data-table sewing-stage-table"><thead><tr><th class="sewing-select-column">Select</th><th>Batch</th><th>DIA</th><th class="number">Available</th><th class="number">Release quantity<br><small>Dozen + pieces</small></th>@if($step === 'neck_finishing')<th class="number">Rejected</th>@endif</tr></thead><tbody>
            <template x-for="sourceItem in available" :key="sourceItem.group_key"><tr :class="selected(sourceItem) ? 'sewing-source-selected' : ''"><td class="sewing-select-column"><input type="checkbox" class="sewing-row-checkbox" :checked="selected(sourceItem)" @change="toggle(sourceItem)"><template x-if="selected(sourceItem)"><input type="hidden" :name="`rows[${rows.indexOf(selectedRow(sourceItem))}][group_key]`" x-model="selectedRow(sourceItem).group_key"></template></td><td><div class="sewing-inline-batch-color" x-text="sourceItem.color"></div><strong x-text="sourceItem.lot"></strong></td><td><span class="pill" x-text="'DIA ' + sourceItem.dia"></span></td><td class="number"><div class="sewing-available-quantity"><strong x-text="sourceItem.dozens + ' dozen'"></strong><span x-text="sourceItem.pieces + ' pieces'"></span></div></td><td class="number"><template x-if="selected(sourceItem)"><div class="sewing-compact-quantity"><label><span>Dozen</span><input class="sewing-stage-quantity-input" type="number" required min="0" step="1" :name="`rows[${rows.indexOf(selectedRow(sourceItem))}][{{ $step }}_dozens]`" x-model="selectedRow(sourceItem).{{ $step }}_dozens"></label><b>+</b><label><span>Pieces</span><input class="sewing-stage-quantity-input" type="number" required min="0" max="11" step="1" :name="`rows[${rows.indexOf(selectedRow(sourceItem))}][{{ $step }}_pieces]`" x-model="selectedRow(sourceItem).{{ $step }}_pieces"></label></div></template><span x-show="!selected(sourceItem)" class="hint">Select batch</span></td>@if($step === 'neck_finishing')<td class="number"><template x-if="selected(sourceItem)"><input class="sewing-stage-quantity-input" type="number" required min="0" step="1" :name="`rows[${rows.indexOf(selectedRow(sourceItem))}][rejected_pieces]`" x-model="selectedRow(sourceItem).rejected_pieces"></template></td>@endif</tr></template>
            <tr x-show="!available.length"><td colspan="{{ $step === 'neck_finishing' ? 6 : 5 }}" class="empty">No batches are waiting at this station.</td></tr>
        </tbody></table></div>
        <div class="flex justify-end p-5"><button class="btn btn-primary" :disabled="!rows.length || rows.some(row => invalid(row))">Save and release to next station</button></div>
    </section>
    <section class="panel sewing-station-history">
        <div class="panel-head"><div><h2>Previous {{ strtoupper(str_replace('_', ' ', $step)) }} jobs</h2><p class="hint mt-1">Saved handoffs already completed at this station.</p></div><span class="pill">{{ $stationJobs->count() }} jobs</span></div>
        <div class="table-wrap"><table class="data-table"><thead><tr><th>Date</th><th>Staff</th><th>Batches</th><th class="number">Completed</th><th class="number">Rejected pieces</th><th></th></tr></thead><tbody>
            @forelse($stationJobs as $stationJob)
                @php($completedPieces = \App\Support\Decimal::sum($stationJob->rows->map(fn ($row): string => (string) \App\Support\Decimal::of($row->{$step.'_dozens'})->multipliedBy(12)->plus($row->{$step.'_pieces'}))))
                <tr><td>{{ $stationJob->sewing_date->format('d M Y') }}</td><td>{{ $stationJob->staff->name }}</td><td>{{ $stationJob->rows->count() }} batches<br><span class="hint">{{ $stationJob->rows->pluck('lot.lot_number')->unique()->join(', ') }}</span></td><td class="number font-semibold">{{ \App\Support\Decimal::display($completedPieces, 0, true) }} pcs</td><td class="number">{{ \App\Support\Decimal::display(\App\Support\Decimal::sum($stationJob->rows->map(fn ($row): string => (string) $row->rejected_pieces)), 0, true) }}</td><td><a class="link" href="{{ route('sewing-jobs.show', $stationJob) }}">View</a></td></tr>
            @empty
                <tr><td colspan="6" class="empty">No previous {{ strtoupper(str_replace('_', ' ', $step)) }} jobs.</td></tr>
            @endforelse
        </tbody></table></div>
    </section>
</form>
