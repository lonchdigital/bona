@php
    $consultantVisibility = in_array($consultantVisibility ?? null, ['desktop', 'mobile'], true)
        ? $consultantVisibility
        : 'all';
@endphp

<article
    @class([
        'bona-catalog__consultant',
        'bona-catalog__consultant--desktop' => $consultantVisibility === 'desktop',
        'bona-catalog__consultant--mobile' => $consultantVisibility === 'mobile',
    ])
    aria-label="{{ trans('base.catalog_consultation_aria') }}"
    data-catalog-consultant="{{ $consultantVisibility }}"
>
    <div class="bona-catalog__consultant-top">
        <span>{{ trans('base.catalog_help_kicker') }}</span>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z"></path>
            <path d="M8 10h8M8 14h5"></path>
        </svg>
    </div>
    <div class="bona-catalog__consultant-person">
        <img src="{{ Vite::asset('bona-html/img/manager-oksana.webp') }}" alt="{{ trans('base.catalog_consultant_photo_alt') }}" loading="lazy">
        <div>
            <strong>{{ trans('base.catalog_consultant_name') }}</strong>
            <span>{{ trans('base.catalog_consultant_role') }}</span>
        </div>
    </div>
    <div class="bona-catalog__consultant-copy">
        <h2>{{ trans('base.catalog_consultant_title') }}</h2>
        <p>{{ trans('base.catalog_consultant_text') }}</p>
    </div>
    <a class="bona-catalog__consultant-button" href="#dialog-call-consultation" data-lead-modal-open="dialog-call-consultation">
        {{ trans('base.catalog_get_consultation') }}
        <span aria-hidden="true">→</span>
    </a>
</article>
