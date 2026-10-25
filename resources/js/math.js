import Big from 'big.js';

Big.DP = 30;
Big.RM = 1;
export const FACTOR = '0.45359237';
export function decimal(value) {
    const text = String(value ?? '').trim();
    if (!/^(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d+)?$/.test(text)) throw new Error('Enter a number, for example 2,500.125.');
    return new Big(text.replaceAll(',', ''));
}
export function format(value, places = 3, trim = false) {
    if (value === null || value === undefined || value === '') return '—';
    try {
        const text = new Big(String(value)).toFixed(places, 1);
        return trim ? text.replace(/(\.\d*?)0+$/, '$1').replace(/\.$/, '') : text;
    } catch { return '—'; }
}
export function toKg(value, unit, scale = 3) { return decimal(value).times(unit === 'lb' ? FACTOR : '1').toFixed(scale, 1); }
export function fromKg(value, unit) { return new Big(String(value)).div(unit === 'lb' ? FACTOR : '1').toString(); }
export function priceToKg(value, unit) { return decimal(value).div(unit === 'lb' ? FACTOR : '1').toFixed(6, 1); }
export function priceFromKg(value, unit) { return new Big(String(value)).times(unit === 'lb' ? FACTOR : '1').toString(); }
export function sum(values) { return values.reduce((total, value) => total.plus(String(value ?? '0')), new Big('0')).toString(); }
export function output(input, waste) {
    const p = decimal(waste);
    if (p.gt('100')) throw new Error('Waste must be between 0 and 100.');
    return new Big(String(input)).times(new Big('1').minus(p.div('100'))).toFixed(3, 1);
}
export function waste(given, returned) {
    if (returned === null || returned === '' || new Big(String(given)).eq('0')) return null;
    return new Big(String(given)).minus(String(returned)).times('100').div(String(given)).toFixed(2, 1);
}
export function split(available, count) {
    if (count < 1) return [];
    const portion = new Big(String(available)).div(String(count)).toFixed(3, 0);
    const values = Array(count).fill(portion);
    values[count - 1] = new Big(String(available)).minus(new Big(portion).times(String(count - 1))).toFixed(3);
    return values;
}
export { Big };
