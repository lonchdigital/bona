@props(['feed' => null, 'section' => []])

@php
    $localized = static function ($value) {
        if (! is_array($value)) {
            return trim((string) $value);
        }

        return trim((string) ($value[app()->getLocale()] ?? collect($value)->first(fn ($text) => filled($text)) ?? ''));
    };
    $sectionTitle = $localized($section['title'] ?? []) ?: trans('base.we_are_in_instagram');
    $instagramUrl = trim((string) ($section['link_url'] ?? '')) ?: 'https://www.instagram.com/bona_doors/';
@endphp

@if($section['enabled'] ?? true)
<section class="bona-instagram" aria-labelledby="instagram-title">
    <header class="bona-section-heading">
        <p class="bona-kicker">{{ $localized($section['kicker'] ?? []) }}</p>
        <h2 id="instagram-title">{{ $sectionTitle }}</h2>
    </header>

    @if(is_array($feed) && count($feed) > 0)
        <div class="swiper bona-instagram__slider" data-instagram-slider>
            <div class="swiper-wrapper">
                @foreach($feed as $instagramItem)
                    <a
                        class="swiper-slide bona-instagram__item"
                        href="{{ $instagramItem['permalink'] }}"
                        target="_blank"
                        rel="noopener noreferrer nofollow"
                        aria-label="{{ $sectionTitle }}: {{ \Illuminate\Support\Str::limit($instagramItem['caption'] ?: '@bona_doors', 90) }}"
                    >
                        <img
                            src="{{ $instagramItem['image_url'] }}"
                            alt="{{ \Illuminate\Support\Str::limit($instagramItem['caption'] ?: 'Bona Doors в Instagram', 120) }}"
                            loading="lazy"
                            decoding="async"
                        >
                        @if(($instagramItem['media_type'] ?? null) === 'VIDEO')
                            <span class="bona-instagram__video" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                    <path d="M6.75 4.8 13 9l-6.25 4.2V4.8Z" fill="currentColor"/>
                                </svg>
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="bona-instagram__cta">
        @if(is_array($feed) && count($feed) > 1)
            <button class="bona-instagram__nav bona-instagram__nav--prev" type="button" aria-label="Попередні публікації">
                <span aria-hidden="true">←</span>
            </button>
        @endif
        @if($localized($section['link_label'] ?? []))
            <a
                href="{{ $instagramUrl }}"
                class="bona-instagram__button"
                target="_blank"
                rel="noopener noreferrer nofollow"
            >
                {{ $localized($section['link_label']) }}
            </a>
        @endif
        @if(is_array($feed) && count($feed) > 1)
            <button class="bona-instagram__nav bona-instagram__nav--next" type="button" aria-label="Наступні публікації">
                <span aria-hidden="true">→</span>
            </button>
        @endif
    </div>
</section>
@endif
