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
