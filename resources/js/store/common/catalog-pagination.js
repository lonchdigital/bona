const GRID_SELECTOR = '[data-catalog-grid]';
const PAGINATION_SELECTOR = '[data-catalog-pagination]';
const LOAD_MORE_SELECTOR = '[data-catalog-load-more]';

let initialized = false;
let loading = false;
let enhancedHistory = false;

function updateHeadLink(parsedDocument, relation, pageUrl)
{
    const selector = `link[rel="${relation}"]`;
    const incoming = parsedDocument.head.querySelector(selector);
    const current = document.head.querySelector(selector);

    if (!incoming) {
        current?.remove();

        return;
    }

    const target = current || document.createElement('link');
    target.setAttribute('rel', relation);
    target.setAttribute('href', new URL(incoming.getAttribute('href'), pageUrl).href);

    if (!current) {
        document.head.appendChild(target);
    }
}

function updateDocumentMetadata(parsedDocument, pageUrl)
{
    document.title = parsedDocument.title;
    updateHeadLink(parsedDocument, 'canonical', pageUrl);
    updateHeadLink(parsedDocument, 'prev', pageUrl);
    updateHeadLink(parsedDocument, 'next', pageUrl);
}

function setLoadingState(link, active)
{
    const label = link.querySelector('span');

    link.classList.toggle('is-loading', active);
    link.setAttribute('aria-busy', active ? 'true' : 'false');

    if (!label) {
        return;
    }

    if (!link.dataset.originalLabel) {
        link.dataset.originalLabel = label.textContent.trim();
    }

    label.textContent = active
        ? (link.dataset.loadingLabel || link.dataset.originalLabel)
        : link.dataset.originalLabel;
}

async function loadNextPage(link)
{
    if (loading) {
        return;
    }

    const currentGrid = document.querySelector(GRID_SELECTOR);
    const currentPagination = document.querySelector(PAGINATION_SELECTOR);
    const nextUrl = link.href;

    if (!currentGrid || !currentPagination || !nextUrl) {
        window.location.assign(nextUrl);

        return;
    }

    loading = true;
    setLoadingState(link, true);

    try {
        const response = await fetch(nextUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(`Catalog page request failed with ${response.status}`);
        }

        const parsedDocument = new DOMParser().parseFromString(await response.text(), 'text/html');
        const nextGrid = parsedDocument.querySelector(GRID_SELECTOR);
        const nextPagination = parsedDocument.querySelector(PAGINATION_SELECTOR);

        if (!nextGrid) {
            throw new Error('Catalog page response does not contain a product grid');
        }

        const fragment = document.createDocumentFragment();
        Array.from(nextGrid.children).forEach(function (item) {
            fragment.appendChild(document.importNode(item, true));
        });
        currentGrid.appendChild(fragment);

        if (nextPagination) {
            currentPagination.replaceWith(document.importNode(nextPagination, true));
        } else {
            currentPagination.remove();
        }

        updateDocumentMetadata(parsedDocument, response.url || nextUrl);
        window.history.pushState({ bonaCatalogPage: nextUrl }, '', nextUrl);
        enhancedHistory = true;

        const liveRegion = document.querySelector('[data-catalog-load-status]');
        if (liveRegion) {
            liveRegion.textContent = link.dataset.loadedLabel || '';
        }

        window.dispatchEvent(new CustomEvent('bona:catalog-appended'));
    } catch (error) {
        window.location.assign(nextUrl);
    } finally {
        loading = false;
        setLoadingState(link, false);
    }
}

export default {
    init: function () {
        if (initialized) {
            return;
        }

        initialized = true;

        document.addEventListener('click', function (event) {
            const eventTarget = event.target instanceof Element
                ? event.target
                : event.target?.parentElement;
            const link = eventTarget?.closest(LOAD_MORE_SELECTOR);

            if (!link) {
                return;
            }

            event.preventDefault();
            loadNextPage(link);
        });

        window.addEventListener('popstate', function () {
            if (enhancedHistory) {
                window.location.reload();
            }
        });
    },
};
