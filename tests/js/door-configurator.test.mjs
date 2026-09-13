import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const source = fs.readFileSync('resources/js/store/pages/store.door-configurator.page.js', 'utf8');
const catalog = JSON.parse(fs.readFileSync('resources/data/door-configurator.json', 'utf8'));
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

test('production uses server checkout and protects the base wall and mobile layout', () => {
    assert.ok(source.includes('handleBasket(data)'));
    assert.ok(source.includes('X-CSRF-TOKEN'));
    assert.ok(source.includes('request_id:pendingSelection.id'));
    assert.ok(!source.includes('bona-door-studio-cart'));
    assert.ok(source.includes("const DEFAULT_WALL = '#ffffff'"));
    assert.ok(source.includes("$('mobile-selection-footer').append($('intro-download'), $('scene-note'))"));
});
