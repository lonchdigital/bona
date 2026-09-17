import { hasAnalyticsConsent, trackGoogleEvent } from './cookie-consent';

// GA4 recommended e-commerce events. Payload items come from the server
// (App\Support\Analytics\GoogleAnalyticsCommerce) so both storefront languages
// report the same product names, prices and categories.

const CURRENCY = 'UAH';
const ONCE_PREFIX = 'bona_ga_once_';

let cartLines = null;
let pendingEvents = [];
let cartPageViewed = false;

function round(value) {
    return Math.round((Number(value) || 0) * 100) / 100;
}

function withValue(items) {
    return {
        currency: CURRENCY,
        value: round(items.reduce((sum, item) => sum + (Number(item.price) || 0) * (Number(item.quantity) || 1), 0)),
        items,
    };
}

function onceKey(key) {
    return key ? ONCE_PREFIX + key : null;
}

function alreadySent(key) {
    if (!key) return false;
    try {
        return window.localStorage.getItem(key) === '1';
    } catch (error) {
        return false;
    }
}

function markSent(key) {
    if (!key) return;
    try {
        window.localStorage.setItem(key, '1');
    } catch (error) {
        // Storage can be blocked; GA4 still de-duplicates purchases by transaction_id.
    }
}

export function sendEcommerceEvent(name, parameters, once = null) {
    const key = onceKey(once);
    if (alreadySent(key)) return;

    // Events raised before the visitor accepts cookies on this page are kept
    // and sent if consent is given before leaving it.
    if (!hasAnalyticsConsent()) {
        pendingEvents.push([name, parameters, key]);
        return;
    }

    trackGoogleEvent(name, parameters);
    markSent(key);
}

export function syncCartAnalytics(products) {
    const next = new Map((Array.isArray(products) ? products : []).map((product) => [
        String(product.line_id),
        { count: Number.parseInt(product.count, 10) || 0, item: product.analytics || null },
    ]));

    if (cartLines !== null) {
        const added = [];
        const removed = [];

        next.forEach((line, id) => {
            const delta = line.count - (cartLines.get(id)?.count || 0);
            if (delta > 0 && line.item) added.push({ ...line.item, quantity: delta });
        });

        cartLines.forEach((line, id) => {
            const delta = line.count - (next.get(id)?.count || 0);
            if (delta > 0 && line.item) removed.push({ ...line.item, quantity: delta });
        });

        if (added.length) sendEcommerceEvent('add_to_cart', withValue(added));
        if (removed.length) sendEcommerceEvent('remove_from_cart', withValue(removed));
    }

    cartLines = next;
}

export function trackViewCart({ cartPage = false } = {}) {
    if (!cartLines || cartLines.size === 0) return;
    if (cartPage && cartPageViewed) return;
    if (cartPage) cartPageViewed = true;

    const items = [...cartLines.values()]
        .filter((line) => line.item)
        .map((line) => ({ ...line.item, quantity: line.count }));

    if (items.length) sendEcommerceEvent('view_cart', withValue(items));
}

export function readPagePayload(eventName) {
    const script = document.querySelector(`script[type="application/json"][data-ga-event="${eventName}"]`);
    if (!script) return null;

    try {
        return JSON.parse(script.textContent || 'null');
    } catch (error) {
        return null;
    }
}

function initPageEvents() {
    document.querySelectorAll('script[type="application/json"][data-ga-event]').forEach((script) => {
        const payload = readPagePayload(script.dataset.gaEvent);
        if (payload) sendEcommerceEvent(script.dataset.gaEvent, payload, script.dataset.gaOnce || null);
    });

    window.addEventListener('bona:cookie-consent', (event) => {
        if (event.detail?.choice !== 'all' || !pendingEvents.length) return;

        const queued = pendingEvents;
        pendingEvents = [];
        queued.forEach(([name, parameters, key]) => {
            if (alreadySent(key)) return;
            trackGoogleEvent(name, parameters);
            markSent(key);
        });
    });
}

export default { init: initPageEvents };
