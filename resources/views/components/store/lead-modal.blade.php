@props([
    'id',
    'action',
    'formType',
    'kicker',
    'title',
    'timeLabel' => null,
    'intro' => null,
    'submitLabel',
    'successKicker',
    'successTitle',
    'successText',
    'wide' => false,
])

@php
    $titleId = $id.'-title';
    $introId = $intro ? $id.'-intro' : null;
@endphp

<div
    id="{{ $id }}"
    class="bona-lead-modal {{ $wide ? 'bona-lead-modal--wide' : '' }}"
    data-lead-modal
    hidden
>
    <section
        class="bona-lead-modal__dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $titleId }}"
        @if($introId) aria-describedby="{{ $introId }}" @endif
    >
        <button class="bona-lead-modal__close" type="button" data-lead-modal-close aria-label="{{ trans('base.lead_modal_close') }}">
            <span></span><span></span>
        </button>

        <div class="bona-lead-modal__form-view" data-lead-modal-form-view>
            <div class="bona-lead-modal__head">
                <div>
                    <p class="bona-lead-modal__kicker">{{ $kicker }}</p>
                    <h2 id="{{ $titleId }}">{{ $title }}</h2>
                </div>
                @if($timeLabel)
                    <span class="bona-lead-modal__time">{{ $timeLabel }}</span>
                @endif
            </div>

            @if($intro)
                <p class="bona-lead-modal__intro" id="{{ $introId }}">{{ $intro }}</p>
            @endif

            <form
                class="bona-lead-form"
                action="{{ $action }}"
                method="post"
                data-lead-form="{{ $formType }}"
                data-sending-label="{{ trans('base.lead_sending') }}"
                data-error-label="{{ trans('base.lead_submit_error') }}"
                data-phone-error="{{ trans('base.lead_phone_invalid') }}"
            >
                @csrf
                {{ $slot }}

                <p class="bona-lead-form__error" data-lead-form-error role="alert" hidden></p>
                <button class="bona-lead-form__submit" type="submit" data-submit-label="{{ $submitLabel }}">
                    <span>{{ $submitLabel }}</span>
                    <svg viewBox="0 0 24 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="M1 6h21M17 1l5 5-5 5"></path>
                    </svg>
                </button>
            </form>
        </div>

        <div class="bona-lead-modal__thanks" data-lead-modal-thanks hidden tabindex="-1">
            <span class="bona-lead-modal__success" aria-hidden="true">✓</span>
            <p class="bona-lead-modal__kicker">{{ $successKicker }}</p>
            <h2>{{ $successTitle }}</h2>
            <p>{{ $successText }}</p>
            <button class="bona-lead-form__submit bona-lead-form__submit--center" type="button" data-lead-modal-close>
                <span>{{ trans('base.lead_modal_return') }}</span>
            </button>
        </div>
    </section>
</div>
