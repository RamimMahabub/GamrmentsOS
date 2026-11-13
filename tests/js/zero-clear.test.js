import test from 'node:test';
import assert from 'node:assert/strict';
import { isZero, isZeroEligibleInput, clearIfZero, restoreIfEmpty } from '../../resources/js/zero-clear.js';

function createMockInput(options = {}) {
    const events = [];
    const attrs = new Map();
    if (options.placeholder !== undefined) attrs.set('placeholder', options.placeholder);

    return {
        tagName: 'INPUT',
        type: options.type ?? 'number',
        name: options.name ?? 'test_quantity',
        id: options.id ?? 'test_id',
        value: options.value ?? '0',
        readOnly: options.readOnly ?? false,
        disabled: options.disabled ?? false,
        dataset: { ...(options.dataset ?? {}) },
        events,
        hasAttribute(key) { return attrs.has(key); },
        getAttribute(key) { return attrs.get(key); },
        setAttribute(key, val) { attrs.set(key, String(val)); },
        removeAttribute(key) { attrs.delete(key); },
        dispatchEvent(event) { events.push(event.type); return true; },
    };
}

test('isZero accurately identifies zero values', () => {
    assert.equal(isZero('0'), true);
    assert.equal(isZero('0.0'), true);
    assert.equal(isZero('0.00'), true);
    assert.equal(isZero('0.000'), true);
    assert.equal(isZero(' 0 '), true);
    assert.equal(isZero(' 0.000 '), true);

    assert.equal(isZero('1'), false);
    assert.equal(isZero('18'), false);
    assert.equal(isZero('0.5'), false);
    assert.equal(isZero('01'), false);
    assert.equal(isZero(''), false);
    assert.equal(isZero('   '), false);
    assert.equal(isZero(null), false);
    assert.equal(isZero(undefined), false);
});

test('isZeroEligibleInput accepts numeric/quantity inputs and excludes text/dates/checkboxes', () => {
    assert.equal(isZeroEligibleInput(createMockInput({ type: 'number' })), true);
    assert.equal(isZeroEligibleInput(createMockInput({ type: 'text', name: 'pieces' })), true);

    // Excluded types
    assert.equal(isZeroEligibleInput(createMockInput({ type: 'date' })), false);
    assert.equal(isZeroEligibleInput(createMockInput({ type: 'checkbox' })), false);
    assert.equal(isZeroEligibleInput(createMockInput({ type: 'search' })), false);
    assert.equal(isZeroEligibleInput(createMockInput({ type: 'password' })), false);
    assert.equal(isZeroEligibleInput(createMockInput({ readOnly: true })), false);
    assert.equal(isZeroEligibleInput(createMockInput({ disabled: true })), false);

    // Excluded names
    assert.equal(isZeroEligibleInput(createMockInput({ name: 'q' })), false);
    assert.equal(isZeroEligibleInput(createMockInput({ name: 'staff_name' })), false);
    assert.equal(isZeroEligibleInput(createMockInput({ name: 'hand_note' })), false);
    assert.equal(isZeroEligibleInput(createMockInput({ name: 'phone' })), false);
});

test('clearIfZero clears zero input, preserves placeholder, and dispatches input event', () => {
    const input = createMockInput({ value: '0', placeholder: '0' });

    clearIfZero(input);

    assert.equal(input.value, '');
    assert.equal(input.dataset.zeroCleared, '0');
    assert.equal(input.dataset.savedPlaceholder, '0');
    assert.equal(input.hasAttribute('placeholder'), false);
    assert.ok(input.events.includes('input'));
});

test('clearIfZero does NOT clear non-zero inputs', () => {
    const input = createMockInput({ value: '18', placeholder: '0' });

    clearIfZero(input);

    assert.equal(input.value, '18');
    assert.equal(input.dataset.zeroCleared, undefined);
    assert.equal(input.getAttribute('placeholder'), '0');
    assert.equal(input.events.length, 0);
});

test('restoreIfEmpty restores cleared zero value and placeholder when left empty', () => {
    const input = createMockInput({ value: '0', placeholder: '0' });
    clearIfZero(input);

    assert.equal(input.value, '');

    // User does not type anything and clicks elsewhere (blur / focusout)
    restoreIfEmpty(input);

    assert.equal(input.value, '0');
    assert.equal(input.getAttribute('placeholder'), '0');
    assert.equal(input.dataset.zeroCleared, undefined);
    assert.ok(input.events.includes('input'));
    assert.ok(input.events.includes('change'));
});

test('restoreIfEmpty preserves exact multi-decimal zero format like 0.000', () => {
    const input = createMockInput({ type: 'text', name: 'given_fabric_kg', value: '0.000' });
    clearIfZero(input);

    assert.equal(input.value, '');
    assert.equal(input.dataset.zeroCleared, '0.000');

    // Blur without typing
    restoreIfEmpty(input);

    assert.equal(input.value, '0.000');
    assert.equal(input.dataset.zeroCleared, undefined);
});

test('restoreIfEmpty does not overwrite if user entered a value', () => {
    const input = createMockInput({ value: '0' });
    clearIfZero(input);

    // User typed 25
    input.value = '25';

    restoreIfEmpty(input);

    assert.equal(input.value, '25');
    assert.equal(input.dataset.zeroCleared, undefined);
});

