<x-app-layout>
    <x-slot name="header"><x-page-heading :title="($job->exists ? 'Edit' : 'Record').' sewing · '.strtoupper(str_replace('_', ' ', $step))" subtitle="Release each batch from one sewing station to the next."><a class="btn btn-secondary" href="{{ route('sewing-jobs.index') }}">Back</a></x-page-heading></x-slot>
    <form method="POST" action="{{ $job->exists ? route('sewing-jobs.update', $job) : route('sewing-jobs.store') }}" x-data="sewingForm(@js(['available' => $available, 'rows' => $rows, 'step' => $step]))" class="space-y-6">
        @csrf @if($job->exists) @method('PUT') @endif
        <input type="hidden" name="current_step" value="{{ $step }}">
        <section class="panel grid gap-5 p-6 sm:grid-cols-3">
            <label class="field">Date<input type="date" required name="sewing_date" value="{{ old('sewing_date', $job->sewing_date?->toDateString() ?? now()->toDateString()) }}"></label>
            <label class="field">Operator<select required name="staff_id"><option value="">Choose active staff</option>@foreach($staffs as $staff)<option value="{{ $staff->id }}" @selected(old('staff_id', $job->staff_id) == $staff->id)>{{ $staff->name }}</option>@endforeach</select></label>
            <label class="field">Notes<textarea name="notes">{{ old('notes', $job->notes) }}</textarea></label>
        </section>
        <section class="panel sewing-stage-panel">
            <div class="sewing-stage-header">
                <div class="sewing-stage-icon">
                    @if(in_array($step, ['qc_phase_1', 'qc_phase_2'], true))
                        <x-icon name="{{ $step === 'qc_phase_1' ? 'check' : 'shield' }}" class="h-6 w-6"/>
                    @elseif($step === 'iron')
                        <x-icon name="sparkles" class="h-6 w-6"/>
                    @else
                        →
                    @endif
                </div>
                <div>
                    <div class="sewing-eyebrow">Current station</div>
                    <h2>{{ strtoupper(str_replace('_', ' ', $step)) }}</h2>
                    <p class="hint">
                        @if(in_array($step, ['qc_phase_1', 'qc_phase_2'], true))
                            Select batches and record passed garments, altered count, and rejected count.
                        @elseif($step === 'iron')
                            Steam press and iron all passed garments from QC Phase 1.
                        @else
                            Only batches released by the previous station are shown. Enter the quantity completed here, then save the handoff.
                        @endif
                    </p>
                </div>
            </div>
            <div class="sewing-stage-path">
                @foreach($steps as $stage)
                    <a class="{{ $stage === $step ? 'active' : '' }}" href="{{ route('sewing-jobs.create', ['step' => $stage]) }}">{{ strtoupper(str_replace('_', ' ', $stage)) }}</a>
                    @if(!$loop->last)<span>→</span>@endif
                @endforeach
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-3 bg-[#fafafc]">
                <div class="flex flex-wrap items-center gap-2.5">
                    <input type="search" class="text-xs py-1.5 px-3 rounded-xl border border-line bg-surface focus:border-accent w-44 sm:w-52" placeholder="Search lot, color, DIA..." x-model="search">
                    <select class="text-xs font-semibold py-1.5 pl-3 pr-8 rounded-xl border border-line bg-surface text-ink focus:border-accent cursor-pointer min-w-[7.5rem]" x-model="lotFilter">
                        <option value="">All lots</option>
                        <template x-for="l in lots" :key="l">
                            <option :value="l" x-text="l"></option>
                        </template>
                    </select>
                    <select class="text-xs font-semibold py-1.5 pl-3 pr-8 rounded-xl border border-line bg-surface text-ink focus:border-accent cursor-pointer min-w-[6.5rem]" x-model="diaFilter">
                        <option value="">All DIA</option>
                        <template x-for="d in dias" :key="d">
                            <option :value="d" x-text="'DIA ' + d"></option>
                        </template>
                    </select>
                    <select class="text-xs font-semibold py-1.5 pl-3 pr-8 rounded-xl border border-line bg-surface text-ink focus:border-accent cursor-pointer min-w-[7.5rem]" x-model="colorFilter">
                        <option value="">All colors</option>
                        <template x-for="c in colors" :key="c">
                            <option :value="c" x-text="c"></option>
                        </template>
                    </select>
                    <button type="button" x-show="search || lotFilter || diaFilter || colorFilter" @click="search = ''; lotFilter = ''; diaFilter = ''; colorFilter = '';" class="text-xs text-muted hover:text-accent font-medium px-1">Clear filters</button>
                </div>
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 text-xs font-semibold cursor-pointer text-ink">
                        <input type="checkbox" class="sewing-row-checkbox" :checked="allSelected(filteredAvailable)" x-effect="$el.indeterminate = partiallySelected(filteredAvailable)" :disabled="!filteredAvailable.length" @change="toggleAll(filteredAvailable, $event.target.checked)">
                        Select all visible
                    </label>
                    <div class="text-xs text-muted font-medium">
                        <span x-text="filteredAvailable.length"></span> of <span x-text="available.length"></span> batches
                        <span x-show="rows.length" class="ml-1 text-accent font-semibold" x-text="'(' + rows.length + ' selected)'"></span>
                    </div>
                </div>
            </div>
            <div class="sewing-color-queue">
                <template x-for="group in colorGroups" :key="group.color">
                    <section class="sewing-color-queue-section">
                        <div class="sewing-color-section-heading flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <label class="flex items-center gap-2 cursor-pointer" :title="'Select all ' + group.color + ' batches'">
                                    <input type="checkbox" class="sewing-row-checkbox" :checked="allSelected(group.rows)" x-effect="$el.indeterminate = partiallySelected(group.rows)" @change="toggleAll(group.rows, $event.target.checked)">
                                    <span class="sewing-color"><span class="sewing-color-dot"></span><strong x-text="group.color"></strong></span>
                                </label>
                            </div>
                            <span class="sewing-color-count" x-text="group.rows.length + ' batches'"></span>
                        </div>
                        <div class="table-wrap">
                            <table class="data-table sewing-stage-table">
                                <thead>
                                    <tr>
                                        <th class="sewing-select-column">
                                            <label class="flex items-center justify-center cursor-pointer" :title="'Select all ' + group.color">
                                                <input type="checkbox" class="sewing-row-checkbox" :checked="allSelected(group.rows)" x-effect="$el.indeterminate = partiallySelected(group.rows)" @change="toggleAll(group.rows, $event.target.checked)">
                                            </label>
                                        </th>
                                        <th>Batch</th>
                                        <th>DIA</th>
                                        <th class="number">Available</th>
                                        @if(in_array($step, ['qc_phase_1', 'qc_phase_2'], true))
                                            <th class="number">Passed<br><small>Dozen + pieces</small></th>
                                            <th class="number text-blue-700">Altered<br><small>pieces</small></th>
                                            <th class="number text-rose-700">Rejected<br><small>pieces</small></th>
                                        @elseif($step === 'iron')
                                            <th class="number">Ironed quantity<br><small>Dozen + pieces</small></th>
                                        @else
                                            <th class="number">Release quantity<br><small>Dozen + pieces</small></th>
                                            @if($step === 'neck_finishing')
                                                <th class="number">Rejected</th>
                                            @endif
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(sourceItem, sIdx) in group.rows" :key="sourceItem.group_key">
                                        <tr :class="(selected(sourceItem) ? 'sewing-source-selected' : '') + (sIdx % 2 === 1 ? ' row-even bg-[#f7f9fc]' : ' bg-white')">
                                            <td class="sewing-select-column">
                                                <input type="checkbox" class="sewing-row-checkbox" :checked="selected(sourceItem)" @change="toggle(sourceItem)" :aria-label="'Select ' + sourceItem.lot + ' ' + sourceItem.color + ' DIA ' + sourceItem.dia">
                                                <template x-if="selected(sourceItem)">
                                                    <input type="hidden" :name="`rows[${rows.indexOf(selectedRow(sourceItem))}][group_key]`" x-model="selectedRow(sourceItem).group_key">
                                                </template>
                                            </td>
                                            <td><strong x-text="sourceItem.lot"></strong></td>
                                            <td><span class="pill" x-text="'DIA ' + sourceItem.dia"></span></td>
                                            <td class="number">
                                                <div class="sewing-available-quantity">
                                                    <strong x-text="availableLabel(sourceItem)"></strong>
                                                    <span x-text="sourceItem.total_pieces + ' pieces'"></span>
                                                </div>
                                            </td>
                                            @if(in_array($step, ['qc_phase_1', 'qc_phase_2'], true))
                                                <td class="number">
                                                    <template x-if="selected(sourceItem)">
                                                        <div class="sewing-compact-quantity">
                                                            <label><span>Dozen</span><input class="sewing-stage-quantity-input" type="number" required min="0" step="1" :name="`rows[${rows.indexOf(selectedRow(sourceItem))}][{{ $step }}_dozens]`" x-model="selectedRow(sourceItem).{{ $step }}_dozens" placeholder="0" aria-label="Passed dozen"></label>
                                                            <b>+</b>
                                                            <label><span>Pieces</span><input class="sewing-stage-quantity-input" type="number" required min="0" max="11" step="1" :name="`rows[${rows.indexOf(selectedRow(sourceItem))}][{{ $step }}_pieces]`" x-model="selectedRow(sourceItem).{{ $step }}_pieces" placeholder="0" aria-label="Passed pieces"></label>
                                                        </div>
                                                    </template>
                                                    <span x-show="!selected(sourceItem)" class="hint">Select batch</span>
                                                </td>
                                                <td class="number">
                                                    <template x-if="selected(sourceItem)">
                                                        <div class="flex items-center justify-end">
                                                            <input class="sewing-stage-quantity-input w-20 text-center font-semibold text-blue-800 border-blue-300 bg-blue-50/50 focus:border-blue-500 focus:ring-blue-500" type="number" required min="0" step="1" :name="`rows[${rows.indexOf(selectedRow(sourceItem))}][{{ $step }}_altered_pieces]`" x-model="selectedRow(sourceItem).{{ $step }}_altered_pieces" @input="syncQcPassed(selectedRow(sourceItem), sourceItem, $event)" placeholder="0" title="Altered pieces">
                                                        </div>
                                                    </template>
                                                </td>
                                                <td class="number">
                                                    <template x-if="selected(sourceItem)">
                                                        <div class="flex items-center justify-end">
                                                            <input class="sewing-stage-quantity-input w-20 text-center font-semibold text-rose-800 border-rose-300 bg-rose-50/50 focus:border-rose-500 focus:ring-rose-500" type="number" required min="0" step="1" :name="`rows[${rows.indexOf(selectedRow(sourceItem))}][{{ $step }}_rejected_pieces]`" x-model="selectedRow(sourceItem).{{ $step }}_rejected_pieces" @input="syncQcPassed(selectedRow(sourceItem), sourceItem, $event)" placeholder="0" title="Rejected pieces">
                                                        </div>
                                                    </template>
                                                </td>
                                            @else
                                                <td class="number">
                                                    <template x-if="selected(sourceItem)">
                                                        <div class="sewing-compact-quantity">
                                                            <label><span>Dozen</span><input class="sewing-stage-quantity-input" type="number" required min="0" step="1" :name="`rows[${rows.indexOf(selectedRow(sourceItem))}][{{ $step }}_dozens]`" x-model="selectedRow(sourceItem).{{ $step }}_dozens" placeholder="0" aria-label="Dozen to release"></label>
                                                            <b>+</b>
                                                            <label><span>Pieces</span><input class="sewing-stage-quantity-input" type="number" required min="0" max="11" step="1" :name="`rows[${rows.indexOf(selectedRow(sourceItem))}][{{ $step }}_pieces]`" x-model="selectedRow(sourceItem).{{ $step }}_pieces" placeholder="0" aria-label="Pieces to release"></label>
                                                        </div>
                                                    </template>
                                                    <span x-show="!selected(sourceItem)" class="hint">Select batch</span>
                                                </td>
                                                @if($step === 'neck_finishing')
                                                    <td class="number">
                                                        <template x-if="selected(sourceItem)">
                                                            <input class="sewing-stage-quantity-input" type="number" required min="0" step="1" :name="`rows[${rows.indexOf(selectedRow(sourceItem))}][rejected_pieces]`" x-model="selectedRow(sourceItem).rejected_pieces" placeholder="0" aria-label="Rejected pieces">
                                                        </template>
                                                    </td>
                                                @endif
                                            @endif
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </template>
                <div x-show="!available.length" class="empty p-6">No batches are waiting at this station.</div>
                <div x-show="available.length && !filteredAvailable.length" class="empty p-6">No batches match the current search or filters.</div>
            </div>
        </section>
        <div class="flex justify-end"><button class="btn btn-primary" :disabled="!rows.length || rows.some(row => invalid(row))">Save and release to next station</button></div>
    </form>
</x-app-layout>
