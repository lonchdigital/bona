import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';

const source = fs.readFileSync('resources/js/store/common/cookie-consent.js', 'utf8');

async function boot(savedChoice) {
    const storage = new Map(savedChoice ? [['bona_cookie_consent_v1', savedChoice]] : []);
    const listeners = {};
    const cookies = ['_ga=GA1.1.1', '_ga_0863474309=GS1', 'bona_session=keep'];
    const banner = {
        hidden: true,
        dataset: { googleAnalyticsId: 'G-0863474309' },
        classList: { add() {}, remove() {} },
        querySelector: (selector) => ({ addEventListener: (_, callback) => { listeners[selector] = callback; }, focus() {} }),
    };
    const appended = [];

    globalThis.window = {
        localStorage: { getItem: (key) => storage.get(key) ?? null, setItem: (key, value) => storage.set(key, value) },
        location: { hostname: 'bona-doors.com.ua', reload() { throw new Error('no reload expected'); } },
        requestAnimationFrame: (callback) => callback(),
        setTimeout: () => 0,
        dispatchEvent() {},
    };
    globalThis.CustomEvent = class { constructor(type, init) { this.type = type; this.detail = init?.detail; } };
    globalThis.document = {
        querySelector: (selector) => selector === '[data-cookie-consent]' ? banner : null,
        querySelectorAll: () => [],
        createElement: () => ({ dataset: {} }),
        head: { appendChild: (node) => appended.push(node) },
        get cookie() { return cookies.join('; '); },
        set cookie(value) {
            const name = value.split('=')[0];
            if (/Max-Age=0/.test(value)) {
                const index = cookies.findIndex((cookie) => cookie.startsWith(name + '='));
                if (index !== -1) cookies.splice(index, 1);
            }
        },
    };

    const module = await import('data:text/javascript;base64,' + Buffer.from(source + `\n//${Math.random()}`).toString('base64'));
    module.default.init();

    const commands = () => window.dataLayer.map((args) => [...args]);

    return { module, listeners, banner, appended, cookies, commands };
}

test('the Google tag loads for every visitor after a denied default', async () => {
    const { commands, appended, banner } = await boot(null);
    const log = commands();

    assert.equal(log[0][0], 'consent');
    assert.equal(log[0][1], 'default');
    assert.equal(log[0][2].analytics_storage, 'denied');
    assert.equal(log[0][2].ad_storage, 'denied');
    assert.ok(log.findIndex((c) => c[0] === 'config') > 0, 'config comes after the default');
    assert.ok(appended[0].src.includes('googletagmanager.com/gtag/js?id=G-0863474309'));
    assert.equal(banner.hidden, false, 'the banner still asks for a choice');
});

test('a stored acceptance is restored before config, and events are sent without consent', async () => {
    const { module, commands } = await boot('all');
    const log = commands();
    const updateAt = log.findIndex((c) => c[0] === 'consent' && c[1] === 'update');
    const configAt = log.findIndex((c) => c[0] === 'config');

    assert.equal(log[updateAt][2].analytics_storage, 'granted');
    assert.ok(updateAt < configAt);

    module.trackGoogleEvent('submit_form_call_master');
    assert.deepEqual(commands().slice(-2).map((c) => [c[0], c[1]]), [['event', 'submit_form_call_master'], ['event', 'generate_lead']]);
});

test('choosing necessary cookies only denies storage and removes existing GA cookies without a reload', async () => {
    const { listeners, commands, cookies } = await boot('all');
    listeners['[data-cookie-consent-necessary]']();

    const last = commands().at(-1);
    assert.deepEqual([last[0], last[1], last[2].analytics_storage], ['consent', 'update', 'denied']);
    assert.deepEqual(cookies, ['bona_session=keep']);
});
