import test from 'node:test';
import assert from 'node:assert/strict';
import { cuttingRow, cuttingSummary, registerCutting } from '../../resources/js/cutting.js';
const roll = {id: 1, weight_kg: '10.000', color: 'Navy', dia: 32};
test('cutting kg components reconcile and decimals remain exact', () => {
 const row = cuttingRow({dozens: '2.5',weight_per_dozen: '2',weight_unit:'kg',remaining_fabric:'1'},roll);
 assert.equal(row.cut_weight_kg,'5.000'); assert.equal(row.cutting_scrap_kg,'4.000'); assert.equal(row.waste_kg,'5.000'); assert.equal(row.pieces,'30.000'); assert.equal(row.invalid,false);
});
test('lb conversion, empty remaining and overweight preview', () => {
 const row = {roll_id: 1,dozens:'2',weight_per_dozen:'2',weight_unit:'lb',remaining_fabric:''};
 assert.equal(cuttingRow(row,roll).cut_weight_kg,'1.814'); assert.equal(cuttingRow(row,roll).remaining_fabric_kg,'0.000');
 assert.equal(cuttingRow({...row,weight_unit:'kg',dozens:'5',remaining_fabric:'0.001'},roll).invalid,true);
 const totals=cuttingSummary([row],[roll]); assert.equal(totals[0].label,'Navy / DIA 32'); assert.equal(totals[1].label,'Grand total'); assert.equal(totals[1].waste_kg,'8.186');
});

const cuttingFactories = {};
registerCutting({data(name, factory) { cuttingFactories[name] = factory; }});

test('lot picker groups colors and numeric DIAs and preserves existing input through group selection', () => {
 const available = [
  {id: 1, lot_id: 10, lot: 'LOT-A', color: 'Navy', dia: 40, number: 'R-10', weight_kg: '1.100'},
  {id: 2, lot_id: 10, lot: 'LOT-A', color: 'Navy', dia: 8, number: 'R-2', weight_kg: '2.200'},
  {id: 3, lot_id: 10, lot: 'LOT-A', color: 'Navy', dia: 8, number: 'R-1', weight_kg: '3.300'},
  {id: 4, lot_id: 10, lot: 'LOT-A', color: 'Black', dia: 32, number: 'R-4', weight_kg: '4.400'},
  {id: 5, lot_id: 20, lot: 'LOT-B', color: 'Golden', dia: 50, number: 'R-5', weight_kg: '5.500'},
 ];
 const existing = {roll_id: 2, dozens: '2.5', weight_per_dozen: '0.5', weight_unit: 'kg', remaining_fabric: '0'};
 const form = cuttingFactories.cuttingForm({available, rows: [existing]});
 form.init();
 assert.equal(form.lot, '10');
 assert.deepEqual(form.colorGroups.map(group => group.color), ['Black', 'Navy']);
 const navy = form.colorGroups[1];
 assert.deepEqual(navy.dias.map(group => group.dia), ['8', '40']);
 assert.deepEqual(navy.dias[0].rolls.map(roll => roll.number), ['R-1', 'R-2']);
 assert.equal(form.weight(navy.rolls), '6.600');
 assert.equal(form.partiallySelected(navy.rolls), true);
 form.selectRolls(navy.rolls, true);
 form.selectRolls(navy.rolls, true);
 assert.equal(form.rows.length, 3);
 assert.equal(form.rows.find(row => row.roll_id === 2), existing);
 assert.equal(form.allSelected(navy.rolls), true);
 form.lot = '20'; form.changeLot();
 assert.deepEqual(form.colors, ['Golden']);
 form.selectRolls(form.filtered, true);
 form.lot = '10'; form.changeLot();
 form.selectRolls(navy.dias[0].rolls, false);
 assert.deepEqual(form.rows.map(row => row.roll_id).sort(), [1, 5]);
 form.search = 'R-10';
 assert.equal(form.filtered.length, 1);
 assert.equal(form.colorGroups[0].dias[0].dia, '40');
});

test('rolls can be reserved before results; edit accepts blank pairs and rejects incomplete results', () => {
 const available = [{...roll, lot_id: 1, lot: 'LOT-1', number: 'R-1'}];
 const form = cuttingFactories.cuttingForm({available, rows: [], canRecordResults: false});
 assert.equal(form.invalid, true);
 form.toggle(available[0]);
 assert.equal(form.invalid, false);
 form.canRecordResults = true;
 assert.equal(form.invalid, false);
 assert.deepEqual(form.summary, []);
 form.rows[0].dozens = '2';
 assert.equal(form.invalid, true);
 form.rows[0].weight_per_dozen = '2';
 assert.equal(form.invalid, false);
 assert.equal(form.summary[0].cut_weight_kg, '4.000');
 form.rows[0].dozens = ''; form.rows[0].weight_per_dozen = '';
 form.rows[0].remaining_fabric = '1';
 assert.equal(form.invalid, true);
});

test('neck batches inherit body dozens and use body output weight for previews', () => {
 const source = {id: 9, source_body_row_id: 25, lot_id: 6, lot: 'GR-091026', color: 'Black', color_hex: '#111111', dia: 28, number: 'R-9', dozens: '12.000', weight_kg: '1.680'};
 const form = cuttingFactories.cuttingForm({isNeck: true, available: [source], rows: [], canRecordResults: false});
 form.toggle(source);
 assert.equal(form.rows[0].source_body_row_id, 25);
 assert.equal(form.rows[0].dozens, '12.000');
 assert.equal(form.invalid, false);
 assert.equal(form.pending(form.rows[0]), true);
 assert.deepEqual(form.summary, []);
 assert.equal(form.selectedBatches[0].dozens, '12.000');
 form.canRecordResults = true;
 form.rows[0].weight_per_dozen = '0.13';
 form.rows[0].dozens = '999';
 assert.equal(form.invalid, false);
 assert.equal(form.values(form.rows[0]).cut_weight_kg, '1.560');
 assert.equal(form.values(form.rows[0]).cutting_scrap_kg, '0.120');
 assert.equal(form.summary[0].label, 'GR-091026 / Black / DIA 28');
 assert.equal(form.summary[0].dozens, '12.000');
 form.rows[0].weight_per_dozen = '0.141';
 assert.equal(form.invalid, true);
 form.rows[0].source_body_row_id = 999;
 assert.equal(form.invalid, true);
});

test('summary and origin cards keep identical colors and DIAs in different lots separate', () => {
 const available = [{...roll, lot_id: 10, lot: 'LOT-A', number: 'A'}, {...roll, id: 2, lot_id: 20, lot: 'LOT-B', number: 'B'}];
 const rows = available.map(source => ({roll_id: source.id, dozens: '2', weight_per_dozen: '2', weight_unit: 'kg', remaining_fabric: '0'}));
 const form = cuttingFactories.cuttingForm({available, rows});
 assert.equal(form.selectedBatches.length, 2);
 assert.equal(form.summary.length, 3);
 assert.equal(form.summary[0].label, 'LOT-A / Navy / DIA 32');
 assert.equal(form.summary[1].label, 'LOT-B / Navy / DIA 32');
 assert.equal(form.summary[2].dozens, '4.000');
});
