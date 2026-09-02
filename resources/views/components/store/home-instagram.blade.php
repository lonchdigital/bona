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
    $instagramPath = trim((string) parse_url($instagramUrl, PHP_URL_PATH), '/');
    $instagramUsername = ltrim(explode('/', $instagramPath)[0] ?? '', '@') ?: 'bona_doors';
    $feedItems = is_array($feed) ? array_values($feed) : [];
@endphp

@if($section['enabled'] ?? true)
<section class="bona-instagram" aria-labelledby="instagram-title">
    <header class="bona-section-heading">
        <p class="bona-kicker">{{ $localized($section['kicker'] ?? []) }}</p>
        <h2 id="instagram-title">{{ $sectionTitle }}</h2>
    </header>

    @if(count($feedItems) > 0)
        <div class="swiper bona-instagram__slider" data-instagram-slider>
            <div class="swiper-wrapper">
                @foreach($feedItems as $instagramItem)
                    @php
                        $itemCaption = trim((string) ($instagramItem['caption'] ?? ''));
                        $itemLabel = \Illuminate\Support\Str::limit($itemCaption ?: trans('base.instagram_no_caption'), 90);
                    @endphp
                    <div class="swiper-slide bona-instagram__slide">
                        <button
                            class="bona-instagram__item"
                            type="button"
                            data-instagram-open="{{ $loop->index }}"
                            aria-haspopup="dialog"
                            aria-controls="instagram-post-viewer"
                            aria-label="{{ trans('base.instagram_open_post', ['caption' => $itemLabel]) }}"
                        >
                            <img
                                src="{{ $instagramItem['image_url'] }}"
                                alt="{{ \Illuminate\Support\Str::limit($itemCaption ?: trans('base.instagram_no_caption'), 120) }}"
                                loading="lazy"
                                decoding="async"
                                width="640"
                                height="640"
                            >
                            @if(($instagramItem['media_type'] ?? null) === 'VIDEO')
                                <span class="bona-instagram__video" aria-hidden="true">
                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                        <path d="M6.75 4.8 13 9l-6.25 4.2V4.8Z" fill="currentColor"/>
                                    </svg>
                                </span>
                            @endif
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        <div
            class="bona-instagram-lightbox"
            id="instagram-post-viewer"
            data-instagram-lightbox
            data-likes-label="{{ trans('base.instagram_likes') }}"
            data-comments-label="{{ trans('base.instagram_comments') }}"
            data-empty-caption="{{ trans('base.instagram_no_caption') }}"
            role="dialog"
            aria-modal="true"
            aria-labelledby="instagram-dialog-title"
            aria-describedby="instagram-dialog-caption"
            hidden
        >
            <div class="bona-instagram-lightbox__dialog" role="document">
                <button
                    class="bona-instagram-lightbox__close"
                    type="button"
                    data-instagram-modal-close
                    aria-label="{{ trans('base.instagram_close_post') }}"
                >
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" aria-hidden="true">
                        <path d="M3 3l12 12M15 3 3 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </button>

                <div class="bona-instagram-lightbox__media">
                    <img data-instagram-modal-image alt="" hidden>
                    <video data-instagram-modal-video controls playsinline preload="metadata" hidden></video>

                    @if(count($feedItems) > 1)
                        <button
                            class="bona-instagram-lightbox__nav bona-instagram-lightbox__nav--prev"
                            type="button"
                            data-instagram-modal-prev
                            aria-label="{{ trans('base.instagram_previous_post') }}"
                        >
                            <svg width="28" height="12" viewBox="0 0 28 12" fill="none" aria-hidden="true">
                                <path d="M27 6H1M6 1 1 6l5 5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <button
                            class="bona-instagram-lightbox__nav bona-instagram-lightbox__nav--next"
                            type="button"
                            data-instagram-modal-next
                            aria-label="{{ trans('base.instagram_next_post') }}"
                        >
                            <svg width="28" height="12" viewBox="0 0 28 12" fill="none" aria-hidden="true">
                                <path d="M1 6h26M22 1l5 5-5 5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    @endif
                </div>

                <div class="bona-instagram-lightbox__details">
                    <header class="bona-instagram-lightbox__profile">
                        <span class="bona-instagram-lightbox__brand" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                <rect x="3" y="3" width="18" height="18" rx="5" stroke="currentColor" stroke-width="1.5"/>
                                <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.5"/>
                                <circle cx="17.4" cy="6.8" r="1" fill="currentColor"/>
                            </svg>
                        </span>
                        <div>
                            <h2 id="instagram-dialog-title">{{ '@'.$instagramUsername }}</h2>
                            <span>Instagram</span>
                        </div>
                    </header>

                    <p class="bona-instagram-lightbox__counter" data-instagram-modal-counter aria-live="polite"></p>

                    <div class="bona-instagram-lightbox__stats">
                        <span data-instagram-modal-likes-wrap hidden>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <strong data-instagram-modal-likes></strong>
                            <span>{{ trans('base.instagram_likes') }}</span>
                        </span>
                        <span data-instagram-modal-comments-wrap hidden>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 9.2 9.2 0 0 1-3.8-.8L3 21l1.7-5a8.5 8.5 0 1 1 16.3-4.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <strong data-instagram-modal-comments></strong>
                            <span>{{ trans('base.instagram_comments') }}</span>
                        </span>
                    </div>

                    <p class="bona-instagram-lightbox__caption" id="instagram-dialog-caption" data-instagram-modal-caption></p>
                    <time class="bona-instagram-lightbox__date" data-instagram-modal-date></time>

                    <a
                        class="bona-instagram-lightbox__original"
                        data-instagram-modal-permalink
                        href="{{ $instagramUrl }}"
                        target="_blank"
                        rel="noopener noreferrer nofollow"
                    >
                        {{ trans('base.instagram_view_original') }}
                        <svg width="27" height="12" viewBox="0 0 27 12" fill="none" aria-hidden="true">
                            <path d="M1 6h25M21 1l5 5-5 5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            </div>

            <script type="application/json" data-instagram-lightbox-data>{!! json_encode(
                $feedItems,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
            ) !!}</script>
        </div>
    @endif

    <div class="bona-instagram__cta">
        @if(count($feedItems) > 1)
            <button class="bona-instagram__nav bona-instagram__nav--prev" type="button" aria-label="{{ trans('base.instagram_previous_post') }}">
                <svg width="28" height="12" viewBox="0 0 28 12" fill="none" aria-hidden="true">
                    <path d="M27 6H1M6 1 1 6l5 5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
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
        @if(count($feedItems) > 1)
            <button class="bona-instagram__nav bona-instagram__nav--next" type="button" aria-label="{{ trans('base.instagram_next_post') }}">
                <svg width="28" height="12" viewBox="0 0 28 12" fill="none" aria-hidden="true">
                    <path d="M1 6h26M22 1l5 5-5 5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        @endif
    </div>
</section>
@endif
