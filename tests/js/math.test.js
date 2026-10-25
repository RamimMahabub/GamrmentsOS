import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import { toKg, fromKg, priceToKg, priceFromKg, output, waste, split, sum, Big, decimal } from '../../resources/js/math.js';
const fixtures = JSON.parse(fs.readFileSync(new URL('../Fixtures/calculations.json',import.meta.url),'utf8'));
test('browser and server reference conversion fixtures agree', () => {
    fixtures.conversions.forEach(c => {
        assert.equal(toKg(c.value,c.unit),c.kg);
        assert.equal(toKg(fromKg(c.kg,c.unit),c.unit),c.kg);
    });
});
test('unit switches preserve the canonical price', () => {
    const stored=priceToKg('2','lb');
    assert.equal(stored,'4.409245');
    assert.equal(priceToKg(priceFromKg(stored,'lb'),'lb'),stored);
});
test('bag factors and knitting waste match the owner examples', () => {
    assert.equal(new Big('5').times('45.35923700').toFixed(3,1),'226.796');
    fixtures.waste.forEach(c => assert.equal(output(c.input,c.percent),c.output));
});
test('six color and sub-gram splits never over allocate', () => {
    assert.deepEqual(split('226.796',6),['37.799','37.799','37.799','37.799','37.799','37.801']);
    assert.equal(sum(split('226.796',6)),'226.796');
    assert.deepEqual(split('0.002',3),['0.000','0.000','0.002']);
});
test('pending returns, zero given and added dye weight are safe', () => {
    assert.equal(waste('37.799','35.15'),'7.01');
    assert.equal(waste('0','0'),null);
    assert.equal(waste('100',null),null);
    assert.equal(waste('100','110'),'-10.00');
});
test('paste parsing rejects malformed separators and negatives', () => {
    assert.equal(decimal('2,500.125').toString(),'2500.125');
    assert.throws(()=>decimal('2,50.1')); assert.throws(()=>decimal('-1'));
});
