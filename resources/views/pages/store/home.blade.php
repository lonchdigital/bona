@extends('layouts.store-main')

@php
    $homeMetaTitle = trim((string) $config->meta_title) ?: trans('base.home_meta_title');
    $homeMetaDescription = trim((string) $config->meta_description) ?: trans('base.home_meta_description');
@endphp

@section('seo_title', $homeMetaTitle)
@section('meta_description', $homeMetaDescription)
@section('meta_keywords', (string) $config->meta_keywords)
@section('og_title', $homeMetaTitle)
@section('og_description', $homeMetaDescription)
@section('og_image_alt', $homeMetaTitle)

{{-- Without this the layout falls back to the 32x32 favicon, which is what
     every share of the home page was showing. --}}
@php
    $firstHomeSlide = $slides->first();
    $homeOgImage = App\Helpers\PreviewImage::url(optional($firstHomeSlide)->slide_image_path);
@endphp

@if($homeOgImage)
    @section('og_image', $homeOgImage)
@endif

@if($firstHomeSlide?->slide_image_url)
    @push('head')
        @if($firstHomeSlide->slide_image_mobile_url)
            <link rel="preload" as="image" href="{{ $firstHomeSlide->slide_image_mobile_url }}" media="(max-width: 767px)" fetchpriority="high">
            <link rel="preload" as="image" href="{{ $firstHomeSlide->slide_image_url }}" media="(min-width: 768px)" fetchpriority="high">
        @else
            <link rel="preload" as="image" href="{{ $firstHomeSlide->slide_image_url }}" fetchpriority="high">
        @endif
    @endpush
@endif

@push('structured_data')
    <script type="application/ld+json">{!! json_encode(
        app(App\Services\Seo\HomePageSchemaService::class)->build($config, $slides, $homePopularProducts),
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG
    ) !!}</script>
@endpush

@section('content')

    @php
        $homeSectionText = static function ($value) {
            if (! is_array($value)) {
                return trim((string) $value);
            }

            return trim((string) ($value[app()->getLocale()] ?? collect($value)->first(fn ($text) => filled($text)) ?? ''));
        };
        $catalogSection = $homeSections['catalog'] ?? [];
    @endphp

    <x-store.home-hero :slides="$slides" :section="$homeSections['hero'] ?? []" />
    <x-store.call-consultation-modal :options="$applicationGlobalOptions" />

    @if(($catalogSection['enabled'] ?? true) && count($catalogCards) > 0)
        <section class="bona-categories" aria-labelledby="products-by-type-title">
            <div class="bona-shell">
                <header class="bona-section-heading">
                    <p class="bona-kicker">{{ $homeSectionText($catalogSection['kicker'] ?? []) }}</p>
                    <h2 id="products-by-type-title">{{ $homeSectionText($catalogSection['title'] ?? []) }}</h2>
                </header>

                <div class="bona-categories__grid">
                    @foreach($catalogCards as $catalogCard)
                        <a
                            class="bona-category-card"
                            href="{{ $catalogCard['url'] }}"
                        >
                            <span class="bona-category-card__image">
                                @if($catalogCard['image_url'])
                                    <img src="{{ $catalogCard['image_url'] }}" alt="{{ $catalogCard['name'] }}" loading="lazy" decoding="async" width="720" height="560">
                                @else
                                    <span class="bona-category-card__placeholder" aria-hidden="true">BONA</span>
                                @endif
                            </span>
                            <span class="bona-category-card__row">
                                <span class="bona-category-card__name">{{ $catalogCard['name'] }}</span>
                                <span class="bona-category-card__arrow" aria-hidden="true">
                                    <svg width="34" height="10" viewBox="0 0 34 10" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="0" y1="5" x2="32" y2="5"></line>
                                        <path d="M27 1 L32 5 L27 9"></path>
                                    </svg>
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-store.home-style-selector :section="$styleSection" />

    <x-store.home-popular-products :products="$homePopularProducts" :base-currency="$baseCurrency" :section="$homeSections['popular'] ?? []" />
    <x-store.home-numbers :section="$homeSections['numbers'] ?? []" />
    <x-store.home-ideas :section="$homeSections['ideas'] ?? []" />
    <x-store.home-steps :section="$homeSections['steps'] ?? []" />
    <x-store.home-works :section="$homeSections['works'] ?? []" />

    <x-store.home-reviews :testimonials="$homeTestimonials" :section="$homeSections['reviews'] ?? []" />
    <x-store.home-instagram :feed="$instagramFeed" :section="$homeSections['instagram'] ?? []" />
    <x-store.home-blog :articles="$articles" :section="$homeSections['blog'] ?? []" />
    <x-store.home-faq :faqs="$faqs" :section="$homeSections['faq'] ?? []" />
    <x-store.home-partners :brands="$brands" :section="$homeSections['partners'] ?? []" />

    @if(($homeSections['seo']['enabled'] ?? true) && $seoText)
    <!-- ======================== SEO ======================== -->
    <section class="bona-seo">
        <div class="bona-shell">
            <header class="bona-section-heading bona-seo__heading">
                <h2>{{$seoText['title']}}</h2>
            </header>

            {{-- Height limited with CSS only. The whole text stays in the
                 markup, which is what a crawler reads; nothing is loaded or
                 revealed by script, so none of it is hidden from search. --}}
            <div class="bona-seo__content art-seo-scroll" tabindex="0">
                {!! $seoText['content'] !!}
            </div>

        </div>
    </section>
    @endif
@stop

@push('head')
    <style>
        .bona-seo .art-seo-scroll {
            max-height: 340px;
            overflow-y: auto;
            padding-right: 18px;
            /* Scroll carries on to the page once the block reaches its end,
               so the reader is never trapped inside it on a phone. */
            scrollbar-width: thin;
        }

        .bona-seo .art-seo-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .bona-seo .art-seo-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .bona-seo .art-seo-scroll::-webkit-scrollbar-thumb {
            background: #cccccc;
            border-radius: 3px;
        }

        .bona-seo .art-seo-scroll::-webkit-scrollbar-thumb:hover {
            background: #999999;
        }

        .bona-seo .art-seo-scroll > *:last-child {
            margin-bottom: 0;
        }

        @media (max-width: 767px) {
            .bona-seo .art-seo-scroll {
                max-height: 60vh;
                padding-right: 12px;
            }
        }
    </style>
@endpush
