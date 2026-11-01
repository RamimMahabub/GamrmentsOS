import { Big, decimal, format, toKg, fromKg, priceToKg, priceFromKg, sum, output, waste, split } from './math.js';

export function registerForms(Alpine) {
const invalid = value => 'invalid:' + String(value);
const safe = fn => { try { return fn(); } catch { return null; } };

Alpine.data('weightInput', (initial = {}) => ({
    kg: initial.kg ?? '0', unit: initial.unit ?? 'lb', bagKg: initial.bagKg ?? null, mode: 'weight', text: '',
    init() { this.refresh(); },
    refresh() { this.text = safe(() => this.mode === 'bags' ? format(new Big(this.kg).div(this.bagKg).toString(), 2, true) : format(fromKg(this.kg, this.unit))); },
    change() { this.kg = safe(() => this.mode === 'bags' ? decimal(this.text).times(this.bagKg).toFixed(3, 1) : toKg(this.text, this.unit)) ?? invalid(this.text); },
    switchMode(value) { this.mode = value; this.refresh(); },
    bags() { return this.bagKg ? format(safe(() => new Big(this.kg).div(this.bagKg).toString()), 2, true) : null; },
    format,
}));
Alpine.data('yarnForm', initial => ({
    ...initial, price: '', bag: '', quantityText: '', quantityMode: 'weight',
    init() { this.refreshPrice(); this.refreshBag(); this.refreshQuantity(); },
    refreshPrice() { this.price = format(safe(() => priceFromKg(this.priceKg, this.unit)), 2); },
    priceChanged() { this.priceKg = safe(() => priceToKg(this.price, this.unit)) ?? invalid(this.price); },
    refreshBag() { this.bag = this.bagKg === '' ? '' : format(safe(() => fromKg(this.bagKg, this.bagUnit))); },
    bagChanged() { this.bagKg = this.bag === '' ? '' : (safe(() => toKg(this.bag, this.bagUnit, 8)) ?? invalid(this.bag)); },
    refreshQuantity() { this.quantityText = safe(() => this.quantityMode==='bags' ? format(new Big(this.kg).div(this.bagKg).toString(),2,true) : format(fromKg(this.kg,this.weightUnit))) ?? ''; },
    quantityChanged() { this.kg = safe(() => this.quantityMode==='bags' ? decimal(this.quantityText).times(this.bagKg).toFixed(3,1) : toKg(this.quantityText,this.weightUnit)) ?? invalid(this.quantityText); },
    quantityBags() { return this.bagKg ? format(safe(() => new Big(this.kg).div(this.bagKg).toString()),2,true) : null; },
    format,
}));
Alpine.data('priceInput', initial => ({
    kg: initial.kg ?? '', unit: initial.unit ?? 'lb', text: '',
    init() { this.refresh(); },
    refresh() { this.text = this.kg === '' ? '' : format(safe(() => priceFromKg(this.kg, this.unit)), 2); },
    change() { this.kg = this.text === '' ? '' : (safe(() => priceToKg(this.text, this.unit)) ?? invalid(this.text)); },
}));

Alpine.data('knittingForm', initial => ({
    ...initial, count: initial.rows.length || initial.presets.length, search: '', preview: initial.lotNumber ?? 'Select yarn and date', sequence: 0,
    init() {
        this.rows = this.rows.map(row => ({...row, modes: {}, displays: {}}));
        if (!this.rows.length) this.resize();
        this.syncYarns();
        this.$watch('unit', () => this.refreshDisplays());
        this.$watch('selected', () => { this.syncYarns(); this.previewLot(); });
        this.$watch('date', () => this.previewLot());
        this.previewLot();
    },
    visibleYarns() { return this.yarns.filter(y => (y.name + ' ' + y.type).toLowerCase().includes(this.search.toLowerCase())); },
    selectedYarns() { return this.selected.map(id => this.yarns.find(y => y.id === id)).filter(Boolean); },
    syncYarns() {
        this.rows.forEach(row => {
            const next = {};
            this.selected.forEach(id => next[id] = row.quantities[id] ?? '0.000');
            row.quantities = next; row.modes ??= {}; row.displays ??= {};
        });
        this.refreshDisplays();
    },
    resize() {
        const count = Number(this.count);
        if (!Number.isInteger(count) || count < 1 || count > 100) return;
        if (count < this.rows.length && this.rows.slice(count).some(r => safe(() => new Big(sum(Object.values(r.quantities))).gt('0')))) {
            if (!confirm('Remove the extra DIA rows and their entered quantities?')) { this.count = this.rows.length; return; }
        }
        this.rows = this.rows.slice(0, count);
        while (this.rows.length < count) this.rows.push({dia: String(this.presets[this.rows.length] ?? ''), waste_percent: '0', quantities: {}, modes: {}, displays: {}});
        this.syncYarns();
    },
    refreshDisplays() { this.rows.forEach(row => this.selectedYarns().forEach(y => this.refreshCell(row, y))); },
    refreshCell(row, yarn) {
        row.displays[yarn.id] = safe(() => row.modes[yarn.id] === 'bags' ? format(new Big(row.quantities[yarn.id]).div(yarn.bagKg).toString(), 2, true) : format(fromKg(row.quantities[yarn.id], this.unit))) ?? '';
    },
    setMode(row, yarn, mode) { row.modes[yarn.id] = mode; this.refreshCell(row, yarn); },
    setQuantity(row, yarn) {
        const value = row.displays[yarn.id];
        row.quantities[yarn.id] = safe(() => row.modes[yarn.id] === 'bags' ? decimal(value).times(yarn.bagKg).toFixed(3, 1) : toKg(value, this.unit)) ?? invalid(value);
    },
    rowInput(row) { return safe(() => sum(Object.values(row.quantities))); },
    rowOutput(row) { const input = this.rowInput(row); return input === null ? null : safe(() => output(input, row.waste_percent)); },
    totals() {
        const input = safe(() => sum(this.rows.map(r => this.rowInput(r))));
        const outputs = this.rows.map(r => this.rowOutput(r));
        const out = outputs.includes(null) ? null : safe(() => sum(outputs));
        return {input, output: out, waste: input !== null && out !== null ? safe(() => waste(input, out)) : null};
    },
    bags(row, yarn) { return yarn.bagKg ? format(safe(() => new Big(row.quantities[yarn.id]).div(yarn.bagKg).toString()), 2, true) : null; },
    async previewLot() {
        if (this.lotNumber) { this.preview = this.lotNumber; return; }
        const id = this.selected[0], sequence = ++this.sequence;
        if (!id || !this.date) { this.preview = 'Select yarn and date'; return; }
        try {
            const response = await fetch('/api/lots/preview?' + new URLSearchParams({yarn_id: id, date: this.date}), {headers: {Accept: 'application/json'}});
            if (!response.ok) throw new Error();
            const data = await response.json();
            if (sequence === this.sequence) this.preview = data.lot_number;
        } catch { if (sequence === this.sequence) this.preview = 'Preview unavailable'; }
    },
    format,
}));

Alpine.data('dyeingForm', initial => ({
    ...initial, count: initial.blocks.length ? initial.blocks.length : '', summary: null, loading: false, loadError: '', sequence: 0,
    init() {
        this.blocks.forEach((block, blockIndex) => block.rows.forEach(row => {
            row.touched = true;
            row.rolls ??= [];
            row.rolls.forEach(roll => { roll.colorIndex ??= blockIndex; });
            if (!row.rolls.length && safe(() => decimal(row.returned_fabric_kg).gt('0'))) {
                row.rolls.push({number: this.nextRollNumber(row, block), weight: row.returned_fabric_kg, unit: 'kg', autoNumber: true, autoWeight: false, colorIndex: blockIndex});
            }
        }));
        if (this.lotId) this.loadLot(true);
    },
    async loadLot(preserve = false) {
        this.loading = true; this.loadError = ''; this.summary = null;
        const sequence = ++this.sequence;
        if (!this.lotId) { this.loading = false; this.blocks = []; return; }
        const params = new URLSearchParams();
        if (this.jobId) params.set('job_id', this.jobId);
        try {
            const response = await fetch('/api/lots/' + this.lotId + '/fabric?' + params, {headers: {Accept: 'application/json'}});
            if (!response.ok) throw new Error();
            const data = await response.json();
            if (sequence !== this.sequence) return;
            this.summary = data;
            if (!preserve) this.blocks = [];
            this.resize();
        } catch { if (sequence === this.sequence) this.loadError = 'Fabric availability could not be loaded. Refresh and try again.'; }
        finally { if (sequence === this.sequence) this.loading = false; }
    },
    resize() {
        if (!this.summary) return;
        const count = Number(this.count);
        if (!Number.isInteger(count) || count < 1 || count > 50) return;
        if (count < this.blocks.length && this.blocks.slice(count).some(b => b.color || b.rows.some(r => r.touched || r.returned_fabric_kg))) {
            if (!confirm('Remove the extra color blocks and their entered values?')) { this.count = this.blocks.length; return; }
        }
        this.blocks = this.blocks.slice(0, count);
        while (this.blocks.length < count) this.blocks.push({color: '', rows: this.summary.rows.map(row => ({dia: row.dia, given_fabric_kg: '0.000', returned_fabric_kg: '', rolls: [], touched: false}))});
        this.summary.rows.forEach(dia => {
            const cells = this.blocks.map(b => b.rows.find(r => String(r.dia) === String(dia.dia))).filter(Boolean);
            const fixed = safe(() => sum(cells.filter(c => c.touched).map(c => decimal(c.given_fabric_kg).toString())));
            const remaining = fixed === null ? '0' : Big(dia.available_kg).minus(fixed).toString();
            const untouched = cells.filter(c => !c.touched);
            const portions = split(Big(remaining).lt('0') ? '0' : remaining, untouched.length);
            untouched.forEach((row, index) => row.given_fabric_kg = portions[index]);
        });
        this.blocks.forEach((block, blockIndex) => this.ensureDefaultRolls(block, blockIndex));
    },
    ensureDefaultRolls(block, blockIndex) {
        block.rows.forEach(row => {
            if (!row.rolls?.length && safe(() => decimal(row.given_fabric_kg).gt('0'))) {
                row.rolls = [{number: this.nextRollNumber(row, block), weight: row.given_fabric_kg, unit: 'kg', autoNumber: true, autoWeight: true, colorIndex: blockIndex}];
                this.updateRollTotal(row);
            } else if (row.rolls?.length === 1) {
                const roll = row.rolls[0];
                roll.colorIndex ??= blockIndex;
                if (roll.autoNumber) roll.number = this.nextRollNumber(row, block);
                if (roll.autoWeight) {
                    roll.weight = row.given_fabric_kg;
                    roll.unit = 'kg';
                    this.updateRollTotal(row);
                }
            }
        });
    },
    rollPrefix(row, block) {
        const color = String(block?.color ?? '').replace(/[^a-z0-9]/gi, '').toUpperCase() || 'ROLL';
        return String(row.dia) + color;
    },
    nextRollNumber(row, block) {
        const prefix = this.rollPrefix(row, block);
        const numbers = this.allRows().flatMap(currentRow => currentRow.rolls ?? []).map(roll => roll.number);
        let sequence = 1;
        let number = prefix + String(sequence).padStart(2, '0');
        while (numbers.includes(number)) {
            sequence++;
            number = prefix + String(sequence).padStart(2, '0');
        }
        return number;
    },
    distributeRollWeights(row, total) {
        const portions = split(total, row.rolls.length);
        row.rolls.forEach((roll, index) => {
            roll.weight = fromKg(portions[index], roll.unit);
        });
        this.updateRollTotal(row);
    },
    addRoll(row, block, blockIndex) {
        row.rolls ??= [];
        const total = safe(() => row.rolls.length
            ? sum(row.rolls.map(roll => toKg(roll.weight, roll.unit)))
            : decimal(row.returned_fabric_kg || '0').toString());
        row.rolls.push({number: this.nextRollNumber(row, block), weight: '', unit: 'kg', autoNumber: true, autoWeight: false, colorIndex: blockIndex});
        if (total !== null) {
            this.distributeRollWeights(row, total);
        } else {
            this.updateRollTotal(row);
        }
    },
    moveRoll(source, index, targetIndex) {
        const target = this.blocks[Number(targetIndex)]?.rows.find(row => String(row.dia) === String(source.dia));
        if (!target || target === source) return;
        if (!target.rolls?.length && safe(() => decimal(target.returned_fabric_kg || '0').gt('0'))) {
            alert('Record the existing returned weight as rolls before moving a roll into this color.');
            return;
        }
        target.rolls ??= [];
        const moved = source.rolls.splice(index, 1)[0];
        moved.colorIndex = Number(targetIndex);
        target.rolls.push(moved);
        this.updateRollTotal(source);
        if (!source.rolls.length) source.returned_fabric_kg = '0.000';
        this.updateRollTotal(target);
    },
    removeRoll(row, index) { row.rolls.splice(index, 1); this.updateRollTotal(row); },
    syncRollsToReturned(row) {
        if (!row.rolls?.length || safe(() => decimal(row.returned_fabric_kg).lt('0')) || safe(() => decimal(row.returned_fabric_kg).eq('0'))) return;
        if (row.rolls.length === 1) {
            row.rolls[0].weight = row.returned_fabric_kg;
            row.rolls[0].unit = 'kg';
            row.rolls[0].autoWeight = false;
            return;
        }
        this.distributeRollWeights(row, row.returned_fabric_kg);
    },
    updateRollTotal(row) {
        row.returned_fabric_kg = row.rolls.length ? (safe(() => sum(row.rolls.map(roll => toKg(roll.weight, roll.unit)))) ?? '') : '';
    },
    rowWaste(row) { return safe(() => waste(decimal(row.given_fabric_kg).toString(), row.returned_fabric_kg === null || row.returned_fabric_kg === '' ? null : decimal(row.returned_fabric_kg).toString())); },
    totals(rows) {
        const given = safe(() => sum(rows.map(r => decimal(r.given_fabric_kg).toString())));
        const returnedRows = rows.filter(r => r.returned_fabric_kg !== null && r.returned_fabric_kg !== '');
        const returned = returnedRows.length ? safe(() => sum(returnedRows.map(r => decimal(r.returned_fabric_kg).toString()))) : null;
        return {given, returned, waste: given !== null && returned !== null && returnedRows.length === rows.length ? safe(() => waste(given, returned)) : null};
    },
    allRows() { return this.blocks.flatMap(b => b.rows); },
    remaining(dia) {
        const given = safe(() => sum(this.allRows().filter(r => String(r.dia) === String(dia.dia)).map(r => decimal(r.given_fabric_kg).toString())));
        return given === null ? null : Big(dia.available_kg).minus(given).toString();
    },
    overAllocated() { return this.summary?.rows.some(d => safe(() => new Big(this.remaining(d)).lt('0'))); },
    excessReturn() { return this.allRows().some(r => safe(() => r.returned_fabric_kg !== null && r.returned_fabric_kg !== '' && decimal(r.returned_fabric_kg).gt(decimal(r.given_fabric_kg)))); },
    format,
}));

}
