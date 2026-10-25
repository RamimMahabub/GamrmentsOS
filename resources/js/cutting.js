import { Big, decimal, format, toKg } from './math.js';

export function cuttingInputWeight(row, roll) {
    const availableWeight = decimal(roll.weight_kg);
    if (!roll.source_body_row_id) return availableWeight.toFixed(3);
    const dozens = decimal(row.dozens);
    if (dozens.eq(decimal(roll.dozens))) return availableWeight.toFixed(3);
    const proportionalWeight = new Big(decimal(roll.body_weight_kg ?? roll.weight_kg).times(dozens).div(decimal(roll.body_dozens ?? roll.dozens)).toFixed(3));
    return (proportionalWeight.gt(availableWeight) ? availableWeight : proportionalWeight).toFixed(3);
}

export function cuttingRow(row, roll) {
    const dozens = decimal(row.dozens);
    const perKg = toKg(row.weight_per_dozen, row.weight_unit, 8);
    const cut = new Big(dozens.times(perKg).toFixed(3));
    const remaining = new Big(toKg(row.remaining_fabric || '0', row.weight_unit));
    const weight = new Big(cuttingInputWeight(row, roll));
    const scrap = weight.minus(cut).minus(remaining);
    const waste = scrap.plus(remaining);
    return { dozens: dozens.toFixed(3), pieces: dozens.times('12').toFixed(3), roll_weight_kg: weight.toFixed(3), cut_weight_kg: cut.toFixed(3), cutting_scrap_kg: scrap.toFixed(3), remaining_fabric_kg: remaining.toFixed(3), waste_kg: waste.toFixed(3), waste_percent: weight.eq('0') ? '0.00' : waste.times('100').div(weight).toFixed(2), invalid: dozens.lte('0') || (roll.source_body_row_id && dozens.gt(decimal(roll.dozens))) || decimal(row.weight_per_dozen).lte('0') || scrap.lt('0') };
}
export function cuttingSummary(rows, available) {
    const groups = new Map();
    const add = (key, values) => {
        const total = groups.get(key) || Object.fromEntries(['dozens','pieces','roll_weight_kg','cut_weight_kg','cutting_scrap_kg','remaining_fabric_kg','waste_kg'].map(key => [key, '0.000']));
        for (const key of Object.keys(total)) total[key] = new Big(total[key]).plus(values[key]).toFixed(3);
        groups.set(key, total);
    };
    for (const row of rows) {
        const roll = available.find(roll => String(roll.id) === String(row.roll_id));
        if (!roll) continue;
        try { const values = cuttingRow(row, roll); add(`${roll.lot ? roll.lot + ' / ' : ''}${roll.color} / DIA ${roll.dia}`, values); add('Grand total', values); } catch { /* Incomplete inputs have no preview yet. */ }
    }
    return [...groups].sort(([a],[b]) => a === 'Grand total' ? 1 : b === 'Grand total' ? -1 : a.localeCompare(b)).map(([label, totals]) => ({ label, ...totals, waste_percent: new Big(totals.roll_weight_kg).eq('0') ? '0.00' : new Big(totals.waste_kg).times('100').div(totals.roll_weight_kg).toFixed(2) }));
}
export function registerCutting(Alpine) {
    Alpine.data('cuttingForm', config => ({
        canRecordResults: true, isNeck: false, ...config, search: '', lot: '', color: '', dia: '',
        init() {
            const first = this.available.find(roll => this.selected(roll.id));
            if (first) this.lot = String(first.lot_id);
        },
        changeLot() { this.search = ''; this.color = ''; this.dia = ''; },
        get lotRolls() { return this.available.filter(roll => String(roll.lot_id) === this.lot); },
        get selectedLot() { return this.lots.find(item => String(item.id) === this.lot); },
        get filtered() { return this.lotRolls.filter(roll => (!this.search || `${roll.number} ${roll.color} ${roll.dia}`.toLowerCase().includes(this.search.toLowerCase())) && (!this.color || roll.color === this.color) && (!this.dia || String(roll.dia) === this.dia)); },
        get lots() { return [...new Map(this.available.map(roll => [roll.lot_id, {id: roll.lot_id, name: roll.lot}])).values()]; },
        get colors() { return [...new Set(this.lotRolls.map(roll => roll.color))].sort(); },
        get dias() { return [...new Set(this.lotRolls.filter(roll => !this.color || roll.color === this.color).map(roll => String(roll.dia)))].sort((a,b) => Number(a) - Number(b)); },
        get colorGroups() {
            const colors = new Map();
            for (const roll of this.filtered) {
                if (!colors.has(roll.color)) colors.set(roll.color, new Map());
                const dias = colors.get(roll.color);
                const key = String(roll.dia);
                if (!dias.has(key)) dias.set(key, []);
                dias.get(key).push(roll);
            }
            return [...colors].sort(([a],[b]) => a.localeCompare(b)).map(([color,dias]) => {
                const groups = [...dias].sort(([a],[b]) => Number(a) - Number(b)).map(([dia,rolls]) => ({dia, rolls: rolls.sort((a,b) => a.number.localeCompare(b.number, undefined, {numeric:true}))}));
                return {color, hex: groups[0].rolls[0].color_hex || '#686a74', dias: groups, rolls: groups.flatMap(group => group.rolls)};
            });
        },
        weight(rolls) { return rolls.reduce((total, roll) => total.plus(roll.weight_kg), new Big('0')).toFixed(3); },
        dozens(rolls) { return rolls.reduce((total, roll) => total.plus(roll.dozens || '0'), new Big('0')).toFixed(3); },
        selectedCount(rolls) { return rolls.filter(roll => this.selected(roll.id)).length; },
        allSelected(rolls) { return rolls.length > 0 && this.selectedCount(rolls) === rolls.length; },
        partiallySelected(rolls) { const count = this.selectedCount(rolls); return count > 0 && count < rolls.length; },
        selectRolls(rolls, checked) {
            if (checked) {
                for (const roll of rolls) {
                    if (!this.selected(roll.id)) this.rows.push(this.newRow(roll));
                }
            } else {
                const ids = new Set(rolls.map(roll => String(roll.id)));
                this.rows = this.rows.filter(row => !ids.has(String(row.roll_id)));
            }
        },
        newRow(roll) { return {roll_id: roll.id, source_body_row_id: roll.source_body_row_id, dozens: this.isNeck ? roll.dozens : '', weight_per_dozen: '', weight_unit: 'kg', remaining_fabric: '0'}; },
        selected(id) { return this.rows.some(row => String(row.roll_id) === String(id)); },
        selectedRow(id) { return this.rows.find(row => String(row.roll_id) === String(id)); },
        remainingDozens(roll) { try { const remaining = decimal(roll.dozens).minus(decimal(this.selectedRow(roll.id)?.dozens || '0')); return remaining.lt('0') ? '-' : remaining.toFixed(3); } catch { return '-'; } },
        toggle(roll) { if (this.selected(roll.id)) this.rows = this.rows.filter(row => String(row.roll_id) !== String(roll.id)); else this.rows.push(this.newRow(roll)); },
        roll(row) { return this.available.find(roll => String(roll.id) === String(row.roll_id)) || {}; },
        values(row) { if (this.pending(row)) return {}; try { return cuttingRow(row, this.roll(row)); } catch { return {}; } },
        inputWeight(row) { try { return cuttingInputWeight(row, this.roll(row)); } catch { return '-'; } },
        get selectedBatches() {
            const groups = new Map();
            for (const row of this.rows) {
                const source = this.roll(row);
                if (!source.id) continue;
                const key = `${source.lot_id}:${source.color}:${source.dia}`;
                if (!groups.has(key)) groups.set(key, {key, lot: source.lot, color: source.color, hex: source.color_hex || '#686a74', dia: source.dia, count: 0, hasDozens: false, dozens: new Big('0')});
                const group = groups.get(key);
                group.count++;
                group.hasDozens ||= this.isNeck || String(row.dozens || '').trim() !== '';
                try { group.dozens = group.dozens.plus(decimal(row.dozens || '0')); } catch { /* Incomplete quantities do not contribute yet. */ }
            }
            return [...groups.values()].map(group => ({...group, dozens: group.dozens.toFixed(3)}));
        },
        get summary() { return cuttingSummary(this.rows.filter(row => !this.pending(row)), this.available); },
        get invalid() {
            if (!this.rows.length) return true;
            if (this.rows.some(row => !this.roll(row).id || (this.isNeck && String(row.source_body_row_id) !== String(this.roll(row).source_body_row_id)))) return true;
            if (this.isNeck && this.rows.some(row => {
                try { const quantity = decimal(row.dozens); return quantity.lte('0') || quantity.gt(decimal(this.roll(row).dozens)); } catch { return true; }
            })) return true;
            if (!this.canRecordResults) return false;
            return this.rows.some(row => {
                if (this.pending(row)) {
                    try { return decimal(row.remaining_fabric || '0').gt('0'); } catch { return true; }
                }
                try { return cuttingRow(row, this.roll(row)).invalid; } catch { return true; }
            });
        },
        pending(row) { return (this.isNeck || !String(row.dozens ?? '').trim()) && !String(row.weight_per_dozen ?? '').trim(); },
        display: format,
    }));
}
