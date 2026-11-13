import test from 'node:test';
import assert from 'node:assert/strict';
import { registerSewing } from '../../resources/js/sewing.js';

const factories = {};
registerSewing({
    data(name, factory) {
        factories[name] = factory;
    }
});

function createSewingForm(initial) {
    return factories['sewingForm'](initial);
}

test('sewingForm filters batches by lot, DIA, and search keyword', () => {
    const available = [
        { group_key: '1:BLACK:38', lot: 'SU-061126', color: 'BLACK', dia: 38, dozens: '44.000', pieces: '528.000' },
        { group_key: '1:BLACK:40', lot: 'SU-061126', color: 'BLACK', dia: 40, dozens: '32.000', pieces: '384.000' },
        { group_key: '2:WHITE:32', lot: 'SU-071126', color: 'WHITE', dia: 32, dozens: '22.000', pieces: '264.000' },
    ];
    const form = createSewingForm({ available, rows: [], step: 'fold' });

    assert.equal(form.lots.length, 2);
    assert.deepEqual(form.dias, ['32', '38', '40']);
    assert.deepEqual(form.colors, ['BLACK', 'WHITE']);

    form.diaFilter = '38';
    assert.equal(form.filteredAvailable.length, 1);
    assert.equal(form.filteredAvailable[0].group_key, '1:BLACK:38');

    form.diaFilter = '';
    form.search = 'white';
    assert.equal(form.filteredAvailable.length, 1);
    assert.equal(form.filteredAvailable[0].color, 'WHITE');

    form.search = '';
    form.lotFilter = 'SU-061126';
    assert.equal(form.filteredAvailable.length, 2);
});

test('sewingForm select-all and toggleAll support indeterminate and toggle', () => {
    const available = [
        { group_key: '1:BLACK:38', lot: 'SU-061126', color: 'BLACK', dia: 38, dozens: '44.000', pieces: '528.000' },
        { group_key: '1:BLACK:40', lot: 'SU-061126', color: 'BLACK', dia: 40, dozens: '32.000', pieces: '384.000' },
    ];
    const form = createSewingForm({ available, rows: [], step: 'fold' });

    assert.equal(form.allSelected(form.filteredAvailable), false);
    assert.equal(form.partiallySelected(form.filteredAvailable), false);

    // Select all visible
    form.toggleAll(form.filteredAvailable, true);
    assert.equal(form.rows.length, 2);
    assert.equal(form.allSelected(form.filteredAvailable), true);
    assert.equal(form.partiallySelected(form.filteredAvailable), false);
    assert.equal(form.rows[0].fold_dozens, '44.000');

    // Deselect one
    form.toggle(available[0]);
    assert.equal(form.rows.length, 1);
    assert.equal(form.allSelected(form.filteredAvailable), false);
    assert.equal(form.partiallySelected(form.filteredAvailable), true);

    // Deselect all
    form.toggleAll(form.filteredAvailable, false);
    assert.equal(form.rows.length, 0);
    assert.equal(form.allSelected(form.filteredAvailable), false);
    assert.equal(form.partiallySelected(form.filteredAvailable), false);
});

test('sewingForm formats available labels and defaults into integer dozen and remainder pieces', () => {
    const available = [
        { group_key: '1:BLACK:32', lot: 'SU-061126', color: 'BLACK', dia: 32, dozens: 9, pieces: 8, total_pieces: 116 },
        { group_key: '1:BLACK:34', lot: 'SU-061126', color: 'BLACK', dia: 34, dozens: 18, pieces: 10, total_pieces: 226 },
        { group_key: '1:BLACK:36', lot: 'SU-061126', color: 'BLACK', dia: 36, dozens: 20, pieces: 0, total_pieces: 240 },
        { group_key: '1:BLACK:38', lot: 'SU-061126', color: 'BLACK', dia: 38, dozens: 0, pieces: 5, total_pieces: 5 },
    ];
    const form = createSewingForm({ available, rows: [], step: 'iron' });

    assert.equal(form.availableLabel(available[0]), '9 dozen + 8 pieces');
    assert.equal(form.availableLabel(available[1]), '18 dozen + 10 pieces');
    assert.equal(form.availableLabel(available[2]), '20 dozen');
    assert.equal(form.availableLabel(available[3]), '5 pieces');

    // When selecting row with 116 pieces (9 doz + 8 pcs)
    form.toggle(available[0]);
    assert.equal(form.rows.length, 1);
    assert.equal(form.rows[0].iron_dozens, 9);
    assert.equal(form.rows[0].iron_pieces, 8);

    // When toggleAll is used, all rows get their respective dozen and remainder pieces
    form.toggleAll(available, true);
    assert.equal(form.rows.length, 4);
    assert.equal(form.rows[1].iron_dozens, 18);
    assert.equal(form.rows[1].iron_pieces, 10);
    assert.equal(form.rows[2].iron_dozens, 20);
    assert.equal(form.rows[2].iron_pieces, 0);
    assert.equal(form.rows[3].iron_dozens, 0);
    assert.equal(form.rows[3].iron_pieces, 5);
});

test('sewingForm handles QC phases tracking passed, altered, and rejected pieces', () => {
    const available = [
        { group_key: '1:BLACK:38', lot: 'SU-061126', color: 'BLACK', dia: 38, dozens: '10.000', pieces: '120.000' },
    ];
    const form = createSewingForm({ available, rows: [], step: 'qc_phase_1' });

    assert.equal(form.isQc, true);

    form.toggle(available[0]);
    assert.equal(form.rows.length, 1);
    const row = form.rows[0];

    // Altered / reject fields are initialized
    assert.equal(row.qc_phase_1_altered_pieces, 0);
    assert.equal(row.qc_phase_1_rejected_pieces, 0);

    // Row is valid because dozens is set
    assert.equal(form.invalid(row), false);

    // If passed is 0, but user records altered or rejected, row is valid
    row.qc_phase_1_dozens = 0;
    row.qc_phase_1_pieces = 0;
    assert.equal(form.invalid(row), true);

    row.qc_phase_1_rejected_pieces = 2;
    assert.equal(form.invalid(row), false);

    row.qc_phase_1_rejected_pieces = 0;
    row.qc_phase_1_altered_pieces = 5;
    assert.equal(form.invalid(row), false);

    // totalReleased computes passed dozens * 12 + pieces
    row.qc_phase_1_dozens = 2;
    row.qc_phase_1_pieces = 4;
    assert.equal(form.totalReleased(row), (2 * 12) + 4);
});

test('sewingForm syncQcPassed updates passed dozen and pieces on the left when altered or rejected change', () => {
    // 240 pieces available (20 dozen)
    const available = [
        { group_key: '1:BLACK:40', lot: 'SU-061126', color: 'BLACK', dia: 40, dozens: 20, pieces: 0, total_pieces: 240 },
        { group_key: '1:BLACK:32', lot: 'SU-061126', color: 'BLACK', dia: 32, dozens: 9, pieces: 8, total_pieces: 116 },
    ];
    const form = createSewingForm({ available, rows: [], step: 'qc_phase_1' });

    // Select the 240-piece batch
    form.toggle(available[0]);
    const row240 = form.rows[0];
    assert.equal(row240.qc_phase_1_dozens, 20);
    assert.equal(row240.qc_phase_1_pieces, 0);

    // Operator enters Altered: 2, Rejected: 5
    // 240 - (2 + 5) = 233 pieces = 19 dozen and 5 pieces
    row240.qc_phase_1_altered_pieces = 2;
    row240.qc_phase_1_rejected_pieces = 5;
    form.syncQcPassed(row240, available[0]);

    assert.equal(row240.qc_phase_1_dozens, 19);
    assert.equal(row240.qc_phase_1_pieces, 5);

    // Operator tests with simulated DOM event from Altered input
    form.syncQcPassed(row240, available[0], { target: { name: 'rows[0][qc_phase_1_altered_pieces]', value: '0' } });
    // 240 - (0 + 5) = 235 pieces = 19 dozen and 7 pieces
    assert.equal(row240.qc_phase_1_dozens, 19);
    assert.equal(row240.qc_phase_1_pieces, 7);

    // When both are 0, it resets back to full 20 dozen
    row240.qc_phase_1_altered_pieces = 0;
    row240.qc_phase_1_rejected_pieces = 0;
    form.syncQcPassed(row240, available[0]);
    assert.equal(row240.qc_phase_1_dozens, 20);
    assert.equal(row240.qc_phase_1_pieces, 0);

    // Now test with 116 pieces (9 dozen + 8 pieces)
    form.toggle(available[1]);
    const row116 = form.rows[1];
    assert.equal(row116.qc_phase_1_dozens, 9);
    assert.equal(row116.qc_phase_1_pieces, 8);

    // Operator enters Altered: 6, Rejected: 4
    // 116 - (6 + 4) = 106 pieces = 8 dozen and 10 pieces
    row116.qc_phase_1_altered_pieces = 6;
    row116.qc_phase_1_rejected_pieces = 4;
    form.syncQcPassed(row116, available[1]);
    assert.equal(row116.qc_phase_1_dozens, 8);
    assert.equal(row116.qc_phase_1_pieces, 10);
});


