import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const source = fs.readFileSync('resources/js/store/pages/store.door-configurator.page.js', 'utf8');
const catalog = JSON.parse(fs.readFileSync('resources/data/door-configurator.json', 'utf8'));
catalog.products.push(...JSON.parse(fs.readFileSync('resources/data/door-configurator-interior.json', 'utf8')).products);
const logic = source.slice(source.indexOf('const DOOR_TYPES ='), source.indexOf('const DEFAULT_WALL ='));
const context = vm.createContext({ PRODUCTS: catalog.products, t: text => text });
vm.runInContext(logic, context);

test('all five types have prepared products and selection always has a valid finish', () => {
    let current = { product: 'new-york', color: 'anthracite', doorType: 'interior' };
    for (const type of ['hidden', 'mirror', 'classic', 'exterior', 'interior']) {
        context.current = current;
        context.type = type;
        current = vm.runInContext('withDoorType(current, type)', context);
        const product = catalog.products.find(p => p.id === current.product);
        assert.equal(current.doorType, type);
        assert.ok(product.colors.some(c => c.id === current.color));
    }
    assert.equal(current.product, 'new-york');
    assert.equal(current.color, 'anthracite');
});

test('invalid type cannot clear the current selection', () => {
    context.current = { product: 'ostin', color: 'white', doorType: 'interior' };
    assert.equal(vm.runInContext('withDoorType(current, "unknown")', context), context.current);
});

test('finish-specific crop and handle geometry do not leak into another finish', () => {
    const geometry = vm.createContext({});
    vm.runInContext(source.slice(source.indexOf('function doorGeometry('), source.indexOf('function drawDoor(')), geometry);
    geometry.product = { id: 'door', crop: [200, 0, 400, 837], handle: [.85, .5], handleSide: 'right' };
    geometry.finish = { crop: [195, 3, 410, 831], handle: [.17, .54], handleSide: 'left' };
    const visual = vm.runInContext('doorGeometry(product, finish)', geometry);
    assert.equal(visual.id, 'door');
    assert.deepEqual(Array.from(visual.crop), [195, 3, 410, 831]);
    assert.equal(visual.handleSide, 'left');
    assert.equal(geometry.product.handleSide, 'right');
    assert.equal(vm.runInContext('doorGeometry(product, {})', geometry).handleSide, 'right');
});

test('every literal DOM reference resolves in namespaced production markup', () => {
    const markup = fs.readFileSync('resources/views/pages/store/partials/door-studio.blade.php', 'utf8');
    for (const [, id] of source.matchAll(/\$\('([a-z][a-z-]*)'\)/g)) {
        assert.ok(markup.includes(`id="studio-${id}"`), id);
    }
    assert.ok(!markup.includes('id="cart-dialog"'));
});

test('all room and handle-free assets exist; unavailable images never use baked handles', () => {
    for (const [, image] of source.matchAll(/['"]([^'"\n]+\.webp)['"]/g)) {
        if (!image.includes('/')) assert.ok(fs.existsSync('public/assets/door-configurator/v1/' + image), image);
    }
    assert.ok(source.includes('if (!image) throw new Error'));
});

test('catalog expansion uses explicit reviewed previews with bounded geometry and lazy thumbnails', () => {
    const ids = new Set(), slugs = new Set();
    for (const product of catalog.products) {
        assert.ok(!ids.has(product.id) && !slugs.has(product.slug), `duplicate ${product.slug}`);
        ids.add(product.id); slugs.add(product.slug);
        assert.ok(product.crop.length === 4 && product.crop.every(Number.isFinite));
        assert.ok(product.crop[0] >= 0 && product.crop[1] >= 0);
        assert.ok(product.crop[2] > 0 && product.crop[3] > 0);
        assert.ok(product.crop[0] + product.crop[2] <= 801 && product.crop[1] + product.crop[3] <= 838);
        const colors = new Set();
        const catalogColors = new Set();
        for (const color of product.colors) {
            assert.ok(!colors.has(color.id), `duplicate finish ${product.id}/${color.id}`);
            assert.ok(!catalogColors.has(color.colorId), `duplicate catalog color ${product.id}/${color.colorId}`);
            colors.add(color.id);
            catalogColors.add(color.colorId);
            if (color.crop) {
                assert.ok(color.crop.length === 4 && color.crop.every(Number.isFinite));
                assert.ok(color.crop[0] >= 0 && color.crop[1] >= 0);
                assert.ok(color.crop[2] > 0 && color.crop[3] > 0);
                assert.ok(color.crop[0] + color.crop[2] <= 801 && color.crop[1] + color.crop[3] <= 838);
            }
            if (color.handle) assert.ok(color.handle.length === 2 && color.handle.every(value => value > 0 && value < 1));
            if (color.handleSide) assert.ok(['left', 'right'].includes(color.handleSide));
            for (const file of [color.image, color.preview].filter(Boolean)) {
                assert.match(file, /^[a-z0-9./-]+\.(?:webp|jpg)$/);
                assert.ok(!file.includes('..'));
                assert.ok(fs.existsSync('public/assets/door-configurator/v1/' + file), file);
            }
        }
    }
    assert.ok(source.includes('f.preview || HANDLE_FREE_IMAGES[f.image]'));
    assert.ok(source.includes('loading="lazy" decoding="async"'));
});

test('production uses server checkout and protects the base wall and mobile layout', () => {
    assert.ok(source.includes('handleBasket(data)'));
    assert.ok(source.includes('X-CSRF-TOKEN'));
    assert.ok(source.includes('request_id:pendingSelection.id'));
    assert.ok(!source.includes('bona-door-studio-cart'));
    assert.ok(source.includes("const DEFAULT_WALL = '#c2b8a8'"));
    assert.ok(!source.includes('#868c76\' }'), 'the olive wall colour stays out of the palette');
    assert.ok(source.includes("$('mobile-selection-footer').append($('intro-download'), $('scene-note'))"));
});

test('interior preparation ledger covers every model exactly once and excludes unresolved products', () => {
    const prepared = JSON.parse(fs.readFileSync('resources/data/door-configurator-interior.json', 'utf8')).products;
    const progress = JSON.parse(fs.readFileSync('docs/door-configurator-interior-progress.json', 'utf8'));
    const assets = JSON.parse(fs.readFileSync('docs/door-configurator-interior-assets.json', 'utf8')).assets;
    assert.equal(new Set(progress.models.map(model => model.slug)).size, progress.models.length);
    assert.equal(progress.models.length, progress.sourceCatalogModels);
    assert.equal(progress.newPrepared, prepared.length);
    assert.equal(progress.models.filter(model => model.status === 'prepared').length, prepared.length);
    assert.equal(new Set(assets.map(asset => `${asset.slug}/${asset.colorId}`)).size, assets.length);
    for (const product of prepared) {
        const tracked = progress.models.find(model => model.slug === product.slug);
        assert.equal(tracked?.status, 'prepared', product.slug);
        assert.ok(product.handle.length === 2 && product.handle.every(value => value > 0 && value < 1));
        for (const color of product.colors) {
            const asset = assets.find(asset => asset.slug === product.slug && asset.colorId === color.colorId);
            assert.equal(asset?.preview, color.preview, product.slug);
            assert.match(asset.sourceSha256, /^[a-f0-9]{64}$/);
            assert.match(asset.sourceUrl, /^https:\/\/bona-doors\.com\.ua\/storage\/product-images\//);
        }
    }
    for (const model of progress.models.filter(model => !['prepared', 'existing-preset'].includes(model.status))) {
        assert.ok(!catalog.products.some(product => product.slug === model.slug), model.slug);
    }
});

test('finish ledger and provenance remain deduplicated and match the prepared catalog', () => {
    const ledger = JSON.parse(fs.readFileSync('docs/door-configurator-finish-progress.json', 'utf8'));
    const assets = JSON.parse(fs.readFileSync('docs/door-configurator-interior-assets.json', 'utf8')).assets;
    const keys = new Set(), counts = {};
    for (const finish of ledger.finishes) {
        const key = `${finish.slug}/${finish.colorId}`;
        assert.ok(!keys.has(key), key);
        keys.add(key);
        counts[finish.status] = (counts[finish.status] || 0) + 1;
        const product = catalog.products.find(product => product.slug === finish.slug);
        const prepared = product?.colors.some(color => color.colorId === finish.colorId) ?? false;
        assert.equal(finish.status === 'prepared', prepared, key);
    }
    assert.deepEqual(counts, ledger.counts);
    for (const product of catalog.products) {
        for (const color of product.colors.filter(color => color.preview?.startsWith('interior/'))) {
            const asset = assets.find(asset => asset.slug === product.slug && asset.colorId === color.colorId);
            assert.equal(asset?.preview, color.preview);
            assert.match(asset.sourceSha256, /^[a-f0-9]{64}$/);
        }
    }
});

test('Molding 2B keeps the photographed molding finish bound to each paint colour', () => {
    const product = catalog.products.find(product => product.id === 'molding-2b');
    for (const [colorId, optionId] of [[168, 17107], [166, 17107], [301, 17108]]) {
        const color = product.colors.find(color => color.colorId === colorId);
        assert.deepEqual(color?.optionIds, [optionId], `Molding 2B/${colorId}`);
    }
});

test('ALUMINIUM LOFT keeps silver and bronze moldings matched to each photographed finish', () => {
    const optionsByModel = {
        'korfad-alp-07': { 251: 15663, 262: 15663, 261: 15664, 263: 15663 },
        'korfad-alp-03': { 251: 15655, 262: 15656, 261: 15655, 263: 15656 },
        'korfad-alp-02': { 251: 15648, 262: 15647, 261: 15648, 263: 15647 },
        'korfad-alp-01': { 251: 15689, 262: 15689, 261: 15690, 263: 15689 },
    };
    for (const [id, finishes] of Object.entries(optionsByModel)) {
        const product = catalog.products.find(product => product.id === id);
        assert.equal(product.colors.length, 4, `${id}: all four catalog shades`);
        for (const [colorId, optionId] of Object.entries(finishes)) {
            const color = product.colors.find(color => color.colorId === Number(colorId));
            assert.deepEqual(color?.optionIds, [optionId], `${id}/${colorId}`);
        }
    }
});

test('GLP-01 and DLP-01 cover their full palettes with the pictured glass or metal insert', () => {
    const glp = catalog.products.find(product => product.id === 'korfad-glp-01');
    assert.deepEqual(glp.colors.map(color => color.colorId).sort((a, b) => a - b), [261, 262, 268, 269, 272]);
    for (const color of glp.colors) assert.deepEqual(color.optionIds, [15705], `GLP-01/${color.colorId}: black Lacobel`);
    const dlp = catalog.products.find(product => product.id === 'korfad-dlp-01');
    assert.deepEqual(dlp.colors.map(color => color.colorId).sort((a, b) => a - b), [251, 261, 262, 263, 268, 269, 271, 272]);
    for (const color of dlp.colors) {
        assert.deepEqual(color.optionIds, [color.colorId === 272 ? 15720 : 15721], `DLP-01/${color.colorId}: black or gold insert`);
    }
});

test('LP-01 covers all seven catalog finishes without inventing glass or molding options', () => {
    const lp = catalog.products.find(product => product.id === 'korfad-lp-01');
    assert.deepEqual(lp.colors.map(color => color.colorId).sort((a, b) => a - b), [251, 254, 260, 261, 262, 263, 268]);
    for (const color of lp.colors) {
        assert.deepEqual(color.optionIds || [], [], `LP-01/${color.colorId}: plain slab`);
        assert.ok(color.preview.includes(`korfad-lp-01-${color.colorId}-clean`));
    }
});

test('WP-01 and CL-05 contain their full palettes and CL-05 retains the pictured white satin glass', () => {
    const wp = catalog.products.find(product => product.id === 'korfad-wp-01');
    const cl = catalog.products.find(product => product.id === 'korfad-classico-cl-05');
    assert.deepEqual(wp.colors.map(color => color.colorId).sort((a, b) => a - b), [258, 259]);
    assert.deepEqual(cl.colors.map(color => color.colorId).sort((a, b) => a - b), [254, 255, 256, 257, 258, 259, 260, 266]);
    for (const color of wp.colors) assert.deepEqual(color.optionIds || [], []);
    for (const color of cl.colors) assert.deepEqual(color.optionIds, [16332]);
});

test('CL-02 covers eight finishes with bronze glass only on the original bleached oak', () => {
    const cl = catalog.products.find(product => product.id === 'korfad-classico-cl-02');
    assert.deepEqual(cl.colors.map(color => color.colorId).sort((a, b) => a - b), [254, 255, 256, 257, 258, 259, 260, 266]);
    for (const color of cl.colors) {
        assert.deepEqual(color.optionIds, [color.colorId === 254 ? 16341 : 16340], `CL-02/${color.colorId}: pictured glass`);
    }
});

test('palette v2 moves the old white default and removed olive to warm cashmere once', () => {
    const start = source.indexOf('const DEFAULT_WALL =');
    const end = source.indexOf('const DEFAULT_HANDLE =');
    const context = vm.createContext({ t: text => text });
    vm.runInContext(source.slice(start, end) + ';globalThis.restoredWall = restoredWall; globalThis.PALETTE = PALETTE;', context);
    assert.equal(context.restoredWall({ wall: '#FFFFFF', wallPaletteVersion: 1 }), '#c2b8a8');
    assert.equal(context.restoredWall({ wall: '#868c76' }), '#c2b8a8');
    assert.equal(context.restoredWall({ wall: '#b38974', wallPaletteVersion: 1 }), '#b38974');
    assert.equal(context.restoredWall({ wall: '#ffffff', wallPaletteVersion: 2 }), '#ffffff');
    assert.equal(context.restoredWall({}), '#c2b8a8');
    assert.ok(!context.PALETTE.some(color => color.hex === '#868c76'));
    assert.equal(context.PALETTE.length, 6);
});

test('black handle is the initial choice while deliberate choices and unavailable stock are respected', () => {
    const logic = source.slice(source.indexOf('const DEFAULT_HANDLE ='), source.indexOf('const DEFAULT = {'));
    const handles = vm.createContext({ HANDLES: catalog.handles });
    vm.runInContext(logic + ';globalThis.restore = restoredHandle;', handles);
    assert.equal(handles.restore(null), 'black');
    assert.equal(handles.restore({ handle: null }), 'black');
    assert.equal(handles.restore({ handle: 'nickel' }), 'nickel');
    assert.equal(handles.restore({ handle: null, handleSelectionVersion: 1 }), null);
    assert.equal(handles.restore({ handle: 'removed' }), 'black');
    const unavailable = vm.createContext({ HANDLES: catalog.handles.filter(handle => handle.id !== 'black') });
    vm.runInContext(logic + ';globalThis.restore = restoredHandle;', unavailable);
    assert.equal(unavailable.restore(null), null, 'never substitute a different sellable handle silently');
});
