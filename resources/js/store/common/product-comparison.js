const BUTTON_SELECTOR = '[data-product-compare]';
const STORAGE_KEY = 'bona-compared-products';

let comparedProducts = new Set();

function readComparedProducts()
{
    try {
        const stored = JSON.parse(window.localStorage.getItem(STORAGE_KEY) || '[]');

        return new Set(Array.isArray(stored) ? stored.filter(item => typeof item === 'string' && item) : []);
    } catch (error) {
        return new Set();
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

function publishChange()
{
    window.dispatchEvent(new CustomEvent('bona:comparison-change', {
        detail: { productSlugs: [...comparedProducts] },
    }));
}

function toggleProduct(button)
{
    const slug = button.dataset.productSlug;

    if (!slug) {
        return;
    }

    if (comparedProducts.has(slug)) {
        comparedProducts.delete(slug);
    } else {
        comparedProducts.add(slug);
    }

    persistComparedProducts();
    syncButtons();
    publishChange();
}

export default {
    init: async function () {
        comparedProducts = readComparedProducts();
        syncButtons();

        document.addEventListener('click', function (event) {
            const button = event.target.closest(BUTTON_SELECTOR);

            if (!button) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            toggleProduct(button);
        });

        window.addEventListener('storage', function (event) {
            if (event.key !== STORAGE_KEY) {
                return;
            }

            comparedProducts = readComparedProducts();
            syncButtons();
            publishChange();
        });
    },
};
