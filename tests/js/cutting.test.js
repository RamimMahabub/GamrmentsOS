import test from 'node:test';
import assert from 'node:assert/strict';
import { cuttingInputWeight, cuttingRow, cuttingSummary, registerCutting, cuttingPieces, quantityText } from '../../resources/js/cutting.js';
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

test('neck batches reserve partial dozens and preview proportional body weight', () => {
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
 form.rows[0].quantity_dozens = '999';
 assert.equal(form.invalid, true);
 form.rows[0].quantity_dozens = '5';
 assert.equal(form.invalid, false);
 assert.equal(form.values(form.rows[0]).roll_weight_kg, '0.700');
 assert.equal(form.values(form.rows[0]).cut_weight_kg, '0.650');
 assert.equal(form.values(form.rows[0]).cutting_scrap_kg, '0.050');
 assert.equal(form.selectedBatches[0].dozens, '5.000');
 assert.equal(form.remainingDozens(source), '7.000');
 form.rows[0].quantity_dozens = '12';
 assert.equal(form.values(form.rows[0]).cut_weight_kg, '1.560');
 assert.equal(form.values(form.rows[0]).cutting_scrap_kg, '0.120');
 assert.equal(form.summary[0].label, 'GR-091026 / Black / DIA 28');
 assert.equal(form.summary[0].dozens, '12.000');
 form.rows[0].weight_per_dozen = '0.141';
 assert.equal(form.invalid, true);
 form.rows[0].source_body_row_id = 999;
 assert.equal(form.invalid, true);
});

test('partial neck reservations validate quantities before results and preserve them through group selection', () => {
 const source = {id: 1, source_body_row_id: 10, lot_id: 2, lot: 'LOT', color: 'Black', dia: 28, number: 'R-1', dozens: '15.000', weight_kg: '0.900'};
 const second = {...source, id: 2, source_body_row_id: 11, number: 'R-2'};
 const form = cuttingFactories.cuttingForm({isNeck: true, available: [source, second], rows: [], canRecordResults: false});
 form.toggle(source);
 for (const dozens of ['', '0', '-1', '15.001']) {
  form.rows[0].quantity_dozens = dozens;
  assert.equal(form.invalid, true);
 }
 form.rows[0].quantity_dozens = '5';
 form.selectRolls([source, second], true);
 assert.equal(form.rows[0].quantity_dozens, '5');
 assert.equal(form.inputWeight(form.rows[0]), '0.300');
 assert.equal(form.remainingDozens(source), '10.000');
 assert.equal(form.invalid, false);
 assert.equal(form.pending(form.rows[0]), true);
});

test('partial neck input preserves the final weight remainder and supports tiny allocations', () => {
 const final = {id: 1, source_body_row_id: 5, dozens: '1.000', weight_kg: '0.334', body_dozens: '3.000', body_weight_kg: '1.000'};
 const row = {roll_id: 1, source_body_row_id: 5, dozens: '1', weight_per_dozen: '0.2', weight_unit: 'kg', remaining_fabric: '0'};
 assert.equal(cuttingInputWeight(row, final), '0.334');
 assert.equal(cuttingRow(row, final).cutting_scrap_kg, '0.134');
 assert.equal(cuttingInputWeight({...row, dozens: '0.5'}, final), '0.167');
 const tiny = {...final, weight_kg: '0.000', body_weight_kg: '0.001'};
 const result = cuttingRow({...row, weight_per_dozen: '0.000001'}, tiny);
 assert.equal(result.waste_percent, '0.00');
 assert.equal(result.invalid, false);
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


test('combined dozen and pieces reserve 124 exact pieces and leave 4 dozen 8 pc', () => {
 const source = {id: 1, source_body_row_id: 10, pieces: '180.000', body_pieces: '180.000', dozens: '15.000', body_dozens: '15.000', weight_kg: '0.900', body_weight_kg: '0.900', lot_id: 2, lot: 'LOT', color: 'Black', dia: 28, number: 'R-1'};
 const form = cuttingFactories.cuttingForm({isNeck: true, available: [source], rows: [], canRecordResults: true});
 form.toggle(source);
 const row = form.rows[0];
 row.quantity_mode = 'dozen_pieces'; form.changeQuantityMode(row);
 row.quantity_dozens = '10'; row.quantity_pieces = '4'; form.syncQuantity(row);
 assert.equal(cuttingPieces(row).toString(), '124');
 assert.equal(form.quantity(row), '10 dozen 4 pc');
 assert.equal(form.remainingQuantity(source), '4 dozen 8 pc remain for later');
 assert.equal(form.remainingForRow(row), '4 dozen 8 pc remain for later');
 assert.equal(form.inputWeight(row), '0.620');
 assert.equal(form.invalid, false);
 row.weight_per_dozen = '0.05';
 assert.equal(form.values(row).cut_weight_kg, '0.517');
 assert.equal(form.values(row).pieces, '124.000');
 assert.equal(form.selectedBatches[0].quantity, '10 dozen 4 pc');
 row.quantity_mode = 'pieces'; form.changeQuantityMode(row);
 assert.equal(row.quantity_pieces, '124');
 assert.equal(form.quantity(row), '10 dozen 4 pc');
 row.quantity_mode = 'dozen_pieces'; form.changeQuantityMode(row);
 assert.equal(row.quantity_dozens, '10'); assert.equal(row.quantity_pieces, '4');
 for (const bad of ['12', '-1', '0.5', '', '999']) {
  row.quantity_pieces = bad; assert.equal(form.invalid, true);
 }
 row.quantity_pieces = '4'; form.syncQuantity(row);
 row.quantity_mode = 'dozen'; form.changeQuantityMode(row);
 assert.equal(row.quantity_dozens, ''); assert.equal(form.invalid, true);
 row.quantity_dozens = '10'; form.syncQuantity(row);
 assert.equal(form.quantity(row), '10 dozen'); assert.equal(form.invalid, false);
});

test('piece totals round only after summing and exact quantities survive editing', () => {
 const source = {id: 1, source_body_row_id: 10, pieces: '12.000', dozens: '1.000', weight_kg: '1.000', lot_id: 2, lot: 'LOT', color: 'Black', dia: 28, number: 'R-1'};
 const saved = {roll_id: 1, source_body_row_id: 10, pieces: '1.000', dozens: '0.083', weight_per_dozen: '0.12', weight_unit: 'kg', remaining_fabric: '0'};
 const form = cuttingFactories.cuttingForm({isNeck: true, available: [source], rows: [saved]});
 form.init();
 assert.equal(saved.quantity_dozens, '0'); assert.equal(saved.quantity_pieces, '1');
 assert.equal(form.quantity(saved), '1 pc'); assert.equal(form.remainingQuantity(source), '11 pc remain for later');
 const available = Array.from({length: 12}, (_, i) => ({...source, id: i + 1}));
 const rows = available.map(roll => ({...saved, roll_id: roll.id}));
 const total = cuttingSummary(rows, available).at(-1);
 assert.equal(total.pieces, '12.000'); assert.equal(total.dozens, '1.000');
 assert.equal(quantityText('56'), '4 dozen 8 pc');
 assert.equal(quantityText('0'), '0 pc');
 const legacy = {...saved, pieces: null, dozens: '0.001', quantity_mode: ''};
 assert.equal(cuttingPieces(legacy).toString(), '0.012');
});
