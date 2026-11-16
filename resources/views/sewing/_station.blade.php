<form method="POST" action="{{ route('sewing-jobs.store') }}" x-data="sewingForm(@js(['available' => $available, 'rows' => $rows, 'step' => $step]))" class="sewing-inline-form">
    @csrf
    <input type="hidden" name="current_step" value="{{ $step }}">
    <input type="hidden" name="workflow" :value="workflow">
    <div class="sewing-inline-meta">
        <label class="field">Date<input type="date" required name="sewing_date" value="{{ now()->toDateString() }}"></label>
        <label class="field">Staff<select required name="staff_id"><option value="">Choose staff</option>@foreach($staffs as $staff)<option value="{{ $staff->id }}">{{ $staff->name }}</option>@endforeach</select></label>
        <label class="field sewing-inline-notes">Notes<input type="text" name="notes" placeholder="Optional handoff note"></label>
    </div>
    <section class="panel sewing-stage-panel">
        <div class="sewing-stage-header">
            <div class="sewing-stage-icon">
                @if(in_array($step, ['qc_phase_1', 'qc_phase_2'], true))
                    <x-icon name="{{ $step === 'qc_phase_1' ? 'check' : 'shield' }}" class="h-4 w-4"/>
                @elseif($step === 'iron')
                    <x-icon name="sparkles" class="h-4 w-4"/>
                @else
                    &rarr;
                @endif
            </div>
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-ink">{{ strtoupper(str_replace('_', ' ', $step)) }}</h2>
                <p class="hint text-[11px]">
                    @if(in_array($step, ['qc_phase_1', 'qc_phase_2'], true))
                        Select batches and record passed garments, altered count, and rejected count.
                    @elseif($step === 'iron')
                        Steam iron all passed garments from QC Phase 1.
                    @else
                        Select batches and enter the quantity completed at this station.
                    @endif
                </p>
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line px-3.5 py-1.5 bg-[#fafafc]">
            <div class="flex flex-wrap items-center gap-1.5">
                <input type="search" class="text-xs py-1 px-2 rounded-lg border border-line bg-surface focus:border-accent w-36 sm:w-44" placeholder="Search lot, color, DIA..." x-model="search">
                <select class="text-xs font-medium py-1 px-2 rounded-lg border border-line bg-surface text-ink focus:border-accent cursor-pointer min-w-[6.5rem]" x-model="lotFilter">
                    <option value="">All lots</option>
                    <template x-for="l in lots" :key="l">
                        <option :value="l" x-text="l"></option>
                    </template>
                </select>
                <select class="text-xs font-medium py-1 px-2 rounded-lg border border-line bg-surface text-ink focus:border-accent cursor-pointer min-w-[5.5rem]" x-model="diaFilter">
                    <option value="">All DIA</option>
                    <template x-for="d in dias" :key="d">
                        <option :value="d" x-text="'DIA ' + d"></option>
                    </template>
                </select>
                <select class="text-xs font-medium py-1 px-2 rounded-lg border border-line bg-surface text-ink focus:border-accent cursor-pointer min-w-[6.5rem]" x-model="colorFilter">
                    <option value="">All colors</option>
                    <template x-for="c in colors" :key="c">
                        <option :value="c" x-text="c"></option>
                    </template>
                </select>
                <button type="button" x-show="search || lotFilter || diaFilter || colorFilter" @click="search = ''; lotFilter = ''; diaFilter = ''; colorFilter = '';" class="text-[11px] text-muted hover:text-accent font-medium px-1">Clear</button>
            </div>
            <div class="text-[11px] text-muted font-medium">
                <span x-text="filteredAvailable.length"></span> of <span x-text="available.length"></span> batches
                <span x-show="rows.length" class="ml-1 text-accent font-semibold" x-text="'(' + rows.length + ' sel)'"></span>
            </div>
        </div>
        <div class="table-wrap">
            <table class="data-table sewing-stage-table">
                <thead>
                    <tr>
                        <th class="sewing-select-column" style="width: 3.5rem; text-align: center;">
                            <label class="flex items-center justify-center cursor-pointer" title="Select all visible">
                                <input type="checkbox" class="sewing-row-checkbox" :checked="allSelected(filteredAvailable)" x-effect="$el.indeterminate = partiallySelected(filteredAvailable)" :disabled="!filteredAvailable.length" @change="toggleAll(filteredAvailable, $event.target.checked)">
                            </label>
                        </th>
                        <th style="{{ in_array($step, ['qc_phase_1', 'qc_phase_2'], true) ? 'width: 17%;' : ($step === 'neck_finishing' ? 'width: 22%;' : 'width: 25%;') }} text-align: left;">Batch</th>
                        <th style="{{ in_array($step, ['qc_phase_1', 'qc_phase_2'], true) ? 'width: 10%;' : ($step === 'neck_finishing' ? 'width: 12%;' : 'width: 14%;') }} text-align: left;">DIA</th>
                        <th class="number" style="{{ in_array($step, ['qc_phase_1', 'qc_phase_2'], true) ? 'width: 18%;' : ($step === 'neck_finishing' ? 'width: 22%;' : 'width: 25%;') }} text-align: right;">Available</th>
                        @if(in_array($step, ['qc_phase_1', 'qc_phase_2'], true))
                            <th style="width: 27%; text-align: center;">Passed<br><small>Dozen + pieces</small></th>
                            <th class="text-blue-700" style="width: 14%; text-align: center;">Altered<br><small>pieces</small></th>
                            <th class="text-rose-700" style="width: 14%; text-align: center;">Rejected<br><small>pieces</small></th>
                        @elseif($step === 'iron')
                            <th style="width: 36%; text-align: center;">Ironed quantity<br><small>Dozen + pieces</small></th>
                        @else
                            <th style="{{ $step === 'neck_finishing' ? 'width: 28%;' : 'width: 36%;' }} text-align: center;">Release quantity<br><small>Dozen + pieces</small></th>
                            @if($step === 'neck_finishing')
                                <th class="text-rose-700" style="width: 16%; text-align: center;">Rejected<br><small>pieces</small></th>
                            @endif
                        @endif
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(sourceItem, sIdx) in filteredAvailable" :key="sourceItem.group_key">
                        <tr :class="(selected(sourceItem) ? 'sewing-source-selected' : '') + (sIdx % 2 === 1 ? ' row-even bg-[#f7f9fc]' : ' bg-white')">
                            <td class="sewing-select-column" style="width: 3.5rem; text-align: center;">
                                <input type="checkbox" class="sewing-row-checkbox" :checked="selected(sourceItem)" @change="toggle(sourceItem)">
                                <template x-if="selected(sourceItem)">
                                    <input type="hidden" :name="`rows[${rows.indexOf(selectedRow(sourceItem))}][group_key]`" x-model="selectedRow(sourceItem).group_key">
                                </template>
                            </td>
                            <td style="text-align: left;">
                                <div class="sewing-inline-batch-color" x-text="sourceItem.color"></div>
                                <strong x-text="sourceItem.lot"></strong>
                            </td>
                            <td style="text-align: left;">
                                <span class="pill" x-text="'DIA ' + sourceItem.dia"></span>
                            </td>
                            <td class="number" style="text-align: right;">
                                <div class="sewing-available-quantity">
                                    <strong x-text="availableLabel(sourceItem)"></strong>
                                    <span class="text-[11px] text-muted font-normal block" x-text="sourceItem.total_pieces + ' pcs remaining'"></span>
                                    <template x-if="sourceItem.used_pieces > 0">
                                        <span class="text-[10px] text-muted/80 block mt-0.5 whitespace-nowrap" x-text="'(' + cutSummary(sourceItem) + ')'"></span>
                                    </template>
                                </div>
                            </td>
                            @if(in_array($step, ['qc_phase_1', 'qc_phase_2'], true))
                                <td style="text-align: center;">
                                    <template x-if="selected(sourceItem)">
                                        <div class="sewing-compact-quantity">
                                            <label><span>Dozen</span><input class="sewing-stage-quantity-input" type="number" required min="0" step="1" :name="`rows[${rows.indexOf(selectedRow(sourceItem))}][{{ $step }}_dozens]`" x-model="selectedRow(sourceItem).{{ $step }}_dozens" placeholder="0"></label>
                                            <b>+</b>
                                            <label><span>Pieces</span><input class="sewing-stage-quantity-input" type="number" required min="0" max="11" step="1" :name="`rows[${rows.indexOf(selectedRow(sourceItem))}][{{ $step }}_pieces]`" x-model="selectedRow(sourceItem).{{ $step }}_pieces" placeholder="0"></label>
                                            <input type="hidden" :name="`rows[${rows.indexOf(selectedRow(sourceItem))}][dozens]`" :value="selectedRow(sourceItem).{{ $step }}_dozens">
                                            <input type="hidden" :name="`rows[${rows.indexOf(selectedRow(sourceItem))}][pieces]`" :value="selectedRow(sourceItem).{{ $step }}_pieces">
                                        </div>
                                    </template>
                                    <span x-show="!selected(sourceItem)" class="hint" style="display: block; text-align: center;">Select batch</span>
                                </td>
                                <td style="text-align: center;">
                                    <template x-if="selected(sourceItem)">
                                        <div class="flex items-center justify-center">
                                            <input class="sewing-stage-quantity-input w-20 text-center font-semibold text-blue-800 border-blue-300 bg-blue-50/50 focus:border-blue-500 focus:ring-blue-500" type="number" required min="0" step="1" :name="`rows[${rows.indexOf(selectedRow(sourceItem))}][{{ $step }}_altered_pieces]`" x-model="selectedRow(sourceItem).{{ $step }}_altered_pieces" @input="syncQcPassed(selectedRow(sourceItem), sourceItem, $event)" placeholder="0" title="Altered pieces">
                                        </div>
                                    </template>
                                </td>
                                <td style="text-align: center;">
                                    <template x-if="selected(sourceItem)">
                                        <div class="flex items-center justify-center">
                                            <input class="sewing-stage-quantity-input w-20 text-center font-semibold text-rose-800 border-rose-300 bg-rose-50/50 focus:border-rose-500 focus:ring-rose-500" type="number" required min="0" step="1" :name="`rows[${rows.indexOf(selectedRow(sourceItem))}][{{ $step }}_rejected_pieces]`" x-model="selectedRow(sourceItem).{{ $step }}_rejected_pieces" @input="syncQcPassed(selectedRow(sourceItem), sourceItem, $event)" placeholder="0" title="Rejected pieces">
                                        </div>
                                    </template>
                                </td>
                            @else
                                <td style="text-align: center;">
                                    <template x-if="selected(sourceItem)">
                                        <div class="sewing-compact-quantity">
                                            <label><span>Dozen</span><input class="sewing-stage-quantity-input" type="number" required min="0" step="1" :name="`rows[${rows.indexOf(selectedRow(sourceItem))}][{{ $step }}_dozens]`" x-model="selectedRow(sourceItem).{{ $step }}_dozens" placeholder="0"></label>
                                            <b>+</b>
                                            <label><span>Pieces</span><input class="sewing-stage-quantity-input" type="number" required min="0" max="11" step="1" :name="`rows[${rows.indexOf(selectedRow(sourceItem))}][{{ $step }}_pieces]`" x-model="selectedRow(sourceItem).{{ $step }}_pieces" placeholder="0"></label>
                                            <input type="hidden" :name="`rows[${rows.indexOf(selectedRow(sourceItem))}][dozens]`" :value="selectedRow(sourceItem).{{ $step }}_dozens">
                                            <input type="hidden" :name="`rows[${rows.indexOf(selectedRow(sourceItem))}][pieces]`" :value="selectedRow(sourceItem).{{ $step }}_pieces">
                                        </div>
                                    </template>
                                    <span x-show="!selected(sourceItem)" class="hint" style="display: block; text-align: center;">Select batch</span>
                                </td>
                                @if($step === 'neck_finishing')
                                    <td style="text-align: center;">
                                        <template x-if="selected(sourceItem)">
                                            <div class="flex items-center justify-center">
                                                <input class="sewing-stage-quantity-input w-20 text-center font-semibold text-rose-800 border-rose-300 bg-rose-50/50 focus:border-rose-500 focus:ring-rose-500" type="number" required min="0" step="1" :name="`rows[${rows.indexOf(selectedRow(sourceItem))}][rejected_pieces]`" x-model="selectedRow(sourceItem).rejected_pieces" placeholder="0" aria-label="Rejected pieces">
                                            </div>
                                        </template>
                                    </td>
                                @endif
                            @endif
                        </tr>
                    </template>
                    @php
                        $cols = in_array($step, ['qc_phase_1', 'qc_phase_2'], true) ? 7 : ($step === 'neck_finishing' ? 6 : 5);
                    @endphp
                    <tr x-show="!available.length"><td colspan="{{ $cols }}" class="empty">No batches are waiting at this station.</td></tr>
                    <tr x-show="available.length && !filteredAvailable.length"><td colspan="{{ $cols }}" class="empty">No batches match the current search or filters.</td></tr>
                </tbody>
            </table>
        </div>
        <div class="flex justify-end p-5">
            <button class="btn btn-primary" :disabled="!rows.length || rows.some(row => invalid(row))">Save and release to next station</button>
        </div>
    </section>
    <section class="panel sewing-station-history">
        <div class="panel-head">
            <div>
                <h2>Previous {{ strtoupper(str_replace('_', ' ', $step)) }} jobs</h2>
                <p class="hint mt-1">Saved handoffs already completed at this station.</p>
            </div>
            <span class="pill">{{ $stationJobs->count() }} jobs</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Staff</th>
                        <th>Batches</th>
                        @if(in_array($step, ['qc_phase_1', 'qc_phase_2'], true))
                            <th class="number text-emerald-700">Passed</th>
                            <th class="number text-blue-700">Altered</th>
                            <th class="number text-rose-700">Rejected</th>
                        @elseif($step === 'iron')
                            <th class="number font-semibold">Ironed pieces</th>
                        @else
                            <th class="number">Completed</th>
                            @if($step === 'neck_finishing')
                                <th class="number">Rejected pieces</th>
                            @endif
                        @endif
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stationJobs as $stationJob)
                        @if(in_array($step, ['qc_phase_1', 'qc_phase_2'], true))
                            @php
                                $passedPieces = \App\Support\Decimal::sum($stationJob->rows->map(function ($row) use ($step): string {
                                    if (isset($row->stage_data['total_pieces']) && ($row->stage_data['step'] ?? null) === $step) {
                                        return (string) $row->stage_data['total_pieces'];
                                    }
                                    $dozens = $row->{$step.'_dozens'} ?? $row->stage_data['dozens'] ?? 0;
                                    $pieces = $row->{$step.'_pieces'} ?? $row->stage_data['pieces'] ?? 0;
                                    return (string) \App\Support\Decimal::of($dozens)->multipliedBy(12)->plus($pieces);
                                }));
                                $altered = (int) $stationJob->rows->sum("{$step}_altered_pieces");
                                $rejected = (int) $stationJob->rows->sum("{$step}_rejected_pieces");
                            @endphp
                            <tr>
                                <td>{{ $stationJob->sewing_date->format('d M Y') }}</td>
                                <td>{{ $stationJob->staff->name }}</td>
                                <td>
                                    {{ $stationJob->rows->count() }} batches<br>
                                    <span class="hint">{{ $stationJob->rows->pluck('lot.lot_number')->unique()->join(', ') }}</span>
                                </td>
                                <td class="number font-semibold text-emerald-700">{{ \App\Support\Decimal::display($passedPieces, 0, true) }} pcs</td>
                                <td class="number font-medium text-blue-700">{{ \App\Support\Decimal::display($altered, 0, true) }} pcs</td>
                                <td class="number font-medium text-rose-700">{{ \App\Support\Decimal::display($rejected, 0, true) }} pcs</td>
                                <td>
                                    <div class="flex items-center gap-2 justify-end">
                                        <a class="link text-xs" href="{{ route('sewing-jobs.edit', $stationJob) }}">Edit</a>
                                        <a class="link text-xs" href="{{ route('sewing-jobs.show', $stationJob) }}">View</a>
                                    </div>
                                </td>
                            </tr>
                        @elseif($step === 'iron')
                            @php
                                $ironedPieces = \App\Support\Decimal::sum($stationJob->rows->map(function ($row): string {
                                    if (isset($row->stage_data['total_pieces']) && ($row->stage_data['step'] ?? null) === 'iron') {
                                        return (string) $row->stage_data['total_pieces'];
                                    }
                                    $dozens = $row->iron_dozens ?? $row->stage_data['dozens'] ?? 0;
                                    $pieces = $row->iron_pieces ?? $row->stage_data['pieces'] ?? 0;
                                    return (string) \App\Support\Decimal::of($dozens)->multipliedBy(12)->plus($pieces);
                                }));
                            @endphp
                            <tr>
                                <td>{{ $stationJob->sewing_date->format('d M Y') }}</td>
                                <td>{{ $stationJob->staff->name }}</td>
                                <td>
                                    {{ $stationJob->rows->count() }} batches<br>
                                    <span class="hint">{{ $stationJob->rows->pluck('lot.lot_number')->unique()->join(', ') }}</span>
                                </td>
                                <td class="number font-semibold">{{ \App\Support\Decimal::display($ironedPieces, 0, true) }} pcs</td>
                                <td>
                                    <div class="flex items-center gap-2 justify-end">
                                        <a class="link text-xs" href="{{ route('sewing-jobs.edit', $stationJob) }}">Edit</a>
                                        <a class="link text-xs" href="{{ route('sewing-jobs.show', $stationJob) }}">View</a>
                                    </div>
                                </td>
                            </tr>
                        @else
                            @php
                                $completedPieces = \App\Support\Decimal::sum($stationJob->rows->map(function ($row) use ($step): string {
                                    if (isset($row->stage_data['total_pieces']) && ($row->stage_data['step'] ?? null) === $step) {
                                        return (string) $row->stage_data['total_pieces'];
                                    }
                                    if (isset($row->stage_data['dozens']) && ($row->stage_data['step'] ?? null) === $step) {
                                        return (string) \App\Support\Decimal::of($row->stage_data['dozens'])->multipliedBy(12)->plus($row->stage_data['pieces'] ?? 0);
                                    }
                                    $dozens = $row->{$step.'_dozens'} ?? $row->stage_data['dozens'] ?? 0;
                                    $pieces = $row->{$step.'_pieces'} ?? $row->stage_data['pieces'] ?? 0;
                                    return (string) \App\Support\Decimal::of($dozens)->multipliedBy(12)->plus($pieces);
                                }));
                            @endphp
                            <tr>
                                <td>{{ $stationJob->sewing_date->format('d M Y') }}</td>
                                <td>{{ $stationJob->staff->name }}</td>
                                <td>
                                    {{ $stationJob->rows->count() }} batches<br>
                                    <span class="hint">{{ $stationJob->rows->pluck('lot.lot_number')->unique()->join(', ') }}</span>
                                </td>
                                <td class="number font-semibold">{{ \App\Support\Decimal::display($completedPieces, 0, true) }} pcs</td>
                                @if($step === 'neck_finishing')
                                    <td class="number">{{ \App\Support\Decimal::display(\App\Support\Decimal::sum($stationJob->rows->map(fn ($row): string => (string) $row->rejected_pieces)), 0, true) }}</td>
                                @endif
                                <td>
                                    <div class="flex items-center gap-2 justify-end">
                                        <a class="link text-xs" href="{{ route('sewing-jobs.edit', $stationJob) }}">Edit</a>
                                        <a class="link text-xs" href="{{ route('sewing-jobs.show', $stationJob) }}">View</a>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        @php
                            $emptyCols = in_array($step, ['qc_phase_1', 'qc_phase_2'], true) ? 7 : ($step === 'neck_finishing' ? 6 : 5);
                        @endphp
                        <tr><td colspan="{{ $emptyCols }}" class="empty">No previous {{ strtoupper(str_replace('_', ' ', $step)) }} jobs.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</form>
