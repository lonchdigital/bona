import {
    formatInstallmentRate,
    installmentQuote,
    parseInstallmentRates,
} from '../../payment/installment-pricing';

const formatNumber = (value) => new Intl.NumberFormat('uk-UA', {
    maximumFractionDigits: 0,
}).format(Math.round(Number(value) || 0));

const formatInstallmentAmount = (value) => new Intl.NumberFormat(document.documentElement.lang || 'uk-UA', {
    maximumFractionDigits: 2,
}).format(Number(value) || 0);

const readVisiblePrice = (element) => {
    if (!element) {
        return 0;
    }

    return Number.parseFloat(element.textContent.replace(/\s+/g, '').replace(',', '.')) || 0;
};

const parsePeriods = (value, fallback = [3]) => {
    try {
        const periods = JSON.parse(value || '[]')
            .map((period) => Number.parseInt(period, 10))
            .filter((period) => Number.isInteger(period) && period >= 2);

        return periods.length ? [...new Set(periods)].sort((left, right) => left - right) : fallback;
    } catch {
        return fallback;
    }
};

const isRussian = () => document.documentElement.lang.toLowerCase().startsWith('ru');

const closeProductDialog = (dialog) => {
    if (!dialog) {
        return;
    }

    if (typeof dialog.close === 'function' && dialog.open) {
        dialog.close();
    } else {
        dialog.removeAttribute('open');
        document.documentElement.classList.remove('product-dialog-is-open');
    }
};

const openProductDialog = (dialog, opener = null) => {
    if (!dialog) {
        return;
    }

    dialog._productDialogOpener = opener || document.activeElement;

    if (typeof dialog.showModal === 'function') {
        if (!dialog.open) {
            dialog.showModal();
        }
    } else {
        dialog.setAttribute('open', '');
    }

    document.documentElement.classList.add('product-dialog-is-open');
    window.requestAnimationFrame(() => dialog.querySelector('[data-product-dialog-close]')?.focus({ preventScroll: true }));
};

export function init() {
    const page = document.querySelector('[data-product-reference]');

    if (!page) {
        return;
    }

    initProductDialogs();
    initGallery(page);
    initProductTabs(page);
    initInstallments(page);
    initKitBuilder(page);
}

function initProductDialogs() {
    document.querySelectorAll('[data-product-dialog]').forEach((dialog) => {
        if (dialog.dataset.productDialogInitialized === 'true') {
            return;
        }

        dialog.dataset.productDialogInitialized = 'true';
        dialog.querySelectorAll('[data-product-dialog-close]').forEach((button) => {
            button.addEventListener('click', () => closeProductDialog(dialog));
        });

        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                closeProductDialog(dialog);
            }
        });

        dialog.addEventListener('close', () => {
            if (!document.querySelector('[data-product-dialog][open]')) {
                document.documentElement.classList.remove('product-dialog-is-open');
            }

            dialog._productDialogOpener?.focus?.({ preventScroll: true });
        });
    });

    document.querySelectorAll('[data-product-dialog-open]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            openProductDialog(document.getElementById(trigger.dataset.productDialogOpen), trigger);
        });
    });
}

function initGallery(page) {
    const gallery = page.querySelector('[data-product-gallery]');
    const main = gallery?.querySelector('[data-gallery-main]');
    const image = gallery?.querySelector('[data-gallery-image]');
    const allThumbs = Array.from(gallery?.querySelectorAll('[data-gallery-thumb]') || []);
    const currentNode = gallery?.querySelector('[data-gallery-current]');
    const totalNode = gallery?.querySelector('[data-gallery-total]');
    const thumbsShell = gallery?.querySelector('[data-gallery-thumbs-shell]');
    const thumbsTrack = gallery?.querySelector('[data-gallery-thumbs]');
    const thumbsPrevious = gallery?.querySelector('[data-gallery-thumbs-prev]');
    const thumbsNext = gallery?.querySelector('[data-gallery-thumbs-next]');
    const thumbsPerPage = 4;
    let activeThumb = allThumbs[0] || null;
    let thumbWindowStart = 0;
    let swipeStartX = null;
    let imageSwapTimer = null;
    let thumbResizeFrame = null;

    if (!gallery || !main || !image || !allThumbs.length) {
        return;
    }

    const visibleThumbs = () => allThumbs.filter((thumb) => !thumb.hidden);

    const updateThumbCarousel = (focusThumb = activeThumb, smooth = false) => {
        const thumbs = visibleThumbs();
        const hasOverflow = thumbs.length > thumbsPerPage;
        const maximumStart = Math.max(thumbs.length - thumbsPerPage, 0);
        const focusIndex = thumbs.indexOf(focusThumb);

        if (focusIndex >= 0 && (focusIndex < thumbWindowStart || focusIndex >= thumbWindowStart + thumbsPerPage)) {
            thumbWindowStart = Math.min(Math.floor(focusIndex / thumbsPerPage) * thumbsPerPage, maximumStart);
        }

        thumbWindowStart = Math.min(Math.max(thumbWindowStart, 0), maximumStart);
        thumbsShell?.classList.toggle('has-overflow', hasOverflow);

        [thumbsPrevious, thumbsNext].forEach((button) => {
            if (button) button.hidden = !hasOverflow;
        });

        if (thumbsPrevious) thumbsPrevious.disabled = !hasOverflow || thumbWindowStart === 0;
        if (thumbsNext) thumbsNext.disabled = !hasOverflow || thumbWindowStart >= maximumStart;

        if (!thumbsTrack) return;

        const target = thumbs[thumbWindowStart];
        const targetLeft = target
            ? target.getBoundingClientRect().left - thumbsTrack.getBoundingClientRect().left + thumbsTrack.scrollLeft
            : 0;
        if (typeof thumbsTrack.scrollTo === 'function') {
            thumbsTrack.scrollTo({ left: targetLeft, behavior: smooth ? 'smooth' : 'auto' });
        } else {
            thumbsTrack.scrollLeft = targetLeft;
        }
    };

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

        updateThumbCarousel(thumb, animate);
    };

    const move = (direction) => {
        const thumbs = visibleThumbs();

        if (thumbs.length < 2) {
            return;
        }

        const currentIndex = Math.max(thumbs.indexOf(activeThumb), 0);
        showThumb(thumbs[(currentIndex + direction + thumbs.length) % thumbs.length]);
    };

    allThumbs.forEach((thumb) => thumb.addEventListener('click', () => showThumb(thumb)));
    gallery.querySelector('[data-gallery-prev]')?.addEventListener('click', () => move(-1));
    gallery.querySelector('[data-gallery-next]')?.addEventListener('click', () => move(1));
    thumbsPrevious?.addEventListener('click', () => {
        const thumbs = visibleThumbs();
        thumbWindowStart = Math.max(thumbWindowStart - thumbsPerPage, 0);
        showThumb(thumbs[thumbWindowStart]);
    });
    thumbsNext?.addEventListener('click', () => {
        const thumbs = visibleThumbs();
        thumbWindowStart = Math.min(thumbWindowStart + thumbsPerPage, Math.max(thumbs.length - thumbsPerPage, 0));
        showThumb(thumbs[thumbWindowStart]);
    });

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
            thumbWindowStart = 0;

            page.querySelectorAll('.color-option[data-color-id]').forEach((item) => {
                const isActive = item === color;
                item.classList.toggle('is-active', isActive);
                item.setAttribute('aria-pressed', String(isActive));
            });

            const selectedLabel = page.querySelector('#selected-color');
            if (selectedLabel) selectedLabel.textContent = color.dataset.colorLabel || '';

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

    updateThumbCarousel(activeThumb);
    window.addEventListener('resize', () => {
        window.cancelAnimationFrame(thumbResizeFrame);
        thumbResizeFrame = window.requestAnimationFrame(() => updateThumbCarousel(activeThumb));
    }, { passive: true });
}

function initProductTabs(page) {
    const navigation = page.querySelector('[data-product-section-nav]');
    const overview = navigation?.querySelector('[data-product-overview]');
    const tabs = Array.from(page.querySelectorAll('[data-product-tab]'));
    const panels = Array.from(page.querySelectorAll('[data-product-panel]'));
    const details = page.querySelector('#product-details');
    const controls = [overview, ...tabs].filter(Boolean);
    let activeDetail = tabs.find((tab) => tab.dataset.productTab === panels.find((panel) => !panel.hidden)?.dataset.productPanel)
        ?.dataset.productTab || tabs[0]?.dataset.productTab || 'reviews';
    let activeControlName = null;
    let navigationLockUntil = 0;
    let animationFrame = null;

    if (!navigation || !overview || !tabs.length) return;

    const markActiveControl = (name, moveFocus = false) => {
        const activeControlChanged = activeControlName !== name;

        controls.forEach((control) => {
            const controlName = control === overview ? 'overview' : control.dataset.productTab;
            const isActive = controlName === name;

            control.classList.toggle('is-active', isActive);
            control.setAttribute('aria-pressed', String(isActive));
            if (control === overview) {
                if (isActive) control.setAttribute('aria-current', 'true');
                else control.removeAttribute('aria-current');
            }

            if (isActive && moveFocus) control.focus();
        });

        activeControlName = name;

        if (activeControlChanged) {
            navigation.querySelector('.is-active')?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
    };

    const activate = (name, { moveFocus = false, scroll = false } = {}) => {
        if (name === 'overview') {
            markActiveControl('overview', moveFocus);

            if (scroll) {
                navigationLockUntil = Date.now() + 700;
                page.querySelector('#product-overview')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            return;
        }

        activeDetail = name;
        panels.forEach((panel) => {
            const isActive = panel.dataset.productPanel === name;
            panel.classList.toggle('is-active', isActive);
            panel.hidden = !isActive;
        });

        markActiveControl(name, moveFocus);

        if (scroll) {
            navigationLockUntil = Date.now() + 700;
            details?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    overview.addEventListener('click', () => activate('overview', { scroll: true }));
    tabs.forEach((tab) => tab.addEventListener('click', () => activate(tab.dataset.productTab, { scroll: true })));

    controls.forEach((control, index) => {
        control.addEventListener('keydown', (event) => {
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;

            event.preventDefault();
            let nextIndex = index;
            if (event.key === 'ArrowLeft') nextIndex = (index - 1 + controls.length) % controls.length;
            if (event.key === 'ArrowRight') nextIndex = (index + 1) % controls.length;
            if (event.key === 'Home') nextIndex = 0;
            if (event.key === 'End') nextIndex = controls.length - 1;

            const nextControl = controls[nextIndex];
            activate(nextControl === overview ? 'overview' : nextControl.dataset.productTab, { moveFocus: true, scroll: true });
        });
    });

    page.querySelectorAll('[data-open-reviews]').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            activate('reviews', { scroll: true });
        });
    });

    const syncNavigationWithScroll = () => {
        animationFrame = null;
        const stickyOffset = navigation.offsetHeight + 16;
        const detailsReached = details && details.getBoundingClientRect().top <= stickyOffset;

        if (Date.now() >= navigationLockUntil) {
            markActiveControl(detailsReached ? activeDetail : 'overview');
        }

        navigation.classList.toggle('is-stuck', navigation.getBoundingClientRect().top <= 1);
    };

    const scheduleNavigationSync = () => {
        if (animationFrame !== null) return;
        animationFrame = window.requestAnimationFrame(syncNavigationWithScroll);
    };

    window.addEventListener('scroll', scheduleNavigationSync, { passive: true });
    window.addEventListener('resize', scheduleNavigationSync, { passive: true });
    window.addEventListener('pageshow', scheduleNavigationSync);
    markActiveControl('overview');
    scheduleNavigationSync();
}

function initInstallments(page) {
    const card = page.querySelector('[data-installment-card]');
    const price = page.querySelector('#product-price');
    const monthlyPayment = card?.querySelector('[data-monthly-payment]');
    const rateCopy = card?.querySelector('[data-product-installment-rate]');
    const monthsValue = card?.querySelector('[data-months-value]');
    const minus = card?.querySelector('[data-months-minus]');
    const plus = card?.querySelector('[data-months-plus]');
    const creditButton = card?.querySelector('[data-checkout-base]');
    const providerButtons = Array.from(card?.querySelectorAll('[data-provider]') || []);
    const termsDialog = document.getElementById('installment-terms-dialog');
    let activeProvider = providerButtons.find((button) => button.classList.contains('is-active')) || providerButtons[0] || null;
    let periods = parsePeriods(activeProvider?.dataset.periods);
    let rates = parseInstallmentRates(activeProvider?.dataset.rates);
    let months = periods[0] || 3;

    if (!price) return;

    const updateTermsPanel = () => {
        termsDialog?.querySelectorAll('[data-terms-provider]').forEach((panel) => {
            panel.hidden = panel.dataset.termsProvider !== activeProvider?.dataset.provider;
        });
    };

    const update = () => {
        const currentPrice = readVisiblePrice(price);
        const currentIndex = Math.max(periods.indexOf(months), 0);
        const quote = installmentQuote(currentPrice, months, rates[months]);

        if (monthlyPayment) monthlyPayment.textContent = formatInstallmentAmount(quote.monthly);
        if (rateCopy) rateCopy.textContent = `+${formatInstallmentRate(quote.rate)}%`;
        if (monthsValue) monthsValue.textContent = String(months);
        if (minus) minus.disabled = currentIndex <= 0;
        if (plus) plus.disabled = currentIndex >= periods.length - 1;
        page.querySelectorAll('[data-installment-example]').forEach((example) => {
            const provider = providerButtons.find((button) => button.dataset.provider === example.dataset.providerExample);
            const providerPeriods = parsePeriods(provider?.dataset.periods, [3]);
            const providerRates = parseInstallmentRates(provider?.dataset.rates);
            const maximumPeriod = Math.max(...providerPeriods);
            const exampleQuote = installmentQuote(currentPrice, maximumPeriod, providerRates[maximumPeriod]);
            const prefix = isRussian() ? 'от' : 'від';
            const suffix = isRussian() ? 'мес.' : 'міс.';
            example.textContent = `${prefix} ${formatInstallmentAmount(exampleQuote.monthly)} грн/${suffix}`;
        });

        if (creditButton && activeProvider) {
            const checkout = new URL(creditButton.dataset.checkoutBase, window.location.origin);
            checkout.searchParams.set('payment_type_id', activeProvider.dataset.paymentType);
            checkout.searchParams.delete('payment_period');
            checkout.searchParams.delete('mono_payment_period');
            checkout.searchParams.set(activeProvider.dataset.provider === 'mono' ? 'mono_payment_period' : 'payment_period', String(months));
            creditButton.dataset.checkoutRedirect = checkout.toString();
        }
    };

    const selectProvider = (button, keepMonth = false) => {
        if (!button) return;

        activeProvider = button;
        periods = parsePeriods(button.dataset.periods);
        rates = parseInstallmentRates(button.dataset.rates);
        if (!keepMonth || !periods.includes(months)) months = periods[0] || 3;

        providerButtons.forEach((item) => {
            const isActive = item === button;
            item.classList.toggle('is-active', isActive);
            item.setAttribute('aria-selected', String(isActive));
        });

        updateTermsPanel();
        update();
    };

    providerButtons.forEach((button) => button.addEventListener('click', () => selectProvider(button, true)));

    minus?.addEventListener('click', () => {
        const index = Math.max(periods.indexOf(months), 0);
        months = periods[Math.max(0, index - 1)] || months;
        update();
    });

    plus?.addEventListener('click', () => {
        const index = Math.max(periods.indexOf(months), 0);
        months = periods[Math.min(periods.length - 1, index + 1)] || months;
        update();
    });

    card?.querySelector('[data-installment-terms-open]')?.addEventListener('click', (event) => {
        updateTermsPanel();
        openProductDialog(termsDialog, event.currentTarget);
    });

    page.querySelectorAll('[data-focus-provider]').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            const provider = providerButtons.find((button) => button.dataset.provider === link.dataset.focusProvider);
            selectProvider(provider);
            card?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            card?.classList.remove('is-focused');
            window.requestAnimationFrame(() => card?.classList.add('is-focused'));
            window.setTimeout(() => card?.classList.remove('is-focused'), 1500);
        });
    });

    new MutationObserver(update).observe(price, {
        attributes: true,
        attributeFilter: ['data-product-price'],
        characterData: true,
        childList: true,
        subtree: true,
    });

    selectProvider(activeProvider);
}

function initKitBuilder(page) {
    const dialog = document.getElementById('product-kit-dialog');
    const openButton = page.querySelector('[data-product-dialog-open="product-kit-dialog"]');
    const price = page.querySelector('#product-price');
    const categoryButtons = Array.from(dialog?.querySelectorAll('[data-kit-category]') || []);
    const choiceButtons = Array.from(dialog?.querySelectorAll('[data-kit-option]') || []);
    const saveButton = dialog?.querySelector('[data-kit-save]');
    const selectedList = dialog?.querySelector('[data-kit-dialog-selected]');
    const selectionHint = dialog?.querySelector('[data-kit-selection-hint]');
    const totalNode = dialog?.querySelector('[data-kit-dialog-total]');
    const categoryTitle = dialog?.querySelector('[data-kit-choice-title]');
    const categoryStep = dialog?.querySelector('[data-kit-choice-step]');
    const countNode = page.querySelector('#kit-count');
    const summaryNode = page.querySelector('#kit-summary');
    const selectionChips = page.querySelector('.product-kit-selections');
    const basePrice = Number.parseFloat(price?.dataset.startPrice || '0') || 0;
    const currency = page.querySelector('.product-price-row strong > span:last-child')?.textContent.trim() || 'грн.';
    const emptyCount = countNode?.textContent || '';
    const emptySummary = summaryNode?.textContent || '';
    const emptySelectionText = isRussian() ? 'Дополнительные элементы не выбраны' : 'Додаткові елементи не обрані';
    let activeCategory = categoryButtons[0]?.dataset.kitCategory || '';
    let committed = new Map();
    let draft = new Map();

    if (!dialog || !price || !categoryButtons.length || !choiceButtons.length) return;

    const calculateOptionSurcharge = () => {
        const attributePrice = [...page.querySelectorAll('select.art-select-attribute')]
            .reduce((sum, select) => sum + (Number.parseFloat(select.selectedOptions[0]?.dataset.price) || 0), 0);
        const colorPrice = Number.parseFloat(page.querySelector('.color-btn.color-selected')?.dataset.price) || 0;
        return attributePrice + colorPrice;
    };

    const currentQuantity = () => Math.max(Number.parseInt(price.dataset.count || '1', 10) || 1, 1);

    const selectedChoices = (values = draft) => [...values.values()]
        .map((optionKey) => choiceButtons.find((button) => button.dataset.kitOptionKey === optionKey))
        .filter(Boolean);

    const syncSelectionOverflow = () => {
        if (!selectedList) {
            return;
        }

        const isScrollable = selectedList.scrollHeight > selectedList.clientHeight + 1;
        selectedList.classList.toggle('is-scrollable', isScrollable);

        if (selectionHint) {
            selectionHint.hidden = !isScrollable;
        }
    };

    const renderSummary = () => {
        const selected = selectedChoices();
        const extras = selected.reduce((sum, button) => sum + (Number.parseFloat(button.dataset.kitPrice) || 0), 0);

        if (selectedList) {
            selectedList.replaceChildren();
            if (!selected.length) {
                const item = document.createElement('li');
                item.className = 'is-empty';
                item.textContent = emptySelectionText;
                selectedList.append(item);
            } else {
                selected.forEach((choice) => {
                    const item = document.createElement('li');
                    const label = document.createElement('span');
                    const actions = document.createElement('span');
                    const amount = document.createElement('span');
                    const removeButton = document.createElement('button');

                    actions.className = 'kit-dialog__selection-actions';
                    amount.className = 'kit-dialog__selection-price';
                    removeButton.className = 'kit-dialog__remove';
                    removeButton.type = 'button';
                    removeButton.setAttribute('aria-label', `${isRussian() ? 'Удалить' : 'Видалити'} ${choice.dataset.kitCategoryName}: ${choice.dataset.kitLabel}`);
                    removeButton.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16"></path><path d="M9 7V4h6v3"></path><path d="m6.5 7 .8 13h9.4l.8-13"></path><path d="M10 11v5M14 11v5"></path></svg>';
                    label.textContent = `${choice.dataset.kitCategoryName}: ${choice.dataset.kitLabel}`;
                    amount.textContent = `+${formatNumber(choice.dataset.kitPrice)} ${currency}`;
                    removeButton.addEventListener('click', () => {
                        draft.delete(choice.dataset.kitCategoryKey);
                        render();
                    });
                    actions.append(amount, removeButton);
                    item.append(label, actions);
                    selectedList.append(item);
                });
            }

            window.requestAnimationFrame(syncSelectionOverflow);
        }

        if (totalNode) {
            totalNode.textContent = `${formatNumber((basePrice + extras + calculateOptionSurcharge()) * currentQuantity())} ${currency}`;
        }
    };

    const render = () => {
        categoryButtons.forEach((button, index) => {
            const selectedKey = draft.get(button.dataset.kitCategory);
            const selected = choiceButtons.find((choice) => choice.dataset.kitOptionKey === selectedKey);
            const isActive = button.dataset.kitCategory === activeCategory;
            const summary = button.querySelector('[data-kit-category-summary]');
            const state = button.querySelector('.kit-builder__state');
            button.classList.toggle('is-active', isActive);
            button.classList.toggle('is-complete', Boolean(selected));
            button.setAttribute('aria-current', isActive ? 'step' : 'false');
            if (summary) summary.textContent = selected?.dataset.kitLabel || (isRussian() ? 'Выберите подходящий элемент' : 'Оберіть потрібний елемент');
            if (state) state.textContent = selected ? '✓' : '→';
            if (isActive) {
                if (categoryTitle) categoryTitle.textContent = button.querySelector('.kit-builder__copy b')?.textContent || '';
                if (categoryStep) categoryStep.textContent = `${isRussian() ? 'Шаг' : 'Крок'} ${String(index + 1).padStart(2, '0')}`;
            }
        });

        choiceButtons.forEach((button) => {
            const isVisible = button.dataset.kitCategoryKey === activeCategory;
            const isSelected = draft.get(activeCategory) === button.dataset.kitOptionKey;
            button.hidden = !isVisible;
            button.classList.toggle('is-selected', isSelected);
            button.setAttribute('aria-pressed', String(isSelected));
            const label = button.querySelector('.kit-choice-card__copy em');
            if (label) label.textContent = isSelected ? (isRussian() ? 'Выбрано' : 'Обрано') : (isRussian() ? 'Выбрать' : 'Обрати');
        });

        renderSummary();
    };

    const save = () => {
        committed = new Map(draft);
        const selected = selectedChoices(committed);
        const extras = selected.reduce((sum, button) => sum + (Number.parseFloat(button.dataset.kitPrice) || 0), 0);

        document.querySelectorAll('.product-kit-cart-data .single-sub-product-add-to-cart').forEach((carrier) => {
            carrier.setAttribute('data-count', '0');
            carrier.setAttribute('data-added', '0');
        });

        const quantity = currentQuantity();

        selected.forEach((button) => {
            const carrier = document.getElementById(button.dataset.kitCarrier);
            carrier?.setAttribute('data-count', String(quantity));
            carrier?.setAttribute('data-added', '1');
        });

        const kitPrice = (basePrice + extras) * quantity;
        price.dataset.productPrice = String(kitPrice);
        price.textContent = formatNumber(kitPrice + (calculateOptionSurcharge() * quantity));

        if (countNode) countNode.textContent = selected.length ? `${selected.length} ${countNode.dataset.selectedLabel || 'обрано'}` : emptyCount;
        if (summaryNode) summaryNode.textContent = selected.length ? selected.map((button) => button.dataset.kitLabel).join(' · ') : emptySummary;
        if (selectionChips) {
            selectionChips.replaceChildren(...selected.map((button) => {
                const chip = document.createElement('span');
                chip.className = 'product-kit-selection';
                chip.textContent = `${button.dataset.kitCategoryName}: ${button.dataset.kitLabel}`;
                return chip;
            }));
        }

        closeProductDialog(dialog);
    };

    categoryButtons.forEach((button) => button.addEventListener('click', () => {
        activeCategory = button.dataset.kitCategory;
        render();
    }));

    choiceButtons.forEach((button) => button.addEventListener('click', () => {
        draft.set(button.dataset.kitCategoryKey, button.dataset.kitOptionKey);
        render();
    }));

    saveButton?.addEventListener('click', save);
    openButton?.addEventListener('click', () => {
        draft = new Map(committed);
        render();
    });

    render();
}
