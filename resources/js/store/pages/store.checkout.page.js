import $ from 'jquery';
import Inputmask from 'inputmask';

const PAYMENT_PRIVAT = '3';
const PAYMENT_MONO = '4';

export default async function () {
    const form = document.querySelector('#checkout-main');
    if (!form) return;

    const FastSelect = await import('./store.checkout.page/fast-select');
    FastSelect.init();

    Inputmask({ mask: '+38(099)999-99-99' }).mask($('#phone'));
    Inputmask({ mask: '+38(099)999-99-99' }).mask($('#custom_phone'));

    const authDialog = document.querySelector('[data-checkout-auth-dialog]');
    const authForm = authDialog?.querySelector('[data-checkout-auth-form]');
    const authEmail = authForm?.querySelector('input[name="email"]');
    const authPassword = authForm?.querySelector('input[name="password"]');
    const authStatus = authForm?.querySelector('[data-checkout-auth-status]');
    const authSubmit = authForm?.querySelector('button[type="submit"]');
    const authSubmitLabel = authForm?.querySelector('[data-checkout-auth-submit-label]');
    const authForgot = authForm?.querySelector('[data-checkout-auth-forgot]');
    const authSubmitDefaultLabel = authSubmitLabel?.textContent || '';

    const setAuthStatus = (message = '') => {
        if (!authStatus) return;

        authStatus.textContent = message;
        authStatus.hidden = message === '';
    };

    const updateForgotPasswordLink = () => {
        if (!authForgot) return;

        const url = new URL(authForgot.dataset.baseUrl || authForgot.href, window.location.origin);
        const email = authEmail?.value.trim();
        if (email) url.searchParams.set('email', email);
        else url.searchParams.delete('email');
        authForgot.href = url.toString();
    };

    const closeAuthDialog = () => {
        if (!authDialog) return;
        if (typeof authDialog.close === 'function') authDialog.close();
        else authDialog.removeAttribute('open');
    };

    const openAuthDialog = (email = '') => {
        if (!authDialog || !authForm) return;

        setAuthStatus();
        authForm.querySelectorAll('[aria-invalid="true"]').forEach((input) => input.removeAttribute('aria-invalid'));
        if (email && authEmail) authEmail.value = email;
        updateForgotPasswordLink();

        if (typeof authDialog.showModal === 'function') authDialog.showModal();
        else authDialog.setAttribute('open', '');

        requestAnimationFrame(() => {
            const target = authEmail?.value ? authPassword : authEmail;
            target?.focus();
        });
    };

    document.querySelectorAll('[data-checkout-auth-open]').forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            if (!authDialog) return;
            event.preventDefault();
            openAuthDialog(trigger.dataset.authEmail || document.querySelector('#email')?.value || '');
        });
    });

    authDialog?.querySelector('[data-checkout-auth-close]')?.addEventListener('click', closeAuthDialog);
    authDialog?.addEventListener('click', (event) => {
        if (event.target === authDialog) closeAuthDialog();
    });
    authDialog?.addEventListener('close', () => {
        if (authPassword) authPassword.value = '';
        setAuthStatus();
    });
    authEmail?.addEventListener('input', updateForgotPasswordLink);

    authForm?.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!authForm.checkValidity()) {
            authForm.reportValidity();
            return;
        }

        setAuthStatus();
        authForm.querySelectorAll('[aria-invalid="true"]').forEach((input) => input.removeAttribute('aria-invalid'));
        if (authSubmit) {
            authSubmit.disabled = true;
            authSubmit.setAttribute('aria-busy', 'true');
        }
        if (authSubmitLabel) authSubmitLabel.textContent = authForm.dataset.processingLabel;

        try {
            const signInData = new FormData(authForm);
            const checkoutDraft = {};
            new FormData(form).forEach((value, key) => {
                if (key !== '_token' && typeof value === 'string') checkoutDraft[key] = value;
            });
            signInData.set('checkout_draft', JSON.stringify(checkoutDraft));

            const response = await fetch(authForm.action, {
                method: 'POST',
                body: signInData,
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                const errors = data.errors || {};
                const fieldName = Object.keys(errors)[0];
                const field = fieldName ? authForm.elements.namedItem(fieldName) : null;
                const message = fieldName && Array.isArray(errors[fieldName])
                    ? errors[fieldName][0]
                    : (data.message || authForm.dataset.fallbackError);

                if (field instanceof HTMLElement) {
                    field.setAttribute('aria-invalid', 'true');
                    field.focus();
                }
                setAuthStatus(message);
                return;
            }

            window.location.assign(data.redirect_to || window.location.href);
        } catch (error) {
            setAuthStatus(authForm.dataset.networkError);
        } finally {
            if (authSubmit) {
                authSubmit.disabled = false;
                authSubmit.removeAttribute('aria-busy');
            }
            if (authSubmitLabel) authSubmitLabel.textContent = authSubmitDefaultLabel;
        }
    });

    const showPanel = (selector, activeId) => {
        document.querySelectorAll(selector).forEach((panel) => {
            panel.hidden = panel.id !== activeId;
        });
    };

    const choiceName = (input) => input.closest('label')?.querySelector('b')?.textContent.trim() || '';

    const installmentPanels = [...form.querySelectorAll('[data-checkout-installment]')];
    const updateInstallmentPanel = (panel) => {
        const select = panel.querySelector('[data-installment-select]');
        const monthly = panel.querySelector('[data-installment-monthly]');
        const periodCopy = panel.querySelector('[data-installment-period-copy]');
        const decrease = panel.querySelector('[data-installment-decrease]');
        const increase = panel.querySelector('[data-installment-increase]');
        const periods = JSON.parse(panel.dataset.periods || '[]').map(Number).filter(Number.isFinite);
        const currentPeriod = Number(select?.value || periods[0] || 1);
        const currentIndex = Math.max(0, periods.indexOf(currentPeriod));
        const total = Number(form.dataset.checkoutTotal || 0);

        if (monthly) monthly.textContent = money(Math.ceil(total / Math.max(1, currentPeriod)));
        if (periodCopy && select) periodCopy.textContent = select.options[select.selectedIndex]?.textContent || currentPeriod;
        if (decrease) decrease.disabled = currentIndex <= 0;
        if (increase) increase.disabled = currentIndex >= periods.length - 1;
    };

    const updateInstallments = () => installmentPanels.forEach(updateInstallmentPanel);

    installmentPanels.forEach((panel) => {
        const select = panel.querySelector('[data-installment-select]');
        const periods = JSON.parse(panel.dataset.periods || '[]').map(Number).filter(Number.isFinite);

        const changePeriod = (direction) => {
            if (!select || !periods.length) return;

            const currentIndex = Math.max(0, periods.indexOf(Number(select.value)));
            const nextIndex = Math.min(periods.length - 1, Math.max(0, currentIndex + direction));
            select.value = String(periods[nextIndex]);
            updateInstallmentPanel(panel);
        };

        panel.querySelector('[data-installment-decrease]')?.addEventListener('click', () => changePeriod(-1));
        panel.querySelector('[data-installment-increase]')?.addEventListener('click', () => changePeriod(1));
        select?.addEventListener('change', () => updateInstallmentPanel(panel));
    });

    const updateProgress = (step) => {
        const order = ['contact', 'delivery', 'payment'];
        const activeIndex = order.indexOf(step);
        document.querySelectorAll('[data-checkout-progress]').forEach((item) => {
            item.classList.toggle('is-active', order.indexOf(item.dataset.checkoutProgress) <= activeIndex);
        });
    };

    document.querySelectorAll('[data-checkout-step]').forEach((step) => {
        step.addEventListener('focusin', () => updateProgress(step.dataset.checkoutStep));
    });

    const deliveryInputs = [...form.querySelectorAll('input[name="delivery_type_id"]')];
    const onDeliveryChange = (input, refreshSummary = true) => {
        showPanel('.accordion-delivery-data', input.dataset.accordion);
        const output = form.querySelector('.selected-delivery-type');
        if (output) output.textContent = choiceName(input);
        updateProgress('delivery');
        if (refreshSummary) {
            getSummaryByDeliveryTypeId(input.value, (response) => {
                showSummaryWithDelivery(response, form);
                updateInstallments();
            });
        }
    };
    deliveryInputs.forEach((input) => input.addEventListener('change', () => onDeliveryChange(input)));

    const recipientInputs = [...form.querySelectorAll('input[name="recipient_type_id"]')];
    recipientInputs.forEach((input) => input.addEventListener('change', () => {
        showPanel('.accordion-recipient-data', input.dataset.accordion);
    }));

    const paymentInputs = [...form.querySelectorAll('input[name="payment_type_id"]')];
    const onPaymentChange = (input) => {
        const monoPanel = document.querySelector('#collapseMonoPartialPayment');
        const privatPanel = document.querySelector('#collapsePartialPayment');
        if (monoPanel) monoPanel.hidden = input.value !== PAYMENT_MONO;
        if (privatPanel) privatPanel.hidden = input.value !== PAYMENT_PRIVAT;

        const output = form.querySelector('.selected-payment-type');
        if (output) output.textContent = choiceName(input);
        updateProgress('payment');
    };
    paymentInputs.forEach((input) => input.addEventListener('change', () => onPaymentChange(input)));

    const activeDelivery = deliveryInputs.find((input) => input.checked);
    const activeRecipient = recipientInputs.find((input) => input.checked);
    const activePayment = paymentInputs.find((input) => input.checked);
    if (activeDelivery) onDeliveryChange(activeDelivery, true);
    else showPanel('.accordion-delivery-data', null);
    if (activeRecipient) showPanel('.accordion-recipient-data', activeRecipient.dataset.accordion);
    if (activePayment) onPaymentChange(activePayment);
    updateInstallments();

    const termsDialog = document.querySelector('[data-installment-terms-dialog]');
    const closeTermsDialog = () => {
        if (!termsDialog) return;
        if (typeof termsDialog.close === 'function') termsDialog.close();
        else termsDialog.removeAttribute('open');
    };

    document.querySelectorAll('[data-installment-terms]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!termsDialog) return;

            termsDialog.querySelectorAll('[data-installment-terms-panel]').forEach((panel) => {
                panel.hidden = panel.dataset.installmentTermsPanel !== button.dataset.installmentTerms;
            });

            if (typeof termsDialog.showModal === 'function') termsDialog.showModal();
            else termsDialog.setAttribute('open', '');
        });
    });
    termsDialog?.querySelector('[data-installment-terms-close]')?.addEventListener('click', closeTermsDialog);
    termsDialog?.addEventListener('click', (event) => {
        if (event.target === termsDialog) closeTermsDialog();
    });

    const errors = document.querySelector('[data-checkout-errors]');
    if (errors) {
        requestAnimationFrame(() => errors.focus({ preventScroll: true }));
    }

    form.addEventListener('submit', (event) => {
        if (!form.checkValidity()) {
            event.preventDefault();
            form.reportValidity();
            return;
        }

        const submit = document.querySelector('#submit-button');
        const loader = document.querySelector('#loader');
        if (submit) submit.disabled = true;
        if (loader) loader.hidden = false;
    });
}

function money(value) {
    const amount = Number(value || 0);
    return `${new Intl.NumberFormat(document.documentElement.lang || 'uk-UA', {
        maximumFractionDigits: amount % 1 === 0 ? 0 : 2,
    }).format(amount)} ${store.base_currency_name_short}`;
}

function showSummaryWithDelivery(response, form) {
    const data = response?.data || response;
    if (!data) return;

    document.querySelectorAll('.price-products').forEach((node) => { node.textContent = money(data.products); });
    document.querySelectorAll('.price-delivery').forEach((node) => {
        node.textContent = data.is_carrier ? translations.cart_delivery_price : money(data.delivery);
    });
    document.querySelectorAll('.price-discount').forEach((node) => { node.textContent = `−${money(data.discount)}`; });
    document.querySelectorAll('.total-price-delivery').forEach((node) => { node.textContent = money(data.total); });
    if (form) form.dataset.checkoutTotal = String(Number(data.total || 0));

    const discountRow = document.querySelector('[data-checkout-discount-row]');
    if (discountRow) discountRow.hidden = Number(data.discount || 0) <= 0;
}

function getSummaryByDeliveryTypeId(deliveryTypeId, success) {
    $.ajax({
        url: routes.cart.summary_with_delivery_route,
        type: 'get',
        data: { delivery_type_id: deliveryTypeId },
        dataType: 'json',
    }).done(success);
}
