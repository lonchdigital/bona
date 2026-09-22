import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const source = fs.readFileSync('resources/js/store/common/wish-list.js', 'utf8')
    .replace(/^import .*;\n/gm, '')
    .replace('export default', 'const wishList =');

function fixture() {
    const element = (id = '') => ({
        attrs: { id }, classes: new Set(), text: '', disabled: false,
        label: { attrs: {}, classes: new Set(), text: '' },
    });
    const hearts = [element('first'), element('second'), element('first')];
    const badges = [element(), element()];
    const requests = [];
    const listeners = {};
    const document = {};

    class Selection {
        constructor(nodes) { this.nodes = nodes; this.length = nodes.length; }
        each(fn) { this.nodes.forEach(node => fn.call(node)); return this; }
        filter(fn) { return new Selection(this.nodes.filter(node => fn.call(node))); }
        attr(key, value) {
            if (value === undefined) return this.nodes[0]?.attrs[key];
            return this.each(function () { this.attrs[key] = value; });
        }
        removeAttr(key) { return this.each(function () { delete this.attrs[key]; }); }
        prop(key, value) { return this.each(function () { this[key] = value; }); }
        hasClass(name) { return this.nodes[0]?.classes.has(name) || false; }
        toggleClass(name, active) {
            return this.each(function () { active ? this.classes.add(name) : this.classes.delete(name); });
        }
        addClass(name) { return this.toggleClass(name, true); }
        removeClass(name) { return this.toggleClass(name, false); }
        text(value) {
            // Match jQuery's real collection behavior: reading concatenates
            // every matched badge, including the hidden mobile badge.
            if (value === undefined) return this.nodes.map(node => node.text).join('');
            return this.each(function () { this.text = String(value); });
        }
        find(selector) {
            return new Selection(selector === '[data-wish-list-label]' ? this.nodes.map(node => node.label) : []);
        }
        closest() { return new Selection([]); }
        on(event, selector, fn) { listeners[selector] = fn; return this; }
    }

    const $ = value => {
        if (typeof value !== 'string') return new Selection([value]);
        if (value === '.art-main-wishlist-count') return new Selection(badges);
        if (value === '.link-heart[id], .product-wish-list-button[id]') return new Selection(hearts);
        throw new Error(`Unexpected selector: ${value}`);
    };
    $.ajax = options => {
        const request = { options, success() {}, failure() {} };
        requests.push(request);
        const result = {
            done(fn) { request.success = fn; return result; },
            fail(fn) { request.failure = fn; return result; },
        };
        return result;
    };
    const context = vm.createContext({
        $, document, window: { addEventListener() {} },
        translations: { add_to_wish_list: 'До обраного', remove_from_wish_list: 'Прибрати з обраного' },
        routes: { wish_list: { products_slugs_route: '/slugs', product_add_route: '/PRODUCT_SLUG/add', product_delete_route: '/PRODUCT_SLUG/delete' } },
        csrf: 'test',
    });
    vm.runInContext(source, context);
    const click = index => listeners['.link-heart, .product-wish-list-button'].call(hearts[index], { preventDefault() {} });
    const resolve = slugs => requests.at(-1).success({ data: { slugs } });
    const count = () => badges.map(badge => badge.text);
    const init = () => vm.runInContext('wishList.init()', context);
    return { hearts, badges, requests, context, click, resolve, count, init };
}

test('one initial request synchronizes both badges and all copies of a heart', () => {
    const f = fixture();
    f.init();
    assert.equal(f.requests.length, 1);
    f.resolve(['first']);
    assert.deepEqual(f.count(), ['1', '1']);
    assert.equal(f.hearts[0].attrs['aria-pressed'], 'true');
    assert.equal(f.hearts[2].attrs['aria-pressed'], 'true');
    assert.equal(f.hearts[1].attrs['aria-pressed'], 'false');
});

test('adding and removing use actual unique slugs, never concatenated badge text', () => {
    const f = fixture();
    f.init(); f.resolve(['first']);
    f.click(1);
    f.requests.at(-1).success({ data: { success: true } });
    f.resolve(['first', 'second', 'second']);
    assert.deepEqual(f.count(), ['2', '2']);
    f.click(0);
    f.requests.at(-1).success({ data: { success: true } });
    f.resolve(['second']);
    assert.deepEqual(f.count(), ['1', '1']);
    f.click(1);
    f.requests.at(-1).success({ data: { success: true } });
    f.resolve([]);
    assert.deepEqual(f.count(), ['', '']);
    assert.ok(f.badges.every(badge => badge.classes.has('d-none')));
});

test('repeated clicks and duplicate buttons cannot send duplicate requests while pending', () => {
    const f = fixture();
    f.init(); f.resolve([]);
    f.click(0); f.click(0); f.click(2);
    assert.equal(f.requests.length, 2);
    assert.ok(f.hearts[0].disabled && f.hearts[2].disabled);
    f.requests.at(-1).success({ data: { success: true } });
    assert.ok(!f.hearts[0].disabled && !f.hearts[2].disabled);
    f.resolve(['first']);
    assert.deepEqual(f.count(), ['1', '1']);
});

test('failed mutation restores both active classes, labels and enabled state', () => {
    for (const failedResponse of [true, false]) {
        const f = fixture();
        f.init(); f.resolve(['first']);
        f.click(0);
        if (failedResponse) f.requests.at(-1).success({ data: { success: false } });
        else f.requests.at(-1).failure();
        assert.equal(f.hearts[0].attrs['aria-pressed'], 'true');
        assert.ok(f.hearts[0].classes.has('is-active'));
        assert.ok(f.hearts[0].classes.has('link-heart-active'));
        assert.equal(f.hearts[0].label.text, 'Прибрати з обраного');
        assert.equal(f.hearts[0].disabled, false);
        assert.deepEqual(f.count(), ['1', '1']);
    }
});

test('successful removal clears the server-rendered is-active class and visible label', () => {
    const f = fixture();
    f.init(); f.resolve(['first']);
    f.click(0);
    f.requests.at(-1).success({ data: { success: true } });
    f.resolve([]);
    for (const heart of [f.hearts[0], f.hearts[2]]) {
        assert.equal(heart.classes.has('is-active'), false);
        assert.equal(heart.classes.has('link-heart-active'), false);
        assert.equal(heart.attrs['aria-pressed'], 'false');
        assert.equal(heart.label.text, 'До обраного');
    }
});

test('a stale initial read cannot overwrite a click or a newer refresh', () => {
    const f = fixture();
    f.init();
    const stale = f.requests[0];
    f.click(0);
    stale.success({ data: { slugs: [] } });
    assert.equal(f.hearts[0].attrs['aria-pressed'], 'true');
    f.requests.at(-1).success({ data: { success: true } });
    f.resolve(['first']);
    stale.success({ data: { slugs: [] } });
    assert.deepEqual(f.count(), ['1', '1']);
});

test('unavailable or malformed read never clears an existing wish list', () => {
    const f = fixture();
    f.init(); f.resolve(['first']);
    for (const data of [undefined, {}, { data: { slugs: 'bad' } }, { data: { slugs: [null] } }]) {
        vm.runInContext('markActiveHearts()', f.context);
        f.requests.at(-1).success(data);
        assert.deepEqual(f.count(), ['1', '1']);
    }
    vm.runInContext('markActiveHearts()', f.context);
    f.requests.at(-1).failure();
    assert.deepEqual(f.count(), ['1', '1']);
});
