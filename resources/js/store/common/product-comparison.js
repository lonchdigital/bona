const BUTTON_SELECTOR = '[data-product-compare]';
const STORAGE_KEY = 'bona-compared-products';
const DEFAULT_MAX_PRODUCTS = 4;

let comparedProducts = new Set();
let maxProducts = DEFAULT_MAX_PRODUCTS;
let comparisonDock = null;
let comparisonPage = null;

function parseStoredProducts(value)
{
    if (!Array.isArray(value)) {
        return [];
    }

    return [...new Set(value.filter(item => typeof item === 'string' && item))]
        .slice(0, maxProducts);
}

function readComparedProducts()
{
    try {
        return new Set(parseStoredProducts(JSON.parse(window.localStorage.getItem(STORAGE_KEY) || '[]')));
    } catch (error) {
        return new Set();
    }
}

function readPageProducts()
{
    if (!comparisonPage) {
        return [];
    }

    try {
        return parseStoredProducts(JSON.parse(comparisonPage.dataset.comparisonSelected || '[]'));
    } catch (error) {
        return [];
    }
}

function persistComparedProducts()
{
    try {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify([...comparedProducts]));
    } catch (error) {
        // Comparison still works for the current page when storage is blocked.
    }
}

function comparisonBaseUrl()
{
    return comparisonPage?.dataset.comparisonBaseUrl
        || comparisonDock?.dataset.comparisonBaseUrl
        || document.querySelector('[data-comparison-link]')?.getAttribute('href')
        || '/compare';
}

function comparisonUrl()
{
    const url = new URL(comparisonBaseUrl(), window.location.origin);
    const slugs = [...comparedProducts];

    if (slugs.length) {
        url.searchParams.set('products', slugs.join(','));
    } else {
        url.searchParams.delete('products');
    }

    return `${url.pathname}${url.search}${url.hash}`;
}

function setButtonState(button, active)
{
    button.classList.toggle('is-active', active);
    button.setAttribute('aria-pressed', active ? 'true' : 'false');

    const label = active ? button.dataset.removeLabel : button.dataset.addLabel;

    if (label) {
        button.setAttribute('aria-label', label);
        button.setAttribute('title', label);
    }
}

function syncButtons()
{
    document.querySelectorAll(BUTTON_SELECTOR).forEach(function (button) {
        setButtonState(button, comparedProducts.has(button.dataset.productSlug));
    });
}

function setMessage(message = '')
{
    const messageElement = comparisonDock?.querySelector('[data-comparison-message]');

    if (messageElement) {
        messageElement.textContent = message;
    }
}

function syncChrome(message = '')
{
    const count = comparedProducts.size;
    const href = comparisonUrl();

    document.querySelectorAll('[data-comparison-count]').forEach(function (element) {
        element.textContent = count;
        element.classList.toggle('d-none', count === 0);
    });

    document.querySelectorAll('[data-comparison-link]').forEach(function (link) {
        link.setAttribute('href', href);
    });

    document.querySelectorAll('[data-comparison-open]').forEach(function (link) {
        const disabled = count < 2;
        link.classList.toggle('is-disabled', disabled);
        link.setAttribute('aria-disabled', disabled ? 'true' : 'false');
    });

    if (comparisonDock) {
        const shouldShowDock = count > 0 && !comparisonPage;

        comparisonDock.hidden = !shouldShowDock;
        comparisonDock.classList.toggle('is-visible', shouldShowDock);
    }

    setMessage(message);
}

function syncUi(message = '')
{
    syncButtons();
    syncChrome(message);
}

function publishChange(message = '')
{
    window.dispatchEvent(new CustomEvent('bona:comparison-change', {
        detail: {
            productSlugs: [...comparedProducts],
            message,
        },
    }));
}

function toggleProduct(button)
{
    const slug = button.dataset.productSlug;

    if (!slug) {
        return;
    }

    let message = '';

    if (comparedProducts.has(slug)) {
        comparedProducts.delete(slug);
        message = comparisonDock?.dataset.removedMessage || '';
    } else if (comparedProducts.size >= maxProducts) {
        message = comparisonDock?.dataset.limitMessage || '';
        setMessage(message);
        publishChange(message);

        return;
    } else {
        comparedProducts.add(slug);
        message = comparisonDock?.dataset.addedMessage || '';
    }

    persistComparedProducts();
    syncUi(message);
    publishChange(message);
}

function clearComparison()
{
    comparedProducts.clear();
    persistComparedProducts();
    syncUi();
    publishChange();
}

function removeProductAndReload(slug)
{
    comparedProducts.delete(slug);
    persistComparedProducts();
    window.location.assign(comparisonUrl());
}

function hydrateComparisonPage()
{
    if (!comparisonPage) {
        return false;
    }

    if (comparisonPage.dataset.comparisonHasQuery === 'true') {
        comparedProducts = new Set(readPageProducts());
        persistComparedProducts();

        return false;
    }

    if (comparedProducts.size > 0) {
        window.location.replace(comparisonUrl());

        return true;
    }

    return false;
}

function bindInteractions()
{
    document.addEventListener('click', function (event) {
        const removeButton = event.target.closest('[data-comparison-remove]');

        if (removeButton) {
            event.preventDefault();
            removeProductAndReload(removeButton.dataset.productSlug);

            return;
        }

        const clearButton = event.target.closest('[data-comparison-clear]');

        if (clearButton) {
            event.preventDefault();
            clearComparison();

            if (comparisonPage) {
                window.location.assign(comparisonBaseUrl());
            }

            return;
        }

        const openLink = event.target.closest('[data-comparison-open]');

        if (openLink && comparedProducts.size < 2) {
            event.preventDefault();
            setMessage(comparisonDock?.dataset.minimumMessage || '');

            return;
        }

        const button = event.target.closest(BUTTON_SELECTOR);

        if (!button) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        toggleProduct(button);
    });

    document.addEventListener('change', function (event) {
        if (!event.target.matches('[data-comparison-differences]')) {
            return;
        }

        comparisonPage?.classList.toggle('is-differences-only', event.target.checked);
    });

    window.addEventListener('storage', function (event) {
        if (event.key !== STORAGE_KEY) {
            return;
        }

        comparedProducts = readComparedProducts();
        syncUi();
        publishChange();
    });

    window.addEventListener('bona:catalog-appended', syncButtons);
}

export default {
    init: async function () {
        comparisonDock = document.querySelector('[data-comparison-dock]');
        comparisonPage = document.querySelector('[data-comparison-page]');
        maxProducts = Number.parseInt(comparisonDock?.dataset.maxProducts || DEFAULT_MAX_PRODUCTS, 10);
        comparedProducts = readComparedProducts();

        if (hydrateComparisonPage()) {
            return;
        }

        syncUi();
        bindInteractions();
    },
};
