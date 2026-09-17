import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';

const sent = [];
const listeners = {};
const storage = new Map();
let consent = true;

globalThis.window = {
    localStorage: { getItem: (key) => storage.get(key) ?? null, setItem: (key, value) => storage.set(key, String(value)) },
    addEventListener: (name, callback) => { listeners[name] = callback; },
};
globalThis.document = { querySelectorAll: () => [], querySelector: () => null };

const stubConsent = `
    export function hasAnalyticsConsent() { return globalThis.__consent(); }
    export function trackGoogleEvent(name, parameters) { globalThis.__sent.push([name, parameters]); }
`;
globalThis.__consent = () => consent;
globalThis.__sent = sent;

const consentUrl = 'data:text/javascript;base64,' + Buffer.from(stubConsent).toString('base64');
const source = fs.readFileSync('resources/js/store/common/analytics.js', 'utf8').replace("'./cookie-consent'", `'${consentUrl}'`);
const analytics = await import('data:text/javascript;base64,' + Buffer.from(source).toString('base64'));

const line = (id, count, price = 1000, name = 'Door') => ({ line_id: id, count, analytics: { item_id: `door-${id}`, item_name: name, price, quantity: count } });

test('first cart load is only a baseline; later changes become add_to_cart and remove_from_cart with deltas', () => {
    sent.length = 0;
    analytics.syncCartAnalytics([line(1, 1)]);
    assert.equal(sent.length, 0);

    analytics.syncCartAnalytics([line(1, 3), line(2, 1, 250, 'Handle')]);
    assert.deepEqual(sent.map(([name]) => name), ['add_to_cart']);
    assert.deepEqual(sent[0][1].items.map((item) => [item.item_id, item.quantity]), [['door-1', 2], ['door-2', 1]]);
    assert.equal(sent[0][1].value, 2250);
    assert.equal(sent[0][1].currency, 'UAH');

    analytics.syncCartAnalytics([line(2, 1, 250)]);
    assert.equal(sent[1][0], 'remove_from_cart');
    assert.deepEqual(sent[1][1].items.map((item) => [item.item_id, item.quantity]), [['door-1', 3]]);
    assert.equal(sent[1][1].value, 3000);

    analytics.trackViewCart({ cartPage: true });
    analytics.trackViewCart({ cartPage: true });
    assert.equal(sent.filter(([name]) => name === 'view_cart').length, 1);
});

test('events raised before consent are replayed once consent is given, and once-events never repeat', () => {
    sent.length = 0;
    consent = false;
    analytics.sendEcommerceEvent('purchase', { transaction_id: 'BD-000042' }, 'purchase-42');
    assert.equal(sent.length, 0);

    analytics.default.init();
    consent = true;
    listeners['bona:cookie-consent']({ detail: { choice: 'all' } });
    assert.deepEqual(sent.map(([name]) => name), ['purchase']);

    analytics.sendEcommerceEvent('purchase', { transaction_id: 'BD-000042' }, 'purchase-42');
    assert.equal(sent.length, 1, 'a reloaded thank-you page must not report the purchase twice');
});
