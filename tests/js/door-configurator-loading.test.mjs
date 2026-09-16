import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const loaderSource = fs.readFileSync('resources/js/store/common/door-configurator-images.js', 'utf8');
const { createSceneImageLoader } = await import('data:text/javascript;base64,' + Buffer.from(loaderSource).toString('base64'));
const source = fs.readFileSync('resources/js/store/pages/store.door-configurator.page.js', 'utf8');
const tick = () => new Promise(resolve => setImmediate(resolve));
const deferred = () => { let resolve, reject; const promise = new Promise((yes, no) => { resolve = yes; reject = no; }); return { promise, resolve, reject }; };

function loaderFixture(options = {}) {
    const created = [], scheduled = [];
    class FakeImage {
        constructor() { created.push(this); this.decoded = deferred(); }
        decode() { return this.decoded.promise; }
        finish() { this.onload(); this.decoded.resolve(); }
    }
    return { created, scheduled, loader: createSceneImageLoader({ base: '/assets/', ImageClass: FakeImage, schedule: callback => scheduled.push(callback), ...options }) };
}

test('image loads deduplicate, wait for decoding and promote preloads to selected priority', async () => {
    const { loader, created } = loaderFixture();
    const first = loader.load('door.webp', 'low');
    assert.equal(loader.load('door.webp'), first);
    assert.equal(created.length, 1);
    assert.equal(created[0].fetchPriority, 'high');
    let ready = false; first.then(() => { ready = true; });
    created[0].onload(); await tick();
    assert.equal(ready, false);
    created[0].decoded.resolve();
    assert.equal(await first, created[0]);
    assert.equal(loader.load('door.webp'), first);
});

test('network/decode errors and timeouts allow retries without poisoned cached promises', async () => {
    const { loader, created } = loaderFixture({ timeoutMs: 20 });
    const failed = loader.load('door.webp');
    created[0].onerror();
    await assert.rejects(failed, /unavailable/);
    const badDecode = loader.load('door.webp');
    created[1].onload(); created[1].decoded.reject(new Error('decode'));
    await assert.rejects(badDecode, /decode/);
    await assert.rejects(loader.load('door.webp'), /timed out/);
    assert.equal(created[2].src, '');
    const retried = loader.load('door.webp'); created[3].finish();
    await retried;
    assert.equal(created.length, 4);
});

test('background work is bounded to two transfers, deduplicated and replaces stale queued models', async () => {
    const { loader, created, scheduled } = loaderFixture();
    loader.preload(['a', 'a', 'b', 'old-model']);
    assert.equal(created.length, 0); scheduled.shift()();
    assert.deepEqual(created.map(image => image.src), ['/assets/a', '/assets/b']);
    assert.ok(created.every(image => image.fetchPriority === 'low'));
    loader.preload(['new-model']);
    scheduled.shift()(); // Two current transfers still in progress.
    assert.equal(created.length, 2);
    created[0].finish(); await tick(); scheduled.shift()();
    assert.equal(created[2].src, '/assets/new-model');
    created[1].finish(); created[2].finish(); await tick();
    assert.ok(!created.some(image => image.src === '/assets/old-model'));
});

test('decoded cache evicts least recently used assets', async () => {
    const { loader, created } = loaderFixture({ maxEntries: 2 });
    for (const file of ['a', 'b', 'c']) { const loaded = loader.load(file); created.at(-1).finish(); await loaded; }
    const cached = loader.load('c'); await cached;
    assert.equal(created.length, 3);
    const reloaded = loader.load('a'); created.at(-1).finish(); await reloaded;
    assert.equal(created.length, 4);
});

function rendererFixture() {
    const elements = new Map(), pending = new Map(), committed = [];
    const $ = id => {
        if (!elements.has(id)) elements.set(id, { hidden: true, disabled: false, dataset: {}, setAttribute(name, value) { this[name] = value; } });
        return elements.get(id);
    };
    const context = vm.createContext({
        $, setTimeout, clearTimeout, committed,
        renderVersion: 0, rendered: false, hasScene: false, cartPending: false, sceneStatusTimer: undefined,
        state: { product: 'a', wall: '#ffffff', doorType: 'interior' },
        room: () => ({ id: 'living', image: 'room', door: [0, 0, 500], name: 'Room' }),
        product: () => ({ id: context.state.product, name: context.state.product, category: 'interior', crop: [0, 0, 400, 800] }),
        finish: () => ({ id: 'white', name: 'White' }), handle: () => null, t: text => text,
        previewDoorImage: p => p.id,
        loadImage: file => { if (!pending.has(file)) pending.set(file, deferred()); return pending.get(file).promise; },
        frame: {}, frameContext: { putImageData() {} },
        ctx: { drawImage() { committed.push(context.frame.product); } },
        canvas: $('room-canvas'), tintedRoom: () => ({}),
        drawDoor: (_, p) => { context.frame.product = p.id; },
        preloadNearbyDoors() {},
    });
    vm.runInContext(source.slice(source.indexOf('function syncSceneActions()'), source.indexOf('function toast(')), context);
    vm.runInContext(source.slice(source.indexOf('async function renderScene()'), source.indexOf('async function renderDetail(')), context);
    return { $, context, pending, committed, render: () => vm.runInContext('renderScene()', context) };
}

test('previous frame stays visible during a slow switch; stale completions never overwrite the last choice', async () => {
    const { $, context, pending, committed, render } = rendererFixture();
    const first = render();
    assert.equal($('scene-loading').hidden, false);
    pending.get('room').resolve({}); pending.get('a').resolve({}); await first;
    assert.deepEqual(committed, ['a']);
    context.state.product = 'b'; const second = render();
    assert.equal($('scene-loading').hidden, true);
    assert.equal($('save').disabled, true);
    assert.equal($('zoom').disabled, true);
    assert.equal($('order-selection').disabled, true);
    assert.deepEqual(committed, ['a']);
    context.state.product = 'c'; const third = render();
    pending.get('c').resolve({}); await third;
    pending.get('b').resolve({}); await second;
    assert.deepEqual(committed, ['a', 'c']);
    assert.equal($('room-canvas').dataset.product, 'c');
    assert.equal($('order-selection').disabled, false);
    assert.equal($('scene').getAttribute?.('aria-busy') ?? $('scene')['aria-busy'], 'false');
});

test('failed switch preserves the previous frame, exposes retry and keeps export/order disabled', async () => {
    const { $, context, pending, committed, render } = rendererFixture();
    const first = render(); pending.get('room').resolve({}); pending.get('a').resolve({}); await first;
    context.state.product = 'b'; const failed = render(); pending.get('b').reject(new Error('offline')); await failed;
    assert.deepEqual(committed, ['a']);
    assert.equal($('scene-loading').hidden, true);
    assert.equal($('scene-error').hidden, false);
    assert.equal($('order-selection').disabled, true);
    assert.equal($('save').disabled, true);
    pending.delete('b'); const retry = render(); pending.get('b').resolve({}); await retry;
    assert.deepEqual(committed, ['a', 'b']);
    assert.equal($('scene-error').hidden, true);
    assert.equal($('save').disabled, false);
});

test('rendering does not unlock an in-flight cart request', async () => {
    const { $, context, pending, render } = rendererFixture();
    context.cartPending = true;
    const first = render(); pending.get('room').resolve({}); pending.get('a').resolve({}); await first;
    assert.equal($('order-selection').disabled, true);
    assert.equal($('save').disabled, false);
});

test('background preloading respects data saving and prioritizes neighboring doors', () => {
    const calls = [], products = Array.from({ length: 10 }, (_, i) => ({ id: String(i), category: 'interior', colors: [{ id: 'white' }] }));
    const context = vm.createContext({
        navigator: { connection: { saveData: true } }, PRODUCTS: products, state: { product: '0', doorType: 'interior' },
        product: () => products[0], productsForType: () => products,
        previewDoorImage: p => p.id, imageLoader: { preload: files => calls.push(files) },
    });
    vm.runInContext(source.slice(source.indexOf('function preloadNearbyDoors()'), source.indexOf('function syncSceneActions()')), context);
    vm.runInContext('preloadNearbyDoors()', context); assert.equal(calls.length, 0);
    context.navigator.connection = { effectiveType: '2g' };
    vm.runInContext('preloadNearbyDoors()', context); assert.equal(calls.length, 0);
    context.navigator.connection = { effectiveType: '4g' };
    vm.runInContext('preloadNearbyDoors()', context); assert.deepEqual(Array.from(calls[0].slice(0, 2)), ['1', '9']);
});
