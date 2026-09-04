const formatNumber = (value) => new Intl.NumberFormat('uk-UA', {
    maximumFractionDigits: 0,
}).format(Math.round(Number(value) || 0));

const readVisiblePrice = (element) => {
    if (!element) {
        return 0;
    }

    return Number.parseFloat(element.textContent.replace(/\s+/g, '').replace(',', '.')) || 0;
};

export function init() {
    const page = document.querySelector('[data-product-reference]');

    if (!page) {
        return;
    }

    initGallery(page);
    initProductTabs(page);
    initInstallments(page);
    initKitSummary(page);
}

function initGallery(page) {
    const gallery = page.querySelector('[data-product-gallery]');
    const main = gallery?.querySelector('[data-gallery-main]');
    const image = gallery?.querySelector('[data-gallery-image]');
    const allThumbs = Array.from(gallery?.querySelectorAll('[data-gallery-thumb]') || []);
    const currentNode = gallery?.querySelector('[data-gallery-current]');
    const totalNode = gallery?.querySelector('[data-gallery-total]');
    let activeThumb = allThumbs[0] || null;
    let swipeStartX = null;
    let imageSwapTimer = null;

    if (!gallery || !main || !image || !allThumbs.length) {
        return;
    }

    const visibleThumbs = () => allThumbs.filter((thumb) => !thumb.hidden);

    const showThumb = (thumb, animate = true) => {
        const thumbs = visibleThumbs();

        if (!thumb || !thumbs.includes(thumb)) {
            thumb = thumbs[0] || allThumbs[0];
        }

        activeThumb = thumb;
        window.clearTimeout(imageSwapTimer);

        const applyImage = () => {
            image.src = thumb.dataset.image || image.src;
            image.alt = thumb.dataset.alt || '';
            main.classList.toggle('is-interior', thumb.dataset.interior === 'true');
            image.style.opacity = '1';
        };

        if (animate && image.src !== thumb.dataset.image) {
            image.style.opacity = '0';
            imageSwapTimer = window.setTimeout(applyImage, 120);
        } else {
            applyImage();
        }

        allThumbs.forEach((item) => {
            const isActive = item === thumb;
            item.classList.toggle('is-active', isActive);
            item.setAttribute('aria-pressed', String(isActive));
        });

        if (currentNode) {
            currentNode.textContent = String(Math.max(thumbs.indexOf(thumb) + 1, 1)).padStart(2, '0');
        }

        if (totalNode) {
            totalNode.textContent = String(thumbs.length).padStart(2, '0');
        }
    };

    const move = (direction) => {
        const thumbs = visibleThumbs();

        if (thumbs.length < 2) {
            return;
        }

        const currentIndex = Math.max(thumbs.indexOf(activeThumb), 0);
        showThumb(thumbs[(currentIndex + direction + thumbs.length) % thumbs.length]);
    };

    allThumbs.forEach((thumb) => {
        thumb.addEventListener('click', () => showThumb(thumb));
    });

    gallery.querySelector('[data-gallery-prev]')?.addEventListener('click', () => move(-1));
    gallery.querySelector('[data-gallery-next]')?.addEventListener('click', () => move(1));

    main.addEventListener('pointerdown', (event) => {
        if (event.target.closest('button')) {
            return;
        }

        swipeStartX = event.clientX;
        main.setPointerCapture?.(event.pointerId);
    });

    main.addEventListener('pointerup', (event) => {
        if (swipeStartX === null) {
            return;
        }

        const distance = event.clientX - swipeStartX;
        swipeStartX = null;

        if (Math.abs(distance) > 45) {
            move(distance < 0 ? 1 : -1);
        }
    });

    page.querySelectorAll('.color-option[data-color-id]').forEach((color) => {
        const selectColor = () => {
            const colorId = String(color.dataset.colorId || '0');
            const matchingThumbs = allThumbs.filter((thumb) => ['0', colorId].includes(String(thumb.dataset.colorId || '0')));
            const thumbsToShow = matchingThumbs.length ? matchingThumbs : allThumbs;

            allThumbs.forEach((thumb) => {
                thumb.hidden = !thumbsToShow.includes(thumb);
            });

            page.querySelectorAll('.color-option[data-color-id]').forEach((item) => {
                const isActive = item === color;
                item.classList.toggle('is-active', isActive);
                item.setAttribute('aria-pressed', String(isActive));
            });

            const selectedLabel = page.querySelector('#selected-color');
            if (selectedLabel) {
                selectedLabel.textContent = color.dataset.colorLabel || '';
            }

            const colorThumb = thumbsToShow.find((thumb) => String(thumb.dataset.colorId || '0') === colorId);
            showThumb(colorThumb || thumbsToShow[0]);
        };

        color.addEventListener('click', selectColor);
        color.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                color.click();
            }
        });
    });
}

function initProductTabs(page) {
    const tabs = Array.from(page.querySelectorAll('[data-product-tab]'));
    const panels = Array.from(page.querySelectorAll('[data-product-panel]'));

    if (!tabs.length) {
        return;
    }

    const activate = (name, moveFocus = false) => {
        tabs.forEach((tab) => {
            const isActive = tab.dataset.productTab === name;
            tab.classList.toggle('is-active', isActive);
            tab.setAttribute('aria-selected', String(isActive));
            tab.tabIndex = isActive ? 0 : -1;

            if (isActive && moveFocus) {
                tab.focus();
            }
        });

        panels.forEach((panel) => {
            const isActive = panel.dataset.productPanel === name;
            panel.classList.toggle('is-active', isActive);
            panel.hidden = !isActive;
        });
    };

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => activate(tab.dataset.productTab));
        tab.addEventListener('keydown', (event) => {
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) {
                return;
            }

            event.preventDefault();
            let nextIndex = index;

            if (event.key === 'ArrowLeft') nextIndex = (index - 1 + tabs.length) % tabs.length;
            if (event.key === 'ArrowRight') nextIndex = (index + 1) % tabs.length;
            if (event.key === 'Home') nextIndex = 0;
            if (event.key === 'End') nextIndex = tabs.length - 1;

            activate(tabs[nextIndex].dataset.productTab, true);
        });
    });

    page.querySelectorAll('[data-open-reviews]').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            activate('reviews');
            page.querySelector('#product-details')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
}

function initInstallments(page) {
    const card = page.querySelector('[data-installment-card]');
    const price = page.querySelector('#product-price');
    const mobilePrice = page.querySelector('[data-mobile-price]');
    const monthlyPayment = card?.querySelector('[data-monthly-payment]');
    const monthsValue = card?.querySelector('[data-months-value]');
    const minus = card?.querySelector('[data-months-minus]');
    const plus = card?.querySelector('[data-months-plus]');
    let months = 3;

    if (!price) {
        return;
    }

    const update = () => {
        const currentPrice = readVisiblePrice(price);

        if (monthlyPayment) monthlyPayment.textContent = formatNumber(Math.ceil(currentPrice / months));
        if (monthsValue) monthsValue.textContent = String(months);
        if (minus) minus.disabled = months <= 3;
        if (plus) plus.disabled = months >= 10;
        if (mobilePrice) mobilePrice.textContent = formatNumber(currentPrice);
    };

    card?.querySelectorAll('[data-provider]').forEach((button) => {
        button.addEventListener('click', () => {
            card.querySelectorAll('[data-provider]').forEach((item) => {
                const isActive = item === button;
                item.classList.toggle('is-active', isActive);
                item.setAttribute('aria-selected', String(isActive));
            });
        });
    });

    minus?.addEventListener('click', () => {
        months = Math.max(3, months - 1);
        update();
    });

    plus?.addEventListener('click', () => {
        months = Math.min(10, months + 1);
        update();
    });

    new MutationObserver(update).observe(price, {
        attributes: true,
        attributeFilter: ['data-product-price'],
        characterData: true,
        childList: true,
        subtree: true,
    });

    update();
}

function initKitSummary(page) {
    const selections = page.querySelector('.product-kit-selections');
    const count = page.querySelector('#kit-count');
    const summary = page.querySelector('#kit-summary');

    if (!selections || !count || !summary) {
        return;
    }

    const emptyLabel = count.textContent;
    const emptySummary = summary.textContent;

    const update = () => {
        const selected = Array.from(selections.querySelectorAll('.added-line'));
        const names = selected.map((item) => item.textContent.trim()).filter(Boolean);

        count.textContent = selected.length ? `${selected.length} ${count.dataset.selectedLabel || 'обрано'}` : emptyLabel;
        summary.textContent = names.length ? names.join(' · ') : emptySummary;
    };

    new MutationObserver(update).observe(selections, { childList: true, subtree: true });
    update();
}
