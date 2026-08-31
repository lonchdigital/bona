@extends('layouts.store-main')

@section('title')

    @if(isset($config))
        @if($config->meta_title)
            <title>{{ $config->meta_title }}</title>
            <meta name="title" content="{{ $config->meta_title }}">
        @endif

        @if($config->meta_description)
            <meta name="description" content="{{ $config->meta_description }}">
        @endif
        @if($config->meta_keywords)
            <meta name="keywords" content="{{ $config->meta_keywords }}">
        @endif

        @if($config->meta_tags)
            {!! $config->meta_tags !!}
        @endif
    @endif

{{--    @foreach($slides as $slide)--}}
{{--        <link rel="preload" href="{{ $slide->slide_image_url }}" as="image">--}}
{{--    @endforeach--}}

@endsection

{{-- Without this the layout falls back to the 32x32 favicon, which is what
     every share of the home page was showing. --}}
@php
    $homeOgImage = App\Helpers\PreviewImage::url(optional($slides->first())->slide_image_path);
@endphp

@if($homeOgImage)
    @section('og_image', $homeOgImage)
@endif

@section('content')

    <x-store.home-hero :slides="$slides" />
    <x-store.call-consultation-modal :options="$applicationGlobalOptions" />

    @if( count($catalogCards) > 0 )
        <section class="bona-categories" aria-labelledby="products-by-type-title">
            <div class="bona-shell">
                <header class="bona-section-heading">
                    <p class="bona-kicker">{{ trans('base.storefront_catalog_kicker') }}</p>
                    <h2 id="products-by-type-title">{{ trans('base.products_by_type') }}</h2>
                </header>

                <div class="bona-categories__grid">
                    @foreach($catalogCards as $catalogCard)
                        <a
                            class="bona-category-card"
                            href="{{ $catalogCard['url'] }}"
                        >
                            <span class="bona-category-card__image">
                                @if($catalogCard['image_url'])
                                    <img src="{{ $catalogCard['image_url'] }}" alt="{{ $catalogCard['name'] }}" loading="lazy">
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

    <x-store.home-popular-products :products="$homePopularProducts" :base-currency="$baseCurrency" />
    <x-store.home-numbers />
    <x-store.home-ideas />
    <x-store.home-steps />
    <x-store.home-works :works="$homeWorks" />

    <x-store.home-reviews :testimonials="$homeTestimonials" />
    <x-store.home-instagram :feed="$instagramFeed" />
    <x-store.home-blog :articles="$articles" />
    <x-store.home-faq :faqs="$faqs" />
    <x-store.home-partners :brands="$brands" />

    @if($seoText)
    <!-- ======================== SEO ======================== -->
    <section class="seo-section pt-none11">
        <div class="container">

            <header>
                <div class="row">
                    <div class="col-12 text-center">
                        <h2 class="title h2">{{$seoText['title']}}</h2>
                    </div>
                </div>
            </header>

            {{-- Height limited with CSS only. The whole text stays in the
                 markup, which is what a crawler reads; nothing is loaded or
                 revealed by script, so none of it is hidden from search. --}}
            <div class="seo-content art-seo-scroll" tabindex="0">
                {!! $seoText['content'] !!}
            </div>

        </div>
    </section>
    @endif
@stop

@push('head')
    <style>
        .seo-section .art-seo-scroll {
            max-height: 340px;
            overflow-y: auto;
            padding-right: 18px;
            /* Scroll carries on to the page once the block reaches its end,
               so the reader is never trapped inside it on a phone. */
            scrollbar-width: thin;
        }

        .seo-section .art-seo-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .seo-section .art-seo-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .seo-section .art-seo-scroll::-webkit-scrollbar-thumb {
            background: #cccccc;
            border-radius: 3px;
        }

        .seo-section .art-seo-scroll::-webkit-scrollbar-thumb:hover {
            background: #999999;
        }

        .seo-section .art-seo-scroll > *:last-child {
            margin-bottom: 0;
        }

        @media (max-width: 767px) {
            .seo-section .art-seo-scroll {
                max-height: 60vh;
                padding-right: 12px;
            }
        }
    </style>
@endpush
