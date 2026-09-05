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
        if (refreshSummary) getSummaryByDeliveryTypeId(input.value, showSummaryWithDelivery);
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
    if (activeRecipient) showPanel('.accordion-recipient-data', activeRecipient.dataset.accordion);
    if (activePayment) onPaymentChange(activePayment);

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

function showSummaryWithDelivery(response) {
    const data = response?.data || response;
    if (!data) return;

    document.querySelectorAll('.price-products').forEach((node) => { node.textContent = money(data.products); });
    document.querySelectorAll('.price-delivery').forEach((node) => {
        node.textContent = data.is_carrier ? translations.cart_delivery_price : money(data.delivery);
    });
    document.querySelectorAll('.price-discount').forEach((node) => { node.textContent = `−${money(data.discount)}`; });
    document.querySelectorAll('.total-price-delivery').forEach((node) => { node.textContent = money(data.total); });

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
