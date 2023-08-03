@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - ' . $blogArticle->name }}</title>
@endsection

@section('content')
    <main class="main">
        <div class="content">
            <section class="article">
                <div class="container">
                    <div class="row">
                        <div class="col col-lg-8 mx-auto mt-8">
                            <div class="d-flex align-items-center mb-5 mb-md-8">
                                <div class="article-date mr-3">{{ $blogArticle->created_at->format('d.m.Y') }}</div>
                                <div class="article-author">{{ trans('base.team') }} {{ config('app.name') }}</div>
                            </div>
                            <div class="article-title mb-5 mb-md-8">{{ $blogArticle->name }}</div>
                            <p class="article-subtitle mb-5 mb-md-15">{{ $blogArticle->sub_title }}</p>
                        </div>
                        <div class="w-100"></div>
                        <div class="col">
                            <img class="article-img mb-5 mb-md-14" src="{{ $blogArticle->hero_image_url }}" alt="img">
                        </div>
                        @foreach($blogArticle->blocks as $block)
                            @if ($block->type_id === \App\DataClasses\BlogArticleBlockTypesDataClass::TYPE_TEXT)
                                <div class="w-100"></div>
                                <div class="col col-lg-8 mx-auto mb-10 mb-md-0">
                                    {!! isset($block->content[app()->getLocale()]) ? $block->content[app()->getLocale()] : '' !!}
                                </div>
                            @elseif($block->type_id === \App\DataClasses\BlogArticleBlockTypesDataClass::TYPE_IMAGE)
                                <div class="w-100"></div>
                                <div class="col col-lg-10 mx-auto">
                                    <div class="article-img-preview mb-5 mb-md-13">
                                        <div class="row flex-column flex-md-row">
                                            <div class="col item d-flex justify-content-center p-0">
                                                <div class="wrap">
                                                    <img src="{{ $block->content['images'][0]['image_url'] }}" alt="img">

                                                    @isset($block->content['images'][0]['selected_product'])
                                                        <div class="tooltip-preview" style="top: {{ $block->content['images'][0]['position']['top'] }}%; left: {{ $block->content['images'][0]['position']['left'] }}%;">
                                                            <div class="i-tooltip-plus btn-tooltip-preview">
                                                                <svg>
                                                                    <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-tooltip-plus"></use>
                                                                </svg>
                                                            </div>
                                                            <div class="sub-menu cards-products">
                                                                <div class='card card-product'>
                                                                    <div class='card-content'>
                                                                        <a href='{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $block->content['images'][0]['selected_product']['slug'] ]) }}' class='card-link'>
                                                                        <span class='card-link-image'>
                                                                            <img src='{{ $block->content['images'][0]['selected_product']['preview_image_url'] }}' alt='product'>
                                                                        </span>
                                                                            <span class='card-link-title'>{{ $block->content['images'][0]['selected_product']['name'] }}</span>
                                                                            <span class='card-link-price'>{{ $block->content['images'][0]['selected_product']['price'] }} {{ $baseCurrency->name_short }} @if($block->content['images'][0]['selected_product']->productType->product_point_name)<span class='card-link-price--small'>/ {{ $block->content['images'][0]['selected_product']->productType->product_point_name }}</span>@endif</span>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endisset
                                                </div>
                                            </div>
                                            @if(isset($block->content['images'][1]))
                                                <div class="col item mt-5 mt-md-10 p-0">
                                                    <div class="wrap ml-0 ml-md-3">
                                                        <img src="{{ $block->content['images'][1]['image_url'] }}" alt="img">
                                                        @isset($block->content['images'][1]['selected_product'])
                                                            <div class="tooltip-preview ml-4" style="top: {{ $block->content['images'][1]['position']['top'] }}%; left: {{ $block->content['images'][1]['position']['left'] }}%;">
                                                                <div class="i-tooltip-plus btn-tooltip-preview">
                                                                    <svg>
                                                                        <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-tooltip-plus"></use>
                                                                    </svg>
                                                                </div>
                                                                <div class="sub-menu cards-products">
                                                                    <div class='card card-product'>
                                                                        <div class='card-content'>
                                                                            <a href='{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $block->content['images'][1]['selected_product']['slug'] ]) }}' class='card-link'>
                                                                        <span class='card-link-image'>
                                                                            <img src='{{ $block->content['images'][1]['selected_product']['preview_image_url'] }}' alt='product'>
                                                                        </span>
                                                                                <span class='card-link-title'>{{ $block->content['images'][1]['selected_product']['name'] }}</span>
                                                                                <span class='card-link-price'>{{ $block->content['images'][1]['selected_product']['price'] }} {{ $baseCurrency->name_short }} @if($block->content['images'][1]['selected_product']->productType->product_point_name)<span class='card-link-price--small'>/ {{ $block->content['images'][1]['selected_product']->productType->product_point_name }}</span>@endif</span>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endisset
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @elseif($block->type_id === \App\DataClasses\BlogArticleBlockTypesDataClass::TYPE_QUOTE)
                                <div class="w-100"></div>
                                <div class="col col-lg-10 mx-auto">
                                    <div class="quote d-flex flex-column mb-10 mb-md-15">
                                        <svg>
                                            <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-quote"></use>
                                        </svg>
                                        <div class="content">{{ $block->content['quote'][app()->getLocale()] }}</div>
                                        @if (isset($block->content['quote_author']) || isset($block->content['quote_author_position']) || isset($block->content['quote_author_image_url']))
                                        <div class="quote-author d-flex align-items-center mt-4 mt-md-6">
                                            @isset($block->content['quote_author_image_url'])
                                            <img src="{{ $block->content['quote_author_image_url'] }}" alt="img">
                                            @endisset
                                            <div class="flex flex-column ml-2">
                                                @if (isset($block->content['quote_author']) || isset($block->content['quote_author_position']))
                                                    <div class="quote-author-name mb-1">{{ $block->content['quote_author'][app()->getLocale()] }}</div>
                                                    <div class="quote-author-position">{{ $block->content['quote_author_position'][app()->getLocale()] }}</div>
                                                @endif
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            @elseif($block->type_id === \App\DataClasses\BlogArticleBlockTypesDataClass::TYPE_SPONSOR)
                                <div class="w-100"></div>
                                <div class="col col-lg-8 mx-auto">
                                    <div class="article-brand-excerpt d-flex flex-column flex-md-row align-items-center mb-10 mb-md-16">
                                        <img src="{{ $block->content['sponsor_image_url'] }}" alt="York">
                                        <div class="content ml-md-5 mt-5 mt-md-0">
                                            {{ $block->content['sponsor_text'][app()->getLocale()] }}
                                            <div class="btn-article-brand">
                                                <a href="{{ $block->content['sponsor_link'] }}" class="btn-ahead">
                                                    <span class="mr-2">{{ trans('base.see_details') }}</span>
                                                    <svg>
                                                        <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-arrow"></use>
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @elseif($block->type_id === \App\DataClasses\BlogArticleBlockTypesDataClass::TYPE_VIDEO)
                                <div class="w-100"></div>
                                <div class="col col-lg-8 mx-auto mb-5 mb-md-10">
                                    <div class="plyr__video-embed js-player">
                                        <iframe src="https://www.youtube.com/embed/dmy-VxmBVW4" allowfullscreen allowtransparency allow="autoplay"></iframe>
                                    </div>
                                </div>
                            @elseif($block->type_id === \App\DataClasses\BlogArticleBlockTypesDataClass::TYPE_SLIDER)
                                    </div>
                                </div>

                                <div class="article-slider">
                                    <div class="container">
                                        <div class="row">
                                            <div class="col">
                                                <div class="inner">
                                                    <div class="swiper-article">
                                                        <div class="swiper-wrapper row flex-nowrap no-gutters">
                                                            @php
                                                                $totalCountOfItems = 0;
                                                                $firstCycleEnd = false;
                                                            @endphp
                                                            @while($totalCountOfItems < 10)
                                                                @foreach($block->content['images'] as $sliderImage)
                                                                    @php
                                                                        if ($firstCycleEnd && $totalCountOfItems >= 10) {
                                                                            break;
                                                                        }

                                                                        $totalCountOfItems++
                                                                    @endphp
                                                                    <div class="swiper-slide col-auto">
                                                                        <div class="wrap d-flex justify-content-center">
                                                                            <div class="item px-3">
                                                                                <img src="{{ $sliderImage['image_url'] }}" alt="img">
                                                                                @if(isset($sliderImage['selected_product']))
                                                                                    <div class="tooltip-preview">
                                                                                        <div class="i-tooltip-plus btn-tooltip-preview">
                                                                                            <svg>
                                                                                                <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-tooltip-plus"></use>
                                                                                            </svg>
                                                                                        </div>
                                                                                        <div class="sub-menu cards-products">
                                                                                            <div class="card card-product">
                                                                                                <div class='card-content'>
                                                                                                    <a href='{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $sliderImage['selected_product']['slug'] ]) }}' class='card-link'>
                                                                                                                <span class='card-link-image'>
                                                                                                                    <img src='{{ $sliderImage['selected_product']['preview_image_url'] }}' alt='product'>
                                                                                                                </span>
                                                                                                        <span class='card-link-title'>{{ $sliderImage['selected_product']['name'] }}</span>
                                                                                                        <span class='card-link-price'>{{ $sliderImage['selected_product']['price'] }} {{ $baseCurrency->name_short }} @if($sliderImage['selected_product']->productType->product_point_name)<span class='card-link-price--small'>/ {{ $sliderImage['selected_product']->productType->product_point_name }}</span>@endif</span>
                                                                                                    </a>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                                @php
                                                                    $firstCycleEnd = true;
                                                                @endphp
                                                            @endwhile
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="swiper-control d-flex align-items-center justify-content-center mt-5">
                                                    <div class="button-slider-prev d-block">
                                                        <svg>
                                                            <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-arrow-circle"></use>
                                                        </svg>
                                                    </div>
                                                    <div class="swiper-pagination"></div>
                                                    <div class="button-slider-next d-block">
                                                        <svg>
                                                            <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-arrow-circle"></use>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="container">
                                    <div class="row">
                            @elseif($block->type_id === \App\DataClasses\BlogArticleBlockTypesDataClass::TYPE_QUESTIONS_AND_ANSWERS)
                                @foreach($block->content['questions'] as $question)
                                    <div class="w-100"></div>
                                    <div class="col col-lg-8 mx-auto mb-5 mb-md-11">
                                        <div class="question">
                                            <div class="title">
                                                <div class="d-block">
                                                    {!! $question['question'][app()->getLocale()] !!}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="answer">
                                            <div class="title">
                                                <div class="d-block">
                                                {!! $question['answer'][app()->getLocale()] !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        @endforeach
                    </div>
                </div>
            </section>
            <section class="article-preview mb-10 mb-md-17">
                <div class="container">
                    <div class="row">
                        <div class="col">
                            <div class="head text-center mb-4 mb-lg-8 mb-md-12">{{ trans('base.article_read_also') }}:</div>
                            <div class="swiper-article-preview cards-articles">
                                <div class="swiper-wrapper">
                                    @foreach($latestArticles as $latestArticle)
                                        <div class="swiper-slide">
                                            <div class="card card-article">
                                                <div class="card-content">
                                                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('blog.article.page', ['blogArticleSlug' => $latestArticle->slug]) }}" class="card-link">
														<span class="card-link-image">
															<img src="{{ $latestArticle->hero_image_url }}" alt="product">
														</span>
                                                        <span class="card-link-text">{{ $latestArticle->name }}</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mt-8 mt-lg-10 text-center">
                            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('blog.main.page') }}" class="btn btn-outline-black-custom">{{ trans('base.see_all_articles') }}</a>
                        </div>
                    </div>
                </div>
            </section>
            @guest
                @if(!\Illuminate\Support\Facades\Session::exists('email_subscription_sent'))
                    <x-email-subscription-form/>
               @endif
            @endguest
        </div>
    </main>
@endsection
