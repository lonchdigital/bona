const CARD_SELECTOR = '[data-product-card]';
const SWATCH_SELECTOR = '[data-product-card-swatch]';

function formatPrice(value, currency)
{
    const language = document.documentElement.lang === 'ru' ? 'ru-RU' : 'uk-UA';
    const formatted = new Intl.NumberFormat(language, {
        maximumFractionDigits: 0,
    }).format(Math.max(0, value));

    return `${formatted} ${currency}`;
}

function updateCardPrice(card, adjustment)
{
    const currency = card.dataset.currency || '';
    const basePrice = Number.parseFloat(card.dataset.basePrice || '0');
    const baseOldPrice = Number.parseFloat(card.dataset.baseOldPrice || '0');
    const price = card.querySelector('[data-product-card-price]');
    const oldPrice = card.querySelector('[data-product-card-old-price]');

    if (price) {
        price.textContent = formatPrice(basePrice + adjustment, currency);
    }

    if (oldPrice && baseOldPrice > basePrice) {
        oldPrice.textContent = formatPrice(baseOldPrice + adjustment, currency);
    }
}

function updateCardImage(card, swatch)
{
    const image = card.querySelector('[data-product-card-image]');

    if (!image) {
        return;
    }

    const nextSource = swatch.dataset.image || image.dataset.defaultImage;

    if (!nextSource || image.getAttribute('src') === nextSource) {
        return;
    }

    const preload = new Image();
    card.classList.add('is-switching-color');

    preload.onload = function () {
        image.src = nextSource;
        card.classList.remove('is-switching-color');
    };

    preload.onerror = function () {
        card.classList.remove('is-switching-color');
    };

    preload.src = nextSource;
}

function selectColor(swatch)
{
    const card = swatch.closest(CARD_SELECTOR);

    if (!card) {
        return;
    }

    card.querySelectorAll(SWATCH_SELECTOR).forEach(function (item) {
        const isActive = item === swatch;
        item.classList.toggle('is-active', isActive);
        item.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });

    const colorName = card.querySelector('[data-product-card-color-name]');

    if (colorName) {
        colorName.textContent = swatch.dataset.colorName || '';
    }

    const adjustment = Number.parseFloat(swatch.dataset.priceAdjustment || '0');
    updateCardPrice(card, Number.isFinite(adjustment) ? adjustment : 0);
    updateCardImage(card, swatch);
}

export default {
    init: async function () {
        document.addEventListener('click', function (event) {
            const swatch = event.target.closest(SWATCH_SELECTOR);

            if (!swatch) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            selectColor(swatch);
        });
    },
};
