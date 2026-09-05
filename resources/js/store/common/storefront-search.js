const SEARCH_DELAY = 220;

function createElement(tag, className, text) {
    const element = document.createElement(tag);
    if (className) element.className = className;
    if (text !== undefined) element.textContent = text;

    return element;
}

function createHeading(title, count) {
    const heading = createElement('div', 'bona-search-results__heading');
    heading.append(createElement('span', '', title));
    heading.append(createElement('small', '', String(count)));

    return heading;
}

function createCopy(title, meta) {
    const copy = createElement('span', 'bona-search-results__copy');
    copy.append(createElement('b', '', title));
    if (meta) copy.append(createElement('small', '', meta));

    return copy;
}

function createArrow() {
    const arrow = createElement('span', 'bona-search-results__arrow');
    arrow.setAttribute('aria-hidden', 'true');
    arrow.innerHTML = '<svg viewBox="0 0 20 20"><path d="M4 10h12M11.5 5.5 16 10l-4.5 4.5"></path></svg>';

    return arrow;
}

function createProduct(product) {
    const item = createElement('a', 'bona-search-results__item bona-search-results__item--product');
    item.href = product.link;
    item.setAttribute('role', 'option');

    const image = createElement('span', 'bona-search-results__image');
    const picture = document.createElement('img');
    picture.src = product.main_image_url;
    picture.alt = '';
    picture.loading = 'lazy';
    image.append(picture);

    item.append(image, createCopy(product.name, product.meta));
    item.append(createElement('strong', 'bona-search-results__price', product.price_formatted || product.price));

    return item;
}

function createService(service) {
    const item = createElement('a', 'bona-search-results__item bona-search-results__item--service');
    item.href = service.link;
    item.setAttribute('role', 'option');

    const icon = createElement('span', 'bona-search-results__service-icon');
    icon.setAttribute('aria-hidden', 'true');
    icon.innerHTML = '<svg viewBox="0 0 24 24"><path d="M20 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h9a4 4 0 0 1 4 4Z"></path><path d="M8 10h7M8 14h4"></path></svg>';

    item.append(icon, createCopy(service.title, service.description), createArrow());

    return item;
}

function createSuggestion(suggestion) {
    const item = createElement('a', 'bona-search-results__item bona-search-results__item--suggestion');
    item.href = suggestion.link;
    item.setAttribute('role', 'option');

    const icon = createElement('span', 'bona-search-results__suggestion-icon');
    icon.setAttribute('aria-hidden', 'true');
    icon.innerHTML = '<svg viewBox="0 0 24 24"><path d="M4 7.5h6l2 2h8v9H4z"></path><path d="M7 5h5l2 2"></path></svg>';

    item.append(icon, createCopy(suggestion.title, suggestion.meta), createArrow());

    return item;
}

function createSection(title, items, renderer, total = items.length) {
    const section = createElement('section', 'bona-search-results__section');
    section.append(createHeading(title, total));

    const list = createElement('div', 'bona-search-results__items');
    items.forEach((item) => list.append(renderer(item)));
    section.append(list);

    return section;
}

function createShowAll(url, count) {
    const link = createElement('a', 'bona-search-results__all');
    link.href = url;
    link.append(createElement('span', '', translations.storefront_search_show_all));
    link.append(createElement('strong', '', String(count)));
    link.append(createArrow());

    return link;
}

function renderState(panel, title, description) {
    panel.replaceChildren();
    const state = createElement('div', 'bona-search-results__state');
    state.append(createElement('b', '', title));
    if (description) state.append(createElement('span', '', description));
    panel.append(state);
    panel.hidden = false;
}

function initSearch(form) {
    const input = form.querySelector('input[type="search"]');
    const clearButton = form.querySelector('[data-search-clear]');
    const panel = form.querySelector('.bona-search-results');
    const endpoint = form.dataset.searchUrl;

    if (!input || !clearButton || !panel || !endpoint) {
        return;
    }

    let timer;
    let controller;
    const originalPlaceholder = input.getAttribute('placeholder') || '';

    const close = () => {
        panel.hidden = true;
        input.setAttribute('aria-expanded', 'false');
    };

    const open = () => {
        panel.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    };

    const syncClearButton = () => {
        clearButton.hidden = input.value.length === 0;
    };

    const clearSearch = () => {
        window.clearTimeout(timer);
        controller?.abort();
        input.value = '';
        panel.replaceChildren();
        delete panel.dataset.fullResultsUrl;
        delete panel.dataset.productTotal;
        close();
        syncClearButton();
        input.focus();
    };

    const search = async () => {
        const query = input.value.trim();
        if (query.length < 3) {
            controller?.abort();
            panel.replaceChildren();
            close();
            return;
        }

        controller?.abort();
        controller = new AbortController();
        renderState(panel, translations.storefront_search_loading);
        open();

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                signal: controller.signal,
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ query }),
            });

            if (!response.ok) {
                throw new Error(`Search failed with ${response.status}`);
            }

            const payload = await response.json();
            const products = payload.data?.products || [];
            const services = payload.data?.services || [];
            const suggestions = payload.data?.suggestions || [];
            const totals = payload.data?.totals || {};
            const productTotal = Number(totals.products) || products.length;
            const serviceTotal = Number(totals.services) || services.length;
            const suggestionTotal = Number(totals.suggestions) || suggestions.length;
            const fullResultsUrl = payload.data?.full_results_url;
            panel.replaceChildren();
            panel.dataset.fullResultsUrl = fullResultsUrl || '';
            panel.dataset.productTotal = String(productTotal);

            if (suggestions.length) {
                panel.append(createSection(translations.storefront_search_suggestions, suggestions, createSuggestion, suggestionTotal));
            }
            if (products.length) {
                panel.append(createSection(translations.storefront_search_products, products, createProduct, productTotal));
            }
            if (services.length) {
                panel.append(createSection(translations.storefront_search_services, services, createService, serviceTotal));
            }

            if (!products.length && !services.length && !suggestions.length) {
                renderState(panel, translations.nothing_found, translations.storefront_search_hint);
            } else {
                if (fullResultsUrl && productTotal > 0) {
                    panel.append(createShowAll(fullResultsUrl, productTotal));
                }
                open();
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                renderState(panel, translations.storefront_search_error, translations.storefront_search_retry);
            }
        }
    };

    input.addEventListener('input', () => {
        syncClearButton();
        window.clearTimeout(timer);
        timer = window.setTimeout(search, SEARCH_DELAY);
    });

    clearButton.addEventListener('click', clearSearch);

    input.addEventListener('focus', () => {
        input.setAttribute('placeholder', '');
        if (panel.childElementCount && input.value.trim().length >= 3) open();
    });

    input.addEventListener('blur', () => {
        input.setAttribute('placeholder', originalPlaceholder);
    });

    input.addEventListener('keydown', (event) => {
        const options = [...panel.querySelectorAll('[role="option"]')];

        if (event.key === 'Escape') {
            close();
            return;
        }

        if (event.key === 'ArrowDown' && options.length) {
            event.preventDefault();
            options[0].focus();
        }
    });

    panel.addEventListener('keydown', (event) => {
        const options = [...panel.querySelectorAll('[role="option"]')];
        const index = options.indexOf(document.activeElement);

        if (event.key === 'Escape') {
            close();
            input.focus();
        }
        if (event.key === 'ArrowDown' && index >= 0) {
            event.preventDefault();
            options[(index + 1) % options.length].focus();
        }
        if (event.key === 'ArrowUp' && index >= 0) {
            event.preventDefault();
            if (index === 0) input.focus();
            else options[index - 1].focus();
        }
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (input.value.trim().length < 3) {
            input.focus();
            return;
        }

        const fullResultsUrl = panel.dataset.fullResultsUrl;
        const productTotal = Number(panel.dataset.productTotal) || 0;
        if (fullResultsUrl && productTotal > 0) {
            window.location.assign(fullResultsUrl);
            return;
        }

        await search();

        if (panel.dataset.fullResultsUrl && (Number(panel.dataset.productTotal) || 0) > 0) {
            window.location.assign(panel.dataset.fullResultsUrl);
            return;
        }

        const firstResult = panel.querySelector('[role="option"]');
        if (firstResult) window.location.assign(firstResult.href);
    });

    document.addEventListener('pointerdown', (event) => {
        if (!form.contains(event.target)) close();
    });

    syncClearButton();
}

export default {
    init() {
        document.querySelectorAll('[data-storefront-search]').forEach(initSearch);
    },
};
