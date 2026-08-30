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

    @if( count($productTypes) > 0 )
        <section class="art-home-page art-products-category">
            <div class="container">

                <header>
                    <div class="row">
                        <div class="col-12 text-center">
                            <h2 class="title h2">{{trans('base.products_by_type')}}</h2>
                            <div class="subtitle font-two">
                                <p>{{trans('base.doors_category')}}</p>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="art-category-list">
                    @foreach($productTypes as $productType)
                        <div class="art-category-item">
                            <article>
                                <div class="figure-grid">
                                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => $productType->slug]) }}">
                                        <div class="image">
                                            <img src="{{ $productType->image_url }}" alt="Product Type Image" loading="lazy">
                                        </div>
                                        <div class="text">
                                            <span class="title h4">{{ $productType->name }}</span>
                                        </div>
                                    </a>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif


    @if(count($homeNewProducts))
        <!-- ======================== New Products  ======================== -->
        <section class="products">
            <div class="container">

                <div class="art-products-slider-wrapper art-carousel">
                    <div class="swiper art-products-owl-items art-new-products art-big-wrapper art-swiper-common">
                        <div class="swiper-wrapper">
                        @foreach($homeNewProducts as $product)
                            <div class="swiper-slide">
                                @include('pages.store.partials.product_item', ['product' => $product->product, 'baseCurrency' => $baseCurrency])
                            </div>
                        @endforeach
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                </div> <!--/row-->

            </div> <!--/container-->
        </section>
    @endif


    <x-precise-form-component />

    @if(count($homeBestSalesProducts))
        <!-- ======================== Best Sales Products  ======================== -->
        <section class="products">
            <div class="container">
                <header>
                    <div class="row">
                        <div class="col-12 text-center">
                            <h2 class="title h2">{{trans('base.best_sales')}}</h2>
                            <div class="subtitle font-two">
                                <p>Check out our latest collections</p>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="art-products-slider-wrapper art-carousel">
                    <div class="swiper art-products-owl-items art-best-products art-big-wrapper art-swiper-common">
                        <div class="swiper-wrapper">
                            @foreach($homeBestSalesProducts as $product)
                                <div class="swiper-slide">
                                    @include('pages.store.partials.product_item', ['product' => $product->product, 'baseCurrency' => $baseCurrency])
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                </div> <!--/row-->

            </div> <!--/container-->
        </section>
    @endif

    @if(!is_null($instagramFeed) && count($instagramFeed))
        <!-- ========================  Instagram ======================== -->
        <section class="instagram">
            <header>
                <div class="row">
                    <div class="col-12 text-center">
                        <h2 class="title h2">{{ trans('base.we_are_in_instagram') }}</h2>
                    </div>
                </div>
            </header>
            <div class="gallery clearfix mt-10">
                <div class="swiper art-instagram-owl-items art-instagram art-big-wrapper art-swiper-common" id="art-instagram-owl-items">
                    <div class="swiper-wrapper">
                        @foreach($instagramFeed as $instagramItem)
                            @if(isset($instagramItem['media_url']) && $instagramItem['media_type'] != 'VIDEO')
                                <a href="{{ $instagramItem['permalink'] }}" target="_blank" class="swiper-slide">
                                    <img src="{{ $instagramItem['media_url'] }}" alt="Alternate Text" loading="lazy">
                                </a>
                            @endif
                        @endforeach
                    </div>
                    <div class="swiper-pagination"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div>
                </div>
            </div>

            <div class="wrapper-more">
                <a href="https://www.instagram.com/bona_doors/" class="btn btn-empty color-dark" target="_blank">{{trans('base.subscribe')}}</a>
            </div>

        </section>
    @endif

    <section class="instagram">
        <header>
            <div class="row">
                <div class="col-12 text-center">
                    <h2 class="title h2">{{ trans('base.we_are_in_instagram') }}</h2>
                </div>
            </div>
        </header>
        <!-- Elfsight Instagram Feed | Untitled Instagram Feed -->
        <script src="https://elfsightcdn.com/platform.js" async></script>
        <div class="elfsight-app-f85feb4c-b53a-43eb-a103-554c0704ae89" data-elfsight-app-lazy></div>
    </section>

    <!-- ========================  Blog ======================== -->

    <section class="blog">
        <div class="container">

            <header>
                <div class="row">
                    <div class="col-12 text-center">
                        <h2 class="title h2">{{trans('base.blog')}}</h2>
                        <div class="subtitle font-two">
                            <p>{{trans('base.blog_latest')}}</p>
                        </div>
                    </div>
                </div>
            </header>

            <div class="row art-blog-wrapper">
                @foreach($articles as $article)
                    <div class="col-md-6 col-lg-4">
                        @include('pages.store.partials.article_item', ['article' => $article])
                    </div>
                @endforeach
            </div> <!--/row-->

            <div class="wrapper-more">
                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('blog.main.page') }}" class="btn btn-empty color-dark">{{trans('base.blog_all')}}</a>
            </div>

        </div> <!--/container-->
    </section>

    <!-- ======================== Quotes ======================== -->

    <section class="quotes quotes-slider" style="background-image:url({{ asset('storage/bg-images/testimonials-bg.png') }})">
        <div class="container">

            <!-- === quotes header === -->
            <header>
                <h2 class="title h2">{{ trans('base.client_testimonials') }}</h2>
            </header>

            <div class="row">

                <div class="swiper art-quote-carousel-home quote-carousel">
                    <div class="swiper-wrapper">
                        @foreach($homeTestimonials as $testimonial)
                            <div class="swiper-slide">
                                <div class="quote art-quote-review">

                                    <div class="quote-top">
                                        <div class="image">
                                            <img src="{{ $testimonial->testimonial_image_url }}" alt="Testimonial image" loading="lazy">
                                        </div>
                                        <div class="name">
                                            @if( !is_null( $testimonial->url ) )
                                                <h4><a href="{{ $testimonial->url }}" target="_blank">{{ $testimonial->name }}</a></h4>
                                            @else
                                                <h4>{{ $testimonial->name }}</h4>
                                            @endif
                                        </div>
                                        <div class="text">
                                            <p>{{ $testimonial->review }}</p>
                                        </div>
                                    </div>

                                    <div class="more">
                                        @if( !is_null( $testimonial->date ) )
                                            <span class="date">
                                                {{ Carbon\Carbon::createFromFormat('Y-m-d', $testimonial->date)->format('d.m.Y') }}
                                            </span>
                                        @endif
                                        <div class="rating">
                                            @for ($i = 1; $i <= 5; $i++)
                                                @if ($i <= $testimonial->rating)
                                                    <svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M15.3113 19C15.2563 19 15.2082 18.9927 15.167 18.9782C15.1258 18.9636 15.0846 18.949 15.0434 18.9345L9.5 15.7241L3.95662 18.9345C3.86045 18.9782 3.76428 19 3.66811 19C3.57194 19 3.48265 18.9636 3.40022 18.8908C3.31779 18.8326 3.25597 18.7525 3.21475 18.6506C3.17354 18.5487 3.1598 18.4467 3.17354 18.3448L4.1833 11.9241L0.164859 7.66552C0.0961678 7.57816 0.0480839 7.48352 0.0206074 7.38161C-0.00686913 7.27969 -0.00686913 7.18506 0.0206074 7.0977C0.0618221 6.99579 0.116775 6.90843 0.185466 6.83563C0.254158 6.76284 0.343456 6.71916 0.453362 6.7046L6.51193 5.63448L9.02603 0.305748C9.06725 0.203833 9.1325 0.127396 9.2218 0.076437C9.3111 0.0254784 9.40383 0 9.5 0C9.59617 0 9.6889 0.0254784 9.7782 0.076437C9.8675 0.127396 9.93275 0.203833 9.97397 0.305748L12.4881 5.63448L18.5466 6.7046C18.6565 6.71916 18.7458 6.76284 18.8145 6.83563C18.8832 6.90843 18.9382 6.99579 18.9794 7.0977C19.0069 7.18506 19.0069 7.27969 18.9794 7.38161C18.9519 7.48352 18.9038 7.57816 18.8351 7.66552L14.8167 11.9241L15.8265 18.3448C15.8402 18.4467 15.8265 18.5487 15.7852 18.6506C15.744 18.7525 15.6822 18.8326 15.5998 18.8908C15.5586 18.9345 15.5139 18.9636 15.4658 18.9782C15.4178 18.9927 15.3662 19 15.3113 19Z" fill="white"/>
                                                    </svg>
                                                @else
                                                    <svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M15.3113 19C15.2563 19 15.2082 18.9927 15.167 18.9782C15.1258 18.9636 15.0846 18.949 15.0434 18.9345L9.5 15.7241L3.95662 18.9345C3.86045 18.9782 3.76428 19 3.66811 19C3.57194 19 3.48265 18.9636 3.40022 18.8908C3.31779 18.8326 3.25597 18.7525 3.21475 18.6506C3.17354 18.5487 3.1598 18.4467 3.17354 18.3448L4.1833 11.9241L0.164859 7.66552C0.0961678 7.57816 0.0480839 7.48353 0.0206074 7.38161C-0.00686913 7.27969 -0.00686913 7.18506 0.0206074 7.0977C0.0618221 6.99579 0.116775 6.90843 0.185466 6.83563C0.254158 6.76284 0.343456 6.71916 0.453362 6.7046L6.51193 5.63448L9.02603 0.305748C9.06724 0.203831 9.1325 0.127396 9.2218 0.076437C9.3111 0.0254784 9.40383 0 9.5 0C9.59617 0 9.6889 0.0254784 9.7782 0.076437C9.8675 0.127396 9.93275 0.203831 9.97397 0.305748L12.4881 5.63448L18.5466 6.7046C18.6565 6.71916 18.7458 6.76284 18.8145 6.83563C18.8832 6.90843 18.9382 6.99579 18.9794 7.0977C19.0069 7.18506 19.0069 7.27969 18.9794 7.38161C18.9519 7.48353 18.9038 7.57816 18.8351 7.66552L14.8167 11.9241L15.8265 18.3448C15.8402 18.4467 15.8265 18.5487 15.7852 18.6506C15.744 18.7525 15.6822 18.8326 15.5998 18.8908C15.5586 18.9345 15.5139 18.9636 15.4658 18.9782C15.4178 18.9927 15.3662 19 15.3113 19ZM9.5 14.523C9.54121 14.523 9.58243 14.5303 9.62364 14.5448C9.66486 14.5594 9.70607 14.5739 9.74729 14.5885L14.6106 17.4057L13.7245 11.8149C13.7108 11.7276 13.7176 11.6402 13.7451 11.5529C13.7726 11.4655 13.8138 11.3927 13.8688 11.3345L17.372 7.62184L12.0553 6.68276C11.9729 6.6682 11.8973 6.63544 11.8286 6.58448C11.7599 6.53352 11.705 6.47165 11.6638 6.39885L9.5 1.7908L7.33623 6.39885C7.29501 6.47165 7.24006 6.53352 7.17137 6.58448C7.10267 6.63544 7.02711 6.6682 6.94469 6.68276L1.62798 7.62184L5.13124 11.3345C5.18619 11.3927 5.2274 11.4655 5.25488 11.5529C5.28236 11.6402 5.28923 11.7276 5.27549 11.8149L4.38937 17.4057L9.25271 14.5885C9.29393 14.5739 9.33514 14.5594 9.37636 14.5448C9.41757 14.5303 9.45879 14.523 9.5 14.523Z" fill="white"/>
                                                    </svg>
                                                @endif
                                            @endfor
                                        </div>
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="swiper-pagination"></div>

                </div> <!--/quote-carousel-->
            </div> <!--/row-->
        </div> <!--/container-->
    </section>

    <!-- ======================== FAQs ======================== -->

    @if( count($faqs) )
        @php
            // Written out as a JSON string, this block carried a trailing comma
            // and the raw line breaks of every answer, so nothing could parse
            // it. Built as an array and encoded, it simply cannot break.
            $homeFaqSchema = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $faqs->map(fn ($faq) => [
                    '@type' => 'Question',
                    'name' => (string) $faq->question,
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => (string) $faq->answer,
                    ],
                ])->values()->all(),
            ];
        @endphp

        <script type="application/ld+json">{!! json_encode($homeFaqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    @endif

    <section class="faqs-section">
        <div class="container">

            <header>
                <div class="row">
                    <div class="col-12 text-center">
                        <h2 class="title h2">{{ trans('base.faqs') }}</h2>
                        <div class="subtitle font-two">
                            <p>{{trans('base.faqs_subtitle')}}</p>
                        </div>
                    </div>
                </div>
            </header>

            <div class="accordion-faqs">

                <div class="faq-col">
                    @foreach($faqs as $index => $faq)
                        @if($index % 2 == 0)
                            <div class="accordion-item-wrapper">
                                <button class="accordion">
                                    <span class="question">{{ $faq->question }}</span>
                                </button>
                                <div class="art-panel">
                                    <div class="panel-data">{{ $faq->answer }}</div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                <div class="faq-col">
                    @foreach($faqs as $index => $faq)
                        @if($index % 2 != 0)
                            <div class="accordion-item-wrapper">
                                <button class="accordion">
                                    <span class="question">{{ $faq->question }}</span>
                                </button>
                                <div class="art-panel">
                                    <div class="panel-data">{{ $faq->answer }}</div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

            </div>

        </div>
    </section>

    @if( count($brands) > 0 )
        <!-- ======================== Our Partners ======================== -->
        <section class="art-brands-list">
            <div class="container">

                <header>
                    <div class="row">
                        <div class="col-12 text-center">
                            <h2 class="title h2">{{trans('base.our_partners')}}</h2>
                        </div>
                    </div>
                </header>

                <div class="swiper art-brands-owl-items mt-6">
                    <div class="swiper-wrapper">
                        @foreach( $brands as $brand )
                            <div class="swiper-slide">
                                @include('pages.store.partials.brand_item', ['brand' => $brand->brand])
                            </div>
                        @endforeach
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </section>
    @endif

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

@push('dynamic_scripts')
{{--    @vite('resources/js/store/pages/store.home.js')--}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const observer = new MutationObserver(function () {
                const elfsightLink = document.querySelector(
                    'a[href*="elfsight.com/instagram-feed-instashow"]'
                );

                if (elfsightLink) {
                    elfsightLink.remove();
                    observer.disconnect();
                }
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        });
    </script>
@endpush
