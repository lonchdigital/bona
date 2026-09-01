@props([
    'slides',
    'section' => [],
])

@php
    $heroSlides = $slides->values();
    $slideCount = max(1, $heroSlides->count());
    $localized = static function ($value) {
        if (! is_array($value)) {
            return trim((string) $value);
        }

        return trim((string) ($value[app()->getLocale()] ?? collect($value)->first(fn ($text) => filled($text)) ?? ''));
    };
    $eyebrow = $localized($section['eyebrow'] ?? []) ?: trans('base.storefront_showroom');
    $secondaryLabel = $localized($section['secondary_label'] ?? []) ?: trans('base.services');
    $secondaryUrl = trim((string) ($section['secondary_url'] ?? ''))
        ?: App\Helpers\MultiLangRoute::getMultiLangRoute('store.services');
@endphp

@if($section['enabled'] ?? true)
<section class="bona-hero" aria-roledescription="carousel" aria-label="{{ trans('base.storefront_hero') }}" data-home-hero>
    @forelse($heroSlides as $slide)
        @php
            $heading = preg_match('/[\p{L}\p{N}]/u', (string) $slide->title) ? $slide->title : null;
            $targetUrl = $slide->slide_url ?: ($slide->display_button ? $slide->button_url : null);
            $buttonText = $slide->button_text ?: trans('base.storefront_choose_doors');
        @endphp
        <article
            class="bona-hero__slide{{ $loop->first ? ' is-active' : '' }}"
            aria-hidden="{{ $loop->first ? 'false' : 'true' }}"
            style="--hero-overlay: {{ max(.28, ($slide->overlay_opacity ?? 0) / 100) }}"
            data-hero-slide
        >
            <picture class="bona-hero__media">
                @if($slide->slide_image_mobile_url)
                    <source media="(max-width: 767px)" srcset="{{ $slide->slide_image_mobile_url }}">
                @endif
                <img src="{{ $slide->slide_image_url }}" alt="" width="1920" height="1080" decoding="async" @if(!$loop->first) loading="lazy" @else fetchpriority="high" @endif>
            </picture>
            <div class="bona-shell bona-hero__content">
                <div class="bona-hero__copy">
                    <div class="bona-hero__eyebrow"><span></span>{{ $eyebrow }}</div>
                    @if($loop->first)
                        <h1>{{ $heading ?: trans('base.home_h1') }}</h1>
                    @elseif($heading)
                        <h2>{{ $heading }}</h2>
                    @endif
                    @if($slide->description)
                        <div class="bona-hero__description">{!! $slide->description !!}</div>
                    @endif
                    @if($targetUrl)
                        <div class="bona-hero__actions">
                            @if($targetUrl === '#')
                                <a class="bona-button bona-button--light" href="#dialog-call-consultation" data-lead-modal-open="dialog-call-consultation">{{ $buttonText }}</a>
                            @else
                                <a class="bona-button bona-button--light" href="{{ $targetUrl }}">{{ $buttonText }}</a>
                            @endif
                            @if($secondaryLabel)
                                <a class="bona-button bona-button--ghost" href="{{ $secondaryUrl }}">{{ $secondaryLabel }}</a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </article>
    @empty
        <article class="bona-hero__slide bona-hero__slide--empty is-active" aria-hidden="false" data-hero-slide>
            <div class="bona-shell bona-hero__content">
                <div class="bona-hero__copy">
                    <div class="bona-hero__eyebrow"><span></span>{{ $eyebrow }}</div>
                    <h1>{{ trans('base.home_h1') }}</h1>
                    <p class="bona-hero__description">{{ trans('base.storefront_hero_fallback') }}</p>
                    <div class="bona-hero__actions">
                        <a class="bona-button bona-button--light" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.all-products.page') }}">{{ trans('base.storefront_choose_doors') }}</a>
                        @if($secondaryLabel)
                            <a class="bona-button bona-button--ghost" href="{{ $secondaryUrl }}">{{ $secondaryLabel }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </article>
    @endforelse

    @if($slideCount > 1)
        <div class="bona-hero__controls">
            <div class="bona-shell bona-hero__controls-inner">
                <div class="bona-hero__dots" role="tablist" aria-label="{{ trans('base.storefront_slides') }}">
                    @foreach($heroSlides as $slide)
                        <button
                            class="bona-hero__dot{{ $loop->first ? ' is-active' : '' }}"
                            type="button"
                            aria-label="{{ trans('base.storefront_slide_number', ['number' => $loop->iteration]) }}"
                            aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                            data-hero-dot="{{ $loop->index }}"
                        ></button>
                    @endforeach
                </div>
                <div class="bona-hero__nav">
                    <span><b data-hero-current>01</b> / {{ str_pad((string) $slideCount, 2, '0', STR_PAD_LEFT) }}</span>
                    <button type="button" aria-label="{{ trans('base.storefront_previous_slide') }}" data-hero-previous>←</button>
                    <button type="button" aria-label="{{ trans('base.storefront_next_slide') }}" data-hero-next>→</button>
                </div>
            </div>
        </div>
    @endif
</section>
@endif
