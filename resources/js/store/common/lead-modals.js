const PHONE_PREFIX = '+38 (0';
const PHONE_PREFIX_LENGTH = PHONE_PREFIX.length;

function editablePhoneDigits(value) {
    let digits = String(value || '').replace(/\D/g, '');

    if (digits.startsWith('380')) {
        digits = digits.slice(3);
    } else if (digits.startsWith('38')) {
        digits = digits.slice(2);

        if (digits.startsWith('0')) digits = digits.slice(1);
    } else if (digits.startsWith('0')) {
        digits = digits.slice(1);
    }

    return digits.slice(0, 9);
}

function formatUaPhone(value) {
    const digits = editablePhoneDigits(value);
    let formatted = PHONE_PREFIX + digits.slice(0, 2);

    if (digits.length >= 2) formatted += ')';
    if (digits.length > 2) formatted += ' ' + digits.slice(2, 5);
    if (digits.length > 5) formatted += ' ' + digits.slice(5, 7);
    if (digits.length > 7) formatted += ' ' + digits.slice(7, 9);

    return formatted;
}

function phoneCaretPosition(value, digitCount) {
    if (digitCount <= 0) return PHONE_PREFIX_LENGTH;

    let seen = 0;
    let position = PHONE_PREFIX_LENGTH;

    for (let index = PHONE_PREFIX_LENGTH; index < value.length; index += 1) {
        if (/\d/.test(value[index])) seen += 1;
        position = index + 1;

        if (seen === digitCount) break;
    }

    while (position < value.length && !/\d/.test(value[position])) position += 1;

    return position;
}

function isCompleteUaPhone(value) {
    return /^\+38 \(0\d{2}\) \d{3} \d{2} \d{2}$/.test(String(value || ''));
}

function bindUaPhone(input) {
    if (!(input instanceof HTMLInputElement) || input.dataset.uaPhoneBound === 'true') return;

    input.dataset.uaPhoneBound = 'true';
    input.type = 'tel';
    input.inputMode = 'tel';
    input.autocomplete = input.autocomplete || 'tel';

    if (input.value) input.value = formatUaPhone(input.value);

    input.addEventListener('focus', () => {
        if (!input.value) {
            input.value = PHONE_PREFIX;
            input.setSelectionRange(PHONE_PREFIX_LENGTH, PHONE_PREFIX_LENGTH);
        }
    });

    input.addEventListener('keydown', (event) => {
        if (!['Backspace', 'Delete'].includes(event.key) || input.selectionStart !== input.selectionEnd) return;

        let position = input.selectionStart ?? PHONE_PREFIX_LENGTH;

        if (event.key === 'Backspace') {
            while (position > PHONE_PREFIX_LENGTH && !/\d/.test(input.value[position - 1])) position -= 1;
            input.setSelectionRange(position, position);
            return;
        }

        while (position < input.value.length && !/\d/.test(input.value[position])) position += 1;
        if (position < input.value.length) input.setSelectionRange(position, position + 1);
    });

    input.addEventListener('input', () => {
        const rawValue = input.value;
        const rawCaret = input.selectionStart ?? rawValue.length;
        const digitsBeforeCaret = editablePhoneDigits(rawValue.slice(0, rawCaret)).length;
        const formatted = formatUaPhone(rawValue);

        input.value = formatted;
        input.setCustomValidity('');

        const nextCaret = phoneCaretPosition(formatted, digitsBeforeCaret);
        input.setSelectionRange(nextCaret, nextCaret);
    });

    input.addEventListener('blur', () => {
        if (input.value === PHONE_PREFIX) input.value = '';
    });
}

function bindPhoneFields(root = document) {
    root.querySelectorAll('.js-ua-phone, .phone-field, input.phone').forEach(bindUaPhone);
}

function updateRating(group) {
    const checked = group.querySelector('input:checked');
    const selected = checked ? Number(checked.value) : 0;

    group.querySelectorAll('label').forEach((label) => {
        const input = document.getElementById(label.htmlFor);
        label.classList.toggle('is-active', Boolean(input) && Number(input.value) <= selected);
    });
}

function clearFormErrors(form) {
    form.querySelectorAll('.bona-lead-field__error').forEach((error) => error.remove());
    form.querySelectorAll('[aria-invalid="true"]').forEach((field) => field.removeAttribute('aria-invalid'));

    const formError = form.querySelector('[data-lead-form-error]');
    if (formError) {
        formError.hidden = true;
        formError.textContent = '';
    }
}

function renderFieldErrors(form, errors) {
    let firstInvalid = null;

    Object.entries(errors || {}).forEach(([name, messages]) => {
        const field = form.elements.namedItem(name);
        const input = field instanceof RadioNodeList ? field[0] : field;
        if (!(input instanceof HTMLElement)) return;

        input.setAttribute('aria-invalid', 'true');
        if (!firstInvalid) firstInvalid = input;

        const wrapper = input.closest('.bona-lead-field, .bona-lead-consent, fieldset');
        if (!wrapper) return;

        const error = document.createElement('small');
        error.className = 'bona-lead-field__error';
        error.textContent = Array.isArray(messages) ? messages[0] : String(messages);
        wrapper.appendChild(error);
    });

    if (firstInvalid && typeof firstInvalid.focus === 'function') firstInvalid.focus();
}

function showFormError(form, message) {
    const error = form.querySelector('[data-lead-form-error]');
    if (!error) return;

    error.textContent = message;
    error.hidden = false;
}

function fillChoiceDescription(form) {
    const description = form.querySelector('input[name="description"]');
    if (!description) return;

    description.value = Array.from(form.querySelectorAll('[data-lead-choice-group]')).map((group) => {
        const legend = group.querySelector('legend')?.textContent.trim();
        const selected = group.querySelector('input:checked')?.closest('label')?.querySelector('span')?.textContent.trim();

        return legend && selected ? `${legend} ${selected}` : '';
    }).filter(Boolean).join('\n');
}

function validatePhones(form) {
    let valid = true;

    form.querySelectorAll('.js-ua-phone, .phone-field, input.phone').forEach((input) => {
        const complete = !input.value || isCompleteUaPhone(input.value);
        input.setCustomValidity(complete ? '' : form.dataset.phoneError);
        if (!complete) valid = false;
    });

    return valid;
}

function resetModal(modal) {
    const form = modal.querySelector('[data-lead-form]');
    const formView = modal.querySelector('[data-lead-modal-form-view]');
    const thanks = modal.querySelector('[data-lead-modal-thanks]');

    if (form) {
        form.reset();
        clearFormErrors(form);
        form.querySelectorAll('[data-lead-rating]').forEach(updateRating);

        const submit = form.querySelector('[type="submit"]');
        if (submit) {
            submit.disabled = false;
            const label = submit.querySelector('span');
            if (label) label.textContent = submit.dataset.submitLabel;
        }
    }

    if (formView) formView.hidden = false;
    if (thanks) thanks.hidden = true;
}

export default {
    init: async function () {
        let activeModal = null;
        let returnFocus = null;

        bindPhoneFields();

        document.querySelectorAll('[data-lead-rating]').forEach((group) => {
            updateRating(group);
            group.addEventListener('change', () => updateRating(group));
        });

        const closeModal = (modal) => {
            if (!modal) return;

            modal.hidden = true;
            document.body.classList.remove('bona-lead-modal-open');
            resetModal(modal);
            activeModal = null;

            if (returnFocus && typeof returnFocus.focus === 'function') returnFocus.focus();
            returnFocus = null;
        };

        const openModal = (id, trigger) => {
            const modal = document.getElementById(id);
            if (!modal?.matches('[data-lead-modal]')) return false;

            if (activeModal && activeModal !== modal) closeModal(activeModal);

            activeModal = modal;
            returnFocus = trigger || document.activeElement;
            modal.hidden = false;
            document.body.classList.add('bona-lead-modal-open');

            const firstField = modal.querySelector('[data-lead-modal-form-view] input:not([type="hidden"]):not([type="radio"]):not([type="checkbox"]), [data-lead-modal-form-view] textarea, [data-lead-modal-form-view] button');
            window.setTimeout(() => firstField?.focus(), 40);

            return true;
        };

        document.addEventListener('click', (event) => {
            const opener = event.target.closest('[data-lead-modal-open]');
            if (opener) {
                const id = opener.dataset.leadModalOpen || String(opener.getAttribute('href') || '').replace(/^#/, '');
                if (openModal(id, opener)) event.preventDefault();
                return;
            }

            const closer = event.target.closest('[data-lead-modal-close]');
            if (closer) {
                event.preventDefault();
                closeModal(closer.closest('[data-lead-modal]'));
                return;
            }

            if (event.target.matches('[data-lead-modal]')) closeModal(event.target);
        });

        document.addEventListener('keydown', (event) => {
            if (!activeModal) return;

            if (event.key === 'Escape') {
                event.preventDefault();
                closeModal(activeModal);
                return;
            }

            if (event.key !== 'Tab') return;

            const focusable = Array.from(activeModal.querySelectorAll('button:not([disabled]), input:not([disabled]), textarea:not([disabled]), a[href]'))
                .filter((item) => !item.closest('[hidden]') && item.offsetParent !== null);
            if (!focusable.length) return;

            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });

        document.querySelectorAll('[data-lead-form]').forEach((form) => {
            const submit = form.querySelector('[type="submit"]');
            if (submit) submit.dataset.submitLabel = submit.dataset.submitLabel || submit.querySelector('span')?.textContent || '';

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                clearFormErrors(form);
                fillChoiceDescription(form);
                validatePhones(form);

                if (!form.reportValidity()) return;

                if (submit) {
                    submit.disabled = true;
                    const label = submit.querySelector('span');
                    if (label) label.textContent = form.dataset.sendingLabel;
                }

                try {
                    const response = await fetch(form.action, {
                        method: String(form.method || 'post').toUpperCase(),
                        body: new FormData(form),
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        if (response.status === 422) renderFieldErrors(form, payload.errors);
                        else showFormError(form, payload.message || form.dataset.errorLabel);
                        return;
                    }

                    const modal = form.closest('[data-lead-modal]');
                    const formView = modal?.querySelector('[data-lead-modal-form-view]');
                    const thanks = modal?.querySelector('[data-lead-modal-thanks]');
                    if (formView) formView.hidden = true;
                    if (thanks) {
                        thanks.hidden = false;
                        thanks.focus();
                    }

                    window.dataLayer = window.dataLayer || [];
                    const eventName = form.elements.namedItem('event')?.value;
                    if (eventName) window.dataLayer.push({ event: eventName });
                } catch (error) {
                    showFormError(form, form.dataset.errorLabel);
                } finally {
                    if (submit) {
                        submit.disabled = false;
                        const label = submit.querySelector('span');
                        if (label) label.textContent = submit.dataset.submitLabel;
                    }
                }
            });
        });
    }
};

export { editablePhoneDigits, formatUaPhone, isCompleteUaPhone };
