<x-app-layout>
    <x-slot name="header">
        <x-page-heading :title="($job->exists ? 'Edit' : 'Record').' Packing Run'" subtitle="Inspect, fold, apply stickers, poly bag, and pack into 6-pc packets.">
            <a class="btn btn-secondary" href="{{ route('packing-jobs.index') }}">Back</a>
        </x-page-heading>
    </x-slot>

    <form method="POST" action="{{ $job->exists ? route('packing-jobs.update', $job) : route('packing-jobs.store') }}" x-data="packingForm(@js(['available' => $available, 'rows' => $rows, 'materials' => $materials]))" class="space-y-6">
        @csrf
        @if($job->exists)
            @method('PUT')
        @endif

        <!-- Meta Header: Date, Staff, Notes -->
        <section class="panel grid gap-5 p-6 sm:grid-cols-3">
            <label class="field">
                <span>Date</span>
                <input type="date" required name="packing_date" value="{{ old('packing_date', $job->packing_date?->toDateString() ?? now()->toDateString()) }}">
            </label>
            <label class="field">
                <span>Operator / Staff</span>
                <select required name="staff_id">
                    <option value="">Choose active staff</option>
                    @foreach($staffs as $staff)
                        <option value="{{ $staff->id }}" @selected(old('staff_id', $job->staff_id) == $staff->id)>{{ $staff->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="field">
                <span>Notes</span>
                <textarea name="notes" placeholder="Optional packaging notes...">{{ old('notes', $job->notes) }}</textarea>
            </label>
        </section>

        <!-- Packing Stage Panel -->
        <section class="panel sewing-stage-panel">
            <div class="sewing-stage-header">
                <div class="sewing-stage-icon">
                    <x-icon name="package" class="h-6 w-6"/>
                </div>
                <div>
                    <div class="sewing-eyebrow">Packing station</div>
                    <h2>Batches ready for packing</h2>
                    <p class="hint">Select batches, verify good pieces from QC Phase 2 / Iron, apply stickers &amp; poly bag, and pack into 6-pc packets.</p>
                </div>
            </div>

            <!-- Native GarmentsOS Live Overview KPIs -->
            <div class="sewing-overview-kpis grid grid-cols-2 lg:grid-cols-4 gap-3 p-4 bg-[#f8f8fa] border-b border-line">
                <div class="sewing-overview-kpi sewing-overview-kpi-primary">
                    <span>Total to Pack</span>
                    <strong x-text="totalPackedPieces.toLocaleString() + ' pcs'">0 pcs</strong>
                    <small x-text="totalPackets + ' pkts (6/pkt) · ' + totalDozens + ' doz'">0 pkts</small>
                </div>
                <div class="sewing-overview-kpi">
                    <span>Poly Bags (1/pc)</span>
                    <strong x-text="totalPackedPieces.toLocaleString() + ' pcs'">0 pcs</strong>
                    <small :class="Number(getMaterialStock('poly_bag')?.balance || 0) < totalPackedPieces ? 'text-rose-600 font-semibold' : 'text-emerald-700'" x-text="Number(getMaterialStock('poly_bag')?.balance || 0).toLocaleString() + ' in stock'"></small>
                </div>
                <div class="sewing-overview-kpi">
                    <span>Packet Bags (1/6pc)</span>
                    <strong class="text-accent" x-text="totalPacketMaterials.toLocaleString() + ' pkts'">0 pkts</strong>
                    <small :class="Number(getMaterialStock('packet_material')?.balance || 0) < totalPacketMaterials ? 'text-rose-600 font-semibold' : 'text-emerald-700'" x-text="Number(getMaterialStock('packet_material')?.balance || 0).toLocaleString() + ' in stock'"></small>
                </div>
                <div class="sewing-overview-kpi">
                    <span>Size Stickers (1/pc)</span>
                    <strong x-text="totalPackedPieces.toLocaleString() + ' pcs'">0 pcs</strong>
                    <small class="truncate" x-text="Object.keys(sizeBreakdown).map(s => s + ': ' + sizeBreakdown[s]).join(', ') || 'Auto-matched to DIA'"></small>
                </div>
            </div>

            <!-- Filters Bar -->
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

            <!-- Color Queue -->
            <div class="sewing-color-queue">
                <template x-for="group in colorGroups" :key="group.color">
                    <section class="sewing-color-queue-section">
                        <div class="sewing-color-section-heading flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <label class="flex items-center gap-2 cursor-pointer" :title="'Select all ' + group.color + ' batches'">
                                    <input type="checkbox" class="sewing-row-checkbox" :checked="allSelected(group.rows)" x-effect="$el.indeterminate = partiallySelected(group.rows)" @change="toggleAll(group.rows, $event.target.checked)">
                                    <span class="sewing-color"><span class="sewing-color-dot" :style="{ background: group.color.toLowerCase() }"></span><strong x-text="group.color"></strong></span>
                                </label>
                            </div>
                            <span class="sewing-color-count" x-text="group.rows.length + ' batches'"></span>
                        </div>
                        <div class="table-wrap">
                            <table class="data-table packing-stage-table sewing-stage-table">
                                <thead>
                                    <tr>
                                        <th class="sewing-select-column" style="width: 3.5rem; text-align: center;">
                                            <label class="flex items-center justify-center cursor-pointer" :title="'Select all ' + group.color">
                                                <input type="checkbox" class="sewing-row-checkbox" style="width: 1.15rem !important; height: 1.15rem !important; min-width: 1.15rem !important; max-width: 1.15rem !important; display: block; margin: 0 auto;" :checked="allSelected(group.rows)" x-effect="$el.indeterminate = partiallySelected(group.rows)" @change="toggleAll(group.rows, $event.target.checked)">
                                            </label>
                                        </th>
                                        <th>Batch</th>
                                        <th>DIA</th>
                                        <th style="text-align: center;">Size</th>
                                        <th class="number">Available<br><small>from Iron/QC</small></th>
                                        <th class="number" style="text-align: center;">Quantity to Pack<br><small>Dozen + Pieces</small></th>
                                        <th class="number">Total Pieces</th>
                                        <th class="number">Packets<br><small>6 pcs/pkt</small></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(batch, sIdx) in group.rows" :key="batch.group_key">
                                        <tr :class="(selected(batch) ? 'sewing-source-selected' : '') + (sIdx % 2 === 1 ? ' row-even bg-[#f7f9fc]' : ' bg-white')">
                                            <td class="sewing-select-column" style="width: 3.5rem; text-align: center;">
                                                <input type="checkbox" class="sewing-row-checkbox" style="width: 1.15rem !important; height: 1.15rem !important; min-width: 1.15rem !important; max-width: 1.15rem !important; display: block; margin: 0 auto;" :checked="selected(batch)" @change="toggle(batch)" :aria-label="'Select ' + batch.lot + ' ' + batch.color + ' DIA ' + batch.dia">
                                                <template x-if="selected(batch)">
                                                    <input type="hidden" :name="`rows[${rowIndex(batch)}][group_key]`" :value="batch.group_key">
                                                </template>
                                            </td>
                                            <td>
                                                <strong x-text="batch.lot"></strong>
                                                <span class="hint block text-[11px]" x-text="batch.source_step"></span>
                                            </td>
                                            <td>
                                                <span class="pill" x-text="'DIA ' + batch.dia"></span>
                                            </td>
                                            <td style="text-align: center;">
                                                <template x-if="selected(batch)">
                                                    <input type="text" :name="`rows[${rowIndex(batch)}][size]`" x-model="selectedRow(batch).size" class="w-16 py-1 px-2 text-xs font-bold uppercase rounded border border-line bg-surface text-center focus:border-accent" placeholder="Size" style="max-width: 4.5rem !important; min-width: 0 !important; width: 4.5rem !important;">
                                                </template>
                                                <template x-if="!selected(batch)">
                                                    <span class="pill font-semibold uppercase text-xs" x-text="batch.suggested_size"></span>
                                                </template>
                                            </td>
                                            <td class="number">
                                                <div class="sewing-available-quantity">
                                                    <strong x-text="batch.total_pieces + ' pcs'"></strong>
                                                    <span x-text="batch.dozens + ' doz ' + (batch.pieces ? '+ ' + batch.pieces + ' pcs' : '')"></span>
                                                </div>
                                            </td>
                                            <td class="number">
                                                <template x-if="selected(batch)">
                                                    <div class="sewing-compact-quantity">
                                                        <label><span>Dozen</span><input class="sewing-stage-quantity-input" type="number" required min="0" step="1" :name="`rows[${rowIndex(batch)}][dozens]`" x-model.number="selectedRow(batch).dozens" placeholder="0" aria-label="Packed dozen"></label>
                                                        <b>+</b>
                                                        <label><span>Pieces</span><input class="sewing-stage-quantity-input" type="number" required min="0" max="11" step="1" :name="`rows[${rowIndex(batch)}][pieces]`" x-model.number="selectedRow(batch).pieces" placeholder="0" aria-label="Packed pieces"></label>
                                                    </div>
                                                </template>
                                                <span x-show="!selected(batch)" class="hint">Select batch</span>
                                            </td>
                                            <td class="number font-bold">
                                                <template x-if="selected(batch)">
                                                    <span :class="rowPieces(batch) > batch.total_pieces ? 'text-danger font-bold' : 'text-ink'" x-text="rowPieces(batch) + ' pcs'"></span>
                                                </template>
                                                <span x-show="!selected(batch)" class="hint">—</span>
                                            </td>
                                            <td class="number font-semibold text-accent">
                                                <template x-if="selected(batch)">
                                                    <span x-text="rowPackets(batch) + ' pkts'"></span>
                                                </template>
                                                <span x-show="!selected(batch)" class="hint">—</span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </template>
                <div x-show="!available.length" class="empty p-6">No batches are waiting for packing.</div>
                <div x-show="available.length && !filteredAvailable.length" class="empty p-6">No batches match the current search or filters.</div>
            </div>

            <!-- Bottom Save Action Bar -->
            <div class="flex items-center justify-between p-4 bg-[#fafafc] border-t border-line">
                <div class="text-xs text-muted">
                    <span class="font-bold text-ink" x-text="rows.length + ' batches selected'"></span>
                    <span x-show="totalPackedPieces > 0">
                        · Total to pack: <strong class="text-accent text-sm" x-text="totalPackedPieces.toLocaleString() + ' pieces (' + totalPackets + ' packets)'"></strong>
                    </span>
                </div>
                <div class="flex items-center gap-3">
                    <a class="btn btn-secondary" href="{{ route('packing-jobs.index') }}">Cancel</a>
                    <button class="btn btn-primary" :disabled="!rows.length || totalPackedPieces === 0">
                        {{ $job->exists ? 'Update Packing Job' : 'Complete & Save Packing' }}
                    </button>
                </div>
            </div>
        </section>
    </form>

    <script>
        function packingForm(config) {
            return {
                available: config.available || [],
                rows: config.rows || [],
                materials: config.materials || [],
                search: '',
                lotFilter: '',
                diaFilter: '',
                colorFilter: '',

                get lots() {
                    return [...new Set(this.available.map(r => r.lot))].sort();
                },
                get dias() {
                    return [...new Set(this.available.map(r => r.dia))].sort((a,b) => a-b);
                },
                get colors() {
                    return [...new Set(this.available.map(r => r.color))].sort();
                },
                get filteredAvailable() {
                    return this.available.filter(r => {
                        const s = this.search.toLowerCase();
                        const matchSearch = !s || r.lot.toLowerCase().includes(s) || r.color.toLowerCase().includes(s) || String(r.dia).includes(s);
                        const matchLot = !this.lotFilter || r.lot === this.lotFilter;
                        const matchDia = !this.diaFilter || String(r.dia) === String(this.diaFilter);
                        const matchColor = !this.colorFilter || r.color === this.colorFilter;
                        return matchSearch && matchLot && matchDia && matchColor;
                    });
                },
                get colorGroups() {
                    const groups = {};
                    this.filteredAvailable.forEach(r => {
                        if (!groups[r.color]) {
                            groups[r.color] = { color: r.color, rows: [] };
                        }
                        groups[r.color].rows.push(r);
                    });
                    return Object.values(groups);
                },
                selected(batch) {
                    return this.rows.some(r => r.group_key === batch.group_key);
                },
                selectedRow(batch) {
                    return this.rows.find(r => r.group_key === batch.group_key) || {};
                },
                rowIndex(batch) {
                    return this.rows.findIndex(r => r.group_key === batch.group_key);
                },
                toggle(batch) {
                    const idx = this.rowIndex(batch);
                    if (idx >= 0) {
                        this.rows.splice(idx, 1);
                    } else {
                        this.rows.push({
                            group_key: batch.group_key,
                            size: batch.suggested_size || '',
                            dozens: batch.dozens || 0,
                            pieces: batch.pieces || 0,
                            rejected_pieces: 0,
                        });
                    }
                },
                allSelected(items) {
                    return items.length > 0 && items.every(i => this.selected(i));
                },
                partiallySelected(items) {
                    const count = items.filter(i => this.selected(i)).length;
                    return count > 0 && count < items.length;
                },
                toggleAll(items, checked) {
                    items.forEach(item => {
                        const isSel = this.selected(item);
                        if (checked && !isSel) {
                            this.toggle(item);
                        } else if (!checked && isSel) {
                            this.toggle(item);
                        }
                    });
                },
                rowPieces(batch) {
                    const r = this.selectedRow(batch);
                    if (!r.group_key) return 0;
                    return (Number(r.dozens) || 0) * 12 + (Number(r.pieces) || 0);
                },
                rowPackets(batch) {
                    return Math.floor(this.rowPieces(batch) / 6);
                },
                get totalPackedPieces() {
                    return this.rows.reduce((sum, r) => sum + ((Number(r.dozens) || 0) * 12 + (Number(r.pieces) || 0)), 0);
                },
                get totalPackets() {
                    return Math.floor(this.totalPackedPieces / 6);
                },
                get totalLoosePieces() {
                    return this.totalPackedPieces % 6;
                },
                get totalPacketMaterials() {
                    return this.totalPackets + (this.totalLoosePieces > 0 ? 1 : 0);
                },
                get totalDozens() {
                    return (this.totalPackedPieces / 12).toFixed(3);
                },
                get sizeBreakdown() {
                    const breakdown = {};
                    this.rows.forEach(r => {
                        const pcs = (Number(r.dozens) || 0) * 12 + (Number(r.pieces) || 0);
                        if (pcs > 0) {
                            const size = r.size || 'Unspecified';
                            breakdown[size] = (breakdown[size] || 0) + pcs;
                        }
                    });
                    return breakdown;
                },
                get otherStickersCount() {
                    return this.materials.filter(m => m.category === 'other_sticker').length;
                },
                getMaterialStock(category, size = null) {
                    if (size) {
                        return this.materials.find(m => m.category === category && m.size_code === size) || this.materials.find(m => m.category === category);
                    }
                    return this.materials.find(m => m.category === category);
                }
            };
        }
    </script>
</x-app-layout>
