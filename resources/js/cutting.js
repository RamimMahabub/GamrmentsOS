import { Big, decimal, format, toKg } from './math.js';

export function cuttingPieces(row) {
    if (!row.quantity_mode) return row.pieces == null ? decimal(row.dozens).times('12') : decimal(row.pieces);
    if (!['dozen', 'pieces', 'dozen_pieces'].includes(row.quantity_mode)) throw new Error('Choose a quantity unit.');
    const dozens = row.quantity_mode === 'pieces' ? new Big('0') : decimal(row.quantity_dozens);
    const pieces = row.quantity_mode === 'dozen' ? new Big('0') : decimal(row.quantity_pieces);
    if (!dozens.eq(dozens.round(0)) || !pieces.eq(pieces.round(0)) || (row.quantity_mode === 'dozen_pieces' && pieces.gt('11'))) throw new Error('Enter whole dozens and pieces; extra pieces must be 0 to 11.');
    return dozens.times('12').plus(pieces);
}
export function quantityText(pieces) {
    const count = decimal(pieces);
    const dozens = count.div('12').round(0, 0);
    const remainder = count.minus(dozens.times('12'));
    return [dozens.gt('0') ? `${dozens} dozen` : '', remainder.gt('0') ? `${remainder} pc` : ''].filter(Boolean).join(' ') || '0 pc';
}
function quantityFields(pieces) {
    const count = decimal(pieces);
    if (!count.eq(count.round(0))) return {quantity_mode: '', pieces: count.toFixed(3), dozens: count.div('12').toFixed(3)};
    const dozens = count.div('12').round(0, 0);
    const remainder = count.minus(dozens.times('12'));
    return {quantity_mode: remainder.eq('0') ? 'dozen' : 'dozen_pieces', quantity_dozens: dozens.toString(), quantity_pieces: remainder.toString(), pieces: count.toFixed(3), dozens: count.div('12').toFixed(3)};
}
function availablePieces(roll) { return roll.pieces == null ? decimal(roll.dozens).times('12') : decimal(roll.pieces); }

export function cuttingInputWeight(row, roll) {
    const availableWeight = decimal(roll.weight_kg);
    if (!roll.source_body_row_id) return availableWeight.toFixed(3);
    const pieces = cuttingPieces(row);
    if (pieces.eq(availablePieces(roll))) return availableWeight.toFixed(3);
    const proportionalWeight = new Big(decimal(roll.body_weight_kg ?? roll.weight_kg).times(pieces).div(roll.body_pieces == null ? decimal(roll.body_dozens ?? roll.dozens).times('12') : decimal(roll.body_pieces)).toFixed(3));
    return (proportionalWeight.gt(availableWeight) ? availableWeight : proportionalWeight).toFixed(3);
}

export function cuttingRow(row, roll) {
    const pieces = roll.source_body_row_id ? cuttingPieces(row) : decimal(row.dozens).times('12');
    const dozens = pieces.div('12');
    const automatic = !String(row.weight_per_dozen ?? '').trim();
    const remaining = new Big(toKg(row.remaining_fabric || '0', row.weight_unit));
    const weight = new Big(cuttingInputWeight(row, roll));
    const cut = automatic ? weight.minus(remaining) : new Big(dozens.times(toKg(row.weight_per_dozen, row.weight_unit, 8)).toFixed(3));
    const perKg = automatic ? cut.times('12').div(pieces).toFixed(8) : toKg(row.weight_per_dozen, row.weight_unit, 8);
    const enteredWeight = automatic ? cut.times('12').div(pieces.times(row.weight_unit === 'lb' ? '0.45359237' : '1')).toFixed(6) : decimal(row.weight_per_dozen).toFixed(6);
    const scrap = weight.minus(cut).minus(remaining);
    const waste = scrap.plus(remaining);
    return { automatic, weight_per_dozen: enteredWeight, weight_per_dozen_kg: perKg, dozens: dozens.toFixed(3), pieces: pieces.toFixed(3), roll_weight_kg: weight.toFixed(3), cut_weight_kg: cut.toFixed(3), cutting_scrap_kg: scrap.toFixed(3), remaining_fabric_kg: remaining.toFixed(3), waste_kg: waste.toFixed(3), waste_percent: weight.eq('0') ? '0.00' : waste.times('100').div(weight).toFixed(2), invalid: dozens.lte('0') || (roll.source_body_row_id && pieces.gt(availablePieces(roll))) || (!automatic && decimal(row.weight_per_dozen).lte('0')) || (automatic && (new Big(perKg).gt('9999999999.99999999') || new Big(enteredWeight).gt('999999999999.999999'))) || cut.lt('0') || scrap.lt('0') };
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
    return [...groups].sort(([a],[b]) => a === 'Grand total' ? 1 : b === 'Grand total' ? -1 : a.localeCompare(b)).map(([label, totals]) => ({ label, ...totals, dozens: new Big(totals.pieces).div('12').toFixed(3), waste_percent: new Big(totals.roll_weight_kg).eq('0') ? '0.00' : new Big(totals.waste_kg).times('100').div(totals.roll_weight_kg).toFixed(2) }));
}
export function registerCutting(Alpine) {
    Alpine.data('cuttingForm', config => ({
        canRecordResults: true, isNeck: false, ...config, search: '', lot: '', color: '', dia: '',
        init() {
            if (this.isNeck) for (const row of this.rows) { if (!row.quantity_mode) Object.assign(row, quantityFields(cuttingPieces(row))); }
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
        dozens(rolls) { return rolls.reduce((total, roll) => total.plus(availablePieces(roll)), new Big('0')).div('12').toFixed(3); },
        availableQuantity(roll) { return quantityText(availablePieces(roll)); },
        availableTotal(rolls) { return quantityText(rolls.reduce((total, roll) => total.plus(availablePieces(roll)), new Big('0'))); },
        quantity(row) { try { return quantityText(cuttingPieces(row)); } catch { return '-'; } },
        syncQuantity(row) { try { row.pieces = cuttingPieces({...row, pieces: null}).toFixed(3); row.dozens = new Big(row.pieces).div('12').toFixed(3); } catch { /* Keep incomplete input editable. */ } },
        changeQuantityMode(row) {
            const pieces = decimal(row.pieces ?? '0');
            const fields = quantityFields(pieces);
            row.quantity_dozens = row.quantity_mode === 'dozen' && pieces.mod('12').gt('0') ? '' : (fields.quantity_dozens ?? '');
            row.quantity_pieces = row.quantity_mode === 'pieces' ? pieces.toString() : (fields.quantity_pieces ?? '0');
            this.syncQuantity(row);
        },
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
        newRow(roll) { return {roll_id: roll.id, source_body_row_id: roll.source_body_row_id, dozens: '', weight_per_dozen: '', weight_unit: 'kg', remaining_fabric: '0', ...(this.isNeck ? quantityFields(availablePieces(roll)) : {})}; },
        selected(id) { return this.rows.some(row => String(row.roll_id) === String(id)); },
        selectedRow(id) { return this.rows.find(row => String(row.roll_id) === String(id)); },
        remainingDozens(roll) { try { const remaining = availablePieces(roll).minus(cuttingPieces(this.selectedRow(roll.id))); return remaining.lt('0') ? '-' : remaining.div('12').toFixed(3); } catch { return '-'; } },
        remainingForRow(row) { return this.remainingQuantity(this.available.find(source => String(source.id) === String(row.roll_id))); },
        remainingQuantity(roll) { try { const remaining = availablePieces(roll).minus(cuttingPieces(this.selectedRow(roll.id))); return remaining.lt('0') ? 'Exceeds available quantity' : quantityText(remaining) + ' remain for later'; } catch { return 'Enter a valid quantity'; } },
        toggle(roll) { if (this.selected(roll.id)) this.rows = this.rows.filter(row => String(row.roll_id) !== String(roll.id)); else this.rows.push(this.newRow(roll)); },
        roll(row) { return this.available.find(roll => String(roll.id) === String(row.roll_id)) || {}; },
        autoWeight(row) { if (String(row.weight_per_dozen ?? '').trim()) return ''; try { return 'Auto: ' + cuttingRow(row, this.roll(row)).weight_per_dozen; } catch { return 'Auto from input weight'; } },
        values(row) { if (this.pending(row)) return {}; try { return cuttingRow(row, this.roll(row)); } catch { return {}; } },
        inputWeight(row) { try { return cuttingInputWeight(row, this.roll(row)); } catch { return '-'; } },
        get selectedBatches() {
            const groups = new Map();
            for (const row of this.rows) {
                const source = this.roll(row);
                if (!source.id) continue;
                const key = `${source.lot_id}:${source.color}:${source.dia}`;
                if (!groups.has(key)) groups.set(key, {key, lot: source.lot, color: source.color, hex: source.color_hex || '#686a74', dia: source.dia, count: 0, hasDozens: false, pieces: new Big('0')});
                const group = groups.get(key);
                group.count++;
                group.hasDozens ||= this.isNeck || String(row.dozens || '').trim() !== '';
                try { group.pieces = group.pieces.plus(this.isNeck ? cuttingPieces(row) : decimal(row.dozens || '0').times('12')); } catch { /* Incomplete quantities do not contribute yet. */ }
            }
            return [...groups.values()].map(group => ({...group, dozens: group.pieces.div('12').toFixed(3), quantity: quantityText(group.pieces)}));
        },
        get summary() { return cuttingSummary(this.rows.filter(row => !this.pending(row)), this.available); },
        get invalid() {
            if (!this.rows.length) return true;
            if (this.rows.some(row => !this.roll(row).id || (this.isNeck && String(row.source_body_row_id) !== String(this.roll(row).source_body_row_id)))) return true;
            if (this.isNeck && this.rows.some(row => {
                try { const quantity = cuttingPieces(row); return quantity.lte('0') || quantity.gt(availablePieces(this.roll(row))); } catch { return true; }
            })) return true;
            if (!this.canRecordResults) return false;
            return this.rows.some(row => {
                if (this.pending(row)) {
                    try { return decimal(row.remaining_fabric || '0').gt('0'); } catch { return true; }
                }
                try { return cuttingRow(row, this.roll(row)).invalid; } catch { return true; }
            });
        },
        pending(row) { return !this.isNeck && !String(row.dozens ?? '').trim() && !String(row.weight_per_dozen ?? '').trim(); },
        display: format,
    }));
}
