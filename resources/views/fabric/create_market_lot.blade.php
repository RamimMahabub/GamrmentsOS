<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="eyebrow flex items-center gap-2 mb-1">
                    <span class="h-2 w-2 rounded-full bg-accent animate-pulse"></span>
                    Direct Market Purchase · Ready Fabric Rolls
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-ink">New Underpants Lot from Market Rolls</h1>
                <p class="text-sm text-muted mt-1">Receive ready dyed fabric rolls directly from market/supplier, skipping knitting and dyeing.</p>
            </div>
            <a class="btn btn-secondary text-xs" href="{{ route('fabric.index') }}">
                &larr; Back to Fabric Inventory
            </a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('fabric.market-lot.store') }}" x-data="{
        productType: 'boxer',
        defaultUnit: @js($defaultWeightUnit),
        defaultPrice: '',
        currency: @js($currency ?? 'BDT'),
        previewBoxer: @js($previewBoxerLot),
        previewUnderwear: @js($previewUnderwearLot),
        customLotNumber: '',
        colorCount: 2,
        diaCount: 4,
        dias: [32, 34, 36, 38],
        blocks: [],

        get activeLotNumber() {
            if (this.customLotNumber.trim()) return this.customLotNumber;
            return this.productType === 'boxer' ? this.previewBoxer : this.previewUnderwear;
        },

        init() {
            this.buildStructure();
        },

        buildStructure() {
            let currentDias = [...this.dias];
            while (currentDias.length < this.diaCount) {
                let last = currentDias.length ? currentDias[currentDias.length - 1] : 30;
                currentDias.push(last + 2);
            }
            if (currentDias.length > this.diaCount) {
                currentDias = currentDias.slice(0, this.diaCount);
            }
            this.dias = currentDias;

            let newBlocks = [];
            for (let i = 0; i < this.colorCount; i++) {
                let existingBlock = this.blocks[i];
                let blockColor = existingBlock ? existingBlock.color : '';
                let rows = [];

                for (let j = 0; j < this.dias.length; j++) {
                    let diaVal = this.dias[j];
                    let existingRow = existingBlock ? existingBlock.rows.find(r => r.dia == diaVal) : null;
                    if (existingRow) {
                        rows.push(existingRow);
                    } else {
                        rows.push({
                            dia: diaVal,
                            rolls: [
                                {
                                    number: this.generateRollNumber(i, diaVal, 1, blockColor),
                                    weight: '',
                                    unit: this.defaultUnit,
                                    purchase_price_per_kg: this.defaultPrice || '',
                                    autoNumber: true
                                }
                            ]
                        });
                    }
                }
                newBlocks.push({
                    color: blockColor,
                    rows: rows
                });
            }
            this.blocks = newBlocks;
        },

        resizeColors() {
            let count = parseInt(this.colorCount) || 1;
            if (count < 1) count = 1;
            if (count > 20) count = 20;
            this.colorCount = count;
            this.buildStructure();
        },

        resizeDias() {
            let count = parseInt(this.diaCount) || 1;
            if (count < 1) count = 1;
            if (count > 20) count = 20;
            this.diaCount = count;
            this.buildStructure();
        },

        syncDia(dIdx, newVal) {
            let val = parseInt(newVal) || 30;
            this.dias[dIdx] = val;
            this.blocks.forEach((block, bIdx) => {
                if (block.rows[dIdx]) {
                    block.rows[dIdx].dia = val;
                    block.rows[dIdx].rolls.forEach((r, rIdx) => {
                        if (r.autoNumber) {
                            r.number = this.generateRollNumber(bIdx, val, rIdx + 1, block.color);
                        }
                    });
                }
            });
        },

        generateRollNumber(colorIdx, dia, rollSeq, colorName) {
            let colCode = String(colorName || '').replace(/[^a-z0-9]/gi, '').toUpperCase();
            if (colCode.length > 3) colCode = colCode.substring(0, 3);
            if (!colCode) colCode = 'C' + (colorIdx + 1);
            let seq = String(rollSeq).padStart(2, '0');
            return dia + colCode + '-' + seq;
        },

        updateColorName(block, colorIdx) {
            block.rows.forEach(row => {
                row.rolls.forEach((roll, rIdx) => {
                    if (roll.autoNumber) {
                        roll.number = this.generateRollNumber(colorIdx, row.dia, rIdx + 1, block.color);
                    }
                });
            });
        },

        addRollToRow(row, block, colorIdx) {
            let nextSeq = row.rolls.length + 1;
            let lastRoll = row.rolls[row.rolls.length - 1];
            row.rolls.push({
                number: this.generateRollNumber(colorIdx, row.dia, nextSeq, block.color),
                weight: '',
                unit: lastRoll ? lastRoll.unit : this.defaultUnit,
                purchase_price_per_kg: this.defaultPrice || '',
                autoNumber: true
            });
        },

        removeRollFromRow(row, rollIdx) {
            if (row.rolls.length <= 1) {
                row.rolls[0].weight = '';
                return;
            }
            row.rolls.splice(rollIdx, 1);
        },

        rowWeightKg(row) {
            let sum = 0;
            for (let r of row.rolls) {
                let w = parseFloat(r.weight) || 0;
                if (w > 0) {
                    if (r.unit === 'lb') w = w * 0.45359237;
                    sum += w;
                }
            }
            return sum;
        },

        colorRollsCount(block) {
            let count = 0;
            for (let row of block.rows) {
                for (let r of row.rolls) {
                    if ((parseFloat(r.weight) || 0) > 0) count++;
                }
            }
            return count;
        },

        colorWeightKg(block) {
            let sum = 0;
            for (let row of block.rows) {
                sum += this.rowWeightKg(row);
            }
            return sum;
        },

        totalRollsCount() {
            let count = 0;
            for (let block of this.blocks) {
                count += this.colorRollsCount(block);
            }
            return count;
        },

        grandTotalWeightKg() {
            let sum = 0;
            for (let block of this.blocks) {
                sum += this.colorWeightKg(block);
            }
            return sum;
        },

        grandTotalCost() {
            let total = 0;
            let defaultP = parseFloat(this.defaultPrice) || 0;
            for (let block of this.blocks) {
                for (let row of block.rows) {
                    for (let r of row.rolls) {
                        let w = parseFloat(r.weight) || 0;
                        if (w > 0) {
                            if (r.unit === 'lb') w = w * 0.45359237;
                            let p = parseFloat(r.purchase_price_per_kg);
                            if (isNaN(p) || p <= 0) p = defaultP;
                            total += (w * p);
                        }
                    }
                }
            }
            return total;
        },

        format(val, decimals = 3) {
            let num = parseFloat(val);
            if (isNaN(num)) return '0.000';
            return num.toFixed(decimals);
        },

        formatWeight(kg, decimals = 3) {
            let num = parseFloat(kg);
            if (isNaN(num)) return '—';
            let lb = num / 0.45359237;
            return num.toFixed(decimals) + ' kg (' + lb.toFixed(decimals) + ' lb)';
        }
    }" class="space-y-6">
        @csrf

        <!-- Section 1: Product & Lot Identity -->
        <section class="panel">
            <div class="panel-head">
                <div class="flex items-center gap-3">
                    <span class="step">1</span>
                    <h2>Underpants Product &amp; Lot Setup</h2>
                </div>
            </div>
            <div class="grid gap-6 p-6 sm:grid-cols-3">
                <label class="field">
                    <span>Product Type</span>
                    <select name="product_type" x-model="productType" required class="cursor-pointer">
                        <option value="boxer">Boxer</option>
                        <option value="underwear">Underwear</option>
                    </select>
                </label>

                <label class="field">
                    <span>Lot Number</span>
                    <input type="text" name="lot_number" x-model="customLotNumber" :placeholder="activeLotNumber">
                    <span class="hint">Leave empty to auto-generate: <b x-text="activeLotNumber"></b></span>
                </label>

                <label class="field">
                    <span>Entry Date</span>
                    <input type="date" required name="lot_date" value="{{ old('lot_date', now()->toDateString()) }}">
                </label>
            </div>

            <div class="grid gap-6 px-6 pb-6 sm:grid-cols-3 border-t border-line/60 pt-4">
                <label class="field">
                    <span>Fabric Supplier / Market Source</span>
                    <input type="text" name="supplier_name" value="{{ old('supplier_name') }}" placeholder="e.g. Noman Textile, Market Broker...">
                </label>

                <label class="field">
                    <span>Default Purchase Price / kg ({{ $currency ?? 'BDT' }})</span>
                    <input type="number" step="0.01" min="0" name="default_purchase_price_per_kg" x-model="defaultPrice" placeholder="e.g. 450.00">
                    <span class="hint">Will auto-apply to entered rolls if blank</span>
                </label>

                <div class="field flex flex-col justify-end">
                    <label class="flex items-center gap-2 p-2 rounded-xl bg-raised border border-line cursor-pointer">
                        <input type="checkbox" name="has_rib" value="1" @checked(old('has_rib'))>
                        <div>
                            <span class="text-xs font-bold text-ink">Has Rib Fabric?</span>
                            <p class="text-[11px] text-muted">Check if lot includes rib component for cutting</p>
                        </div>
                    </label>
                </div>
            </div>
        </section>

        <!-- Section 2: Structure Specification (Colors & DIAs) -->
        <section class="panel">
            <div class="panel-head">
                <div class="flex items-center gap-3">
                    <span class="step">2</span>
                    <div>
                        <h2>Colors &amp; DIAs Specification</h2>
                        <p class="hint mt-0.5">Specify the number of colors and total DIAs first. Tables below will generate automatically.</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 p-6 sm:grid-cols-2 lg:grid-cols-4 bg-[#f8fafc] border-b border-line">
                <label class="field">
                    <span class="font-bold text-ink">Number of Colors</span>
                    <input type="number" min="1" max="20" required x-model.number="colorCount" @change="resizeColors()" class="font-bold text-base">
                    <span class="hint">How many colors in this lot</span>
                </label>

                <label class="field">
                    <span class="font-bold text-ink">Total Number of DIAs</span>
                    <input type="number" min="1" max="20" required x-model.number="diaCount" @change="resizeDias()" class="font-bold text-base">
                    <span class="hint">How many distinct DIAs</span>
                </label>

                <div class="sm:col-span-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-muted block mb-2">DIAs for this Lot (inches)</span>
                    <div class="flex flex-wrap items-center gap-2">
                        <template x-for="(dia, dIdx) in dias" :key="dIdx">
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl border border-line bg-surface shadow-xs">
                                <span class="text-[11px] font-bold text-muted" x-text="'DIA ' + (dIdx + 1)"></span>
                                <input type="number" min="1" max="999" x-model.number="dias[dIdx]" @change="syncDia(dIdx, dias[dIdx])" class="w-16 text-center text-xs font-bold font-mono py-1 px-1 rounded-lg border border-line bg-surface" required>
                            </div>
                        </template>
                    </div>
                    <span class="hint mt-1.5 block">Edit DIA values above. Changes instantly update the tables for all colors below.</span>
                </div>
            </div>
        </section>

        <datalist id="color-master">
            @foreach($colorOptions as $color)
                <option value="{{ $color }}"></option>
            @endforeach
        </datalist>

        <!-- Section 3: Color Blocks & Fabric Tables (Dyeing-Style) -->
        <template x-for="(block, i) in blocks" :key="i">
            <section class="panel dyeing-color-panel dyeing-entry-sheet shadow-xs" :class="i % 2 === 0 ? 'dyeing-color-even' : 'dyeing-color-odd'">
                <div class="panel-head dyeing-color-head">
                    <div class="flex flex-wrap items-center gap-4">
                        <span class="step" x-text="i + 1"></span>
                        <div>
                            <div class="dyeing-color-kicker" x-text="'Color ' + (i + 1)"></div>
                            <div class="flex items-center gap-3">
                                <input required type="text" list="color-master" :name="'blocks['+i+'][color]'" x-model="block.color" @input="updateColorName(block, i)" placeholder="Enter color name (e.g. Black, Navy, White)" class="w-64 font-bold text-sm">
                                <span class="text-xs text-muted font-medium" x-show="block.color">
                                    · <span x-text="colorRollsCount(block)"></span> rolls recorded (<span x-text="formatWeight(colorWeightKg(block))"></span>)
                                </span>
                            </div>
                        </div>
                    </div>
                    <span class="hint">New color names are saved automatically</span>
                </div>

                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="w-24">DIA</th>
                                <th>READY FABRIC ROLLS <span class="hint normal-case tracking-normal">(enter roll weights below)</span></th>
                                <th class="number text-right w-36">DIA WEIGHT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, j) in block.rows" :key="row.dia">
                                <tr :class="j % 2 === 1 ? 'row-even bg-[#f7f9fc]' : 'bg-white'">
                                    <td class="align-top pt-3">
                                        <span class="pill font-mono font-bold text-xs" x-text="row.dia"></span>
                                        <input type="hidden" :name="'blocks['+i+'][rows]['+j+'][dia]'" :value="row.dia">
                                    </td>
                                    <td class="dyeing-entry-rolls">
                                        <div class="dyeing-entry-rolls-box">
                                            <div class="dyeing-entry-roll-head">
                                                <span class="font-semibold text-ink" x-text="(row.rolls?.length || 0) + ' rolls for DIA ' + row.dia"></span>
                                                <button type="button" class="btn btn-secondary btn-xs" @click="addRollToRow(row, block, i)">+ Add roll</button>
                                            </div>
                                            <template x-for="(roll, k) in row.rolls" :key="k">
                                                <div class="dyeing-entry-roll">
                                                    <input required maxlength="100" x-model="roll.number" @input="roll.autoNumber=false" :name="'blocks['+i+'][rows]['+j+'][rolls]['+k+'][number]'" placeholder="Roll #" class="font-mono text-xs" aria-label="Roll number">
                                                    <input type="number" step="0.001" min="0.001" x-model="roll.weight" :name="'blocks['+i+'][rows]['+j+'][rolls]['+k+'][weight]'" placeholder="Weight" class="text-xs font-semibold" aria-label="Roll weight">
                                                    <select x-model="roll.unit" :name="'blocks['+i+'][rows]['+j+'][rolls]['+k+'][unit]'" class="text-xs py-1 px-1.5" aria-label="Unit">
                                                        <option value="kg">KG</option>
                                                        <option value="lb">LB</option>
                                                    </select>
                                                    <input type="number" step="0.01" min="0" x-model="roll.purchase_price_per_kg" :name="'blocks['+i+'][rows]['+j+'][rolls]['+k+'][purchase_price_per_kg]'" :placeholder="defaultPrice || 'Price/kg'" class="text-xs" aria-label="Purchase price">
                                                    <button type="button" class="btn btn-secondary btn-xs text-danger hover:bg-danger-soft" @click="removeRollFromRow(row, k)" title="Remove roll">&times;</button>
                                                </div>
                                            </template>
                                        </div>
                                    </td>
                                    <td class="number text-right align-top pt-4 font-mono font-bold text-xs" :class="rowWeightKg(row) > 0 ? 'text-ink' : 'text-muted'">
                                        <span x-text="formatWeight(rowWeightKg(row))"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>Color subtotal</td>
                                <td class="font-semibold text-xs">
                                    <span x-text="colorRollsCount(block)"></span> recorded rolls
                                </td>
                                <td class="number text-right font-bold font-mono text-xs" x-text="formatWeight(colorWeightKg(block))"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
        </template>

        <!-- Grand Total Summary Panel (Dyeing Style) -->
        <section class="panel">
            <div class="panel-head">
                <h2>Market Purchase Fabric Summary</h2>
                <span class="hint">Aggregated across all colors and DIAs</span>
            </div>
            <div class="grid gap-5 p-6 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <span class="hint">TOTAL COLORS</span>
                    <div class="mt-2 text-2xl font-semibold" x-text="blocks.length"></div>
                </div>
                <div>
                    <span class="hint">TOTAL ROLLS RECORDED</span>
                    <div class="mt-2 text-2xl font-semibold">
                        <span x-text="totalRollsCount()"></span> <small class="text-xs text-muted">rolls</small>
                    </div>
                </div>
                <div>
                    <span class="hint">TOTAL FABRIC WEIGHT</span>
                    <div class="mt-2 text-2xl font-semibold text-accent">
                        <span x-text="formatWeight(grandTotalWeightKg())"></span>
                    </div>
                </div>
                <div>
                    <span class="hint">ESTIMATED VALUE</span>
                    <div class="mt-2 text-2xl font-semibold text-ink">
                        <span x-text="currency + ' ' + format(grandTotalCost(), 2)"></span>
                    </div>
                </div>
            </div>
        </section>

        <div class="flex justify-end gap-3 pt-2">
            <a class="btn btn-secondary" href="{{ route('fabric.index') }}">Cancel</a>
            <button class="btn btn-primary" :disabled="totalRollsCount() === 0">
                Create Underpants Lot &amp; Add Rolls
            </button>
        </div>
    </form>
</x-app-layout>
