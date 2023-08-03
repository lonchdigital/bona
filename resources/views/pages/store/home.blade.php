@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - HOME' }}</title>
@endsection

@section('content')
    <main class="main">
        <div class="content">
            @if(isset($config) && count($slides))
            <section class="banner-top pt-lg-2 mb-6 mb-md-10 mb-lg-12">
                <div class="container">
                    <div class="banner-top-inner">
                        <div class="row">
                            <div class="col-3 d-none d-md-block">
                                <div class="banner-top-left-side text-center px-4">
                                    <div class="circle-img mb-4 mb-xl-8 mx-auto">
                                        <img src="{{ $config->slider_logo_image_url }}" alt="{{ $config->slider_title }}">
                                    </div>
                                    <div class="title text-white mb-4 mb-xl-10">{{ $config->slider_title }}</div>
                                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.collection.page', ['collectionSlug' => $config->collection->slug]) }}" class="btn btn-white">{{ trans('base.see_details') }}</a>
                                </div>
                            </div>
                            <div class="col-12 col-md-9 banner-top-wallpaper">
                                <div class="banner-top-wallpaper-inner">
                                    <div class="swiper-wallpaper-collection">
                                        <div class="swiper-wrapper">
                                            @foreach($slides as $slide)
                                                <div class="swiper-slide">
                                                    <img src="{{ $slide->slide_image_url }}" alt="The Journey">
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 d-flex align-items-xxl-center justify-content-between mt-2 flex-column flex-xxl-row">
                            <div class="d-flex align-items-xxl-center flex-column flex-xxl-row ">
                                <div class="swiper-wallpaper-collection-head mb-2 mb-xxl-0 mr-xxl-2">{{ trans('base.on_image') }}</div>
                                <div class="swiper-wallpaper-collection-thumbs">
                                    <div class="swiper-wrapper">
                                        @foreach($slides as $slide)
                                            <div class="swiper-slide">{{ $slide->description }}</div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-buttons ml-auto">
                                <div class="button-slider-prev">
                                    <svg>
                                        <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-arrow-circle"></use>
                                    </svg>
                                </div>
                                <div class="button-slider-next">
                                    <svg>
                                        <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-arrow-circle"></use>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @endif
            @if(count($brands))
            <section class="partners mb-10 mb-md-8 mb-lg-9">
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <div class="row">
                                        @foreach($brands as $brand)
                                        <div class="col-2">
                                            <img src="{{ $brand->brand->logo_image_url }}" alt="Atre">
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
            @endif
            @if(count($newProducts))
            <section class="product-collection-slider">
                <div class="container">
                    <div class="row pb-8 pb-md-11 pb-lg-17 overflow-hidden">
                        <div class="col-12">
                            <div class="head text-center mb-4 mb-md-7 mb-lg-8 pt-3">{{ trans('base.new_products') }}</div>
                            <div class="cards-products">
                                <div class="swiper-cards-products">
                                    <div class="swiper-wrapper">
                                        @foreach($newProducts as $newProduct)
                                            <div class="swiper-slide">
                                                <div class="card card-product">
                                                    <div class="card-content">
                                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $newProduct->slug]) }}"
                                                           class="card-link">
                                                                    <span class="card-link-image">
                                                                        <img src="{{ $newProduct->preview_image_url }}"
                                                                             alt="product">

                                                                        @if ($newProduct->special_offers)
                                                                            <span class="card-link-container">
                                                                                @foreach($newProduct->special_offers as $specialOffer)
                                                                                    <span class="card-link-offer">{{ \App\DataClasses\ProductSpecialOfferOptionsDataClass::get($specialOffer)['name'] ?? '' }}</span>
                                                                                @endforeach
                                                                            </span>
                                                                        @endif

                                                                        @auth
                                                                            <span class="link-heart @if($wishListProducts->contains($newProduct->id)) link-heart-active @endif" id="{{ $newProduct->slug }}">
                                                                            <span>{{ trans('base.add_to_wish_list') }}</span>
                                                                            <svg>
                                                                                <use
                                                                                    xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-heart-hover"></use>
                                                                            </svg>
                                                                        </span>
                                                                        @endauth
                                                                    </span>
                                                            <span class="card-link-title">
								                                        {{ $newProduct->name }}
							                                        </span>
                                                            <span class="card-link-price">
								                                @if($newProduct->old_price)
                                                                    <span class="card-link-price--hot">{{ $newProduct->price }} {{ $baseCurrency->name_short }} </span> <span class="card-link-price--old">{{ $newProduct->old_price }} {{ $baseCurrency->name_short }}</span>
                                                                @else
                                                                    {{ $newProduct->price }} {{ $baseCurrency->name_short }}
                                                                @endif
                                                                @if($newProduct->productType->product_point_name)
                                                                    <span class="card-link-price--small">/ {{ $newProduct->productType->product_point_name }}</span>
                                                                @endif
							                                        </span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="swiper-control mt-4">
                                    <div class="swiper-pagination d-none d-md-flex"></div>
                                    <div class="swiper-buttons ml-auto">
                                        <div class="button-slider-prev">
                                            <svg>
                                                <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-arrow-circle"></use>
                                            </svg>
                                        </div>
                                        <div class="button-slider-next">
                                            <svg>
                                                <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-arrow-circle"></use>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @endif
{{--            <section class="collection-preview mb-5 mb-md-3 mb-lg-6">--}}
{{--                <div class="container">--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-12 col-xl-6">--}}
{{--                            <div class="swiper-collection-preview overflow-hidden">--}}
{{--                                <div class="swiper-wrapper">--}}
{{--                                    <div class="swiper-slide">--}}
{{--                                        <div class="bg-wrap">--}}
{{--                                            <img class="bg-down" src="img/Bitmap-2.jpeg" alt="Bitmap">--}}
{{--                                        </div>--}}
{{--                                        <div class="content">--}}
{{--                                            <div class="title">Muance</div>--}}
{{--                                            <div class="subtitle">Изысканные обои для гостиной или спальной</div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <div class="swiper-slide">--}}
{{--                                        <div class="bg-wrap">--}}
{{--                                            <img class="bg-down" src="img/Bitmap-3.jpeg" alt="Bitmap">--}}
{{--                                        </div>--}}
{{--                                        <div class="content">--}}
{{--                                            <div class="title">Muance</div>--}}
{{--                                            <div class="subtitle">Изысканные обои для гостиной или спальной ля гостиной или спальной</div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <div class="swiper-slide">--}}
{{--                                        <div class="bg-wrap">--}}
{{--                                            <img class="bg-down" src="img/Bitmap-4.jpeg" alt="Bitmap">--}}
{{--                                        </div>--}}
{{--                                        <div class="content">--}}
{{--                                            <div class="title">Muance</div>--}}
{{--                                            <div class="subtitle">Изысканные обои для гостиной или спальной ои для гостиной или спальнойои для гостиной или спальной</div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="swiper-buttons ml-auto">--}}
{{--                                    <div class="button-slider-prev">--}}
{{--                                        <svg>--}}
{{--                                            <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-arrow-circle"></use>--}}
{{--                                        </svg>--}}
{{--                                    </div>--}}
{{--                                    <div class="button-slider-next">--}}
{{--                                        <svg>--}}
{{--                                            <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-arrow-circle"></use>--}}
{{--                                        </svg>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        <div class="col-12 col-xl-6 mt-6 mt-md-3 mt-lg-6 mt-xl-0">--}}
{{--                            <div class="row h-100">--}}
{{--                                <div class="col-12 col-md-6">--}}
{{--                                    <div class="collection-preview-video">--}}
{{--                                        <video class="js-player" playsinline controls data-poster="img/Bitmap-3.jpeg">--}}
{{--                                            <source src="assets/video/example.mp4" type="video/mp4" />--}}
{{--                                        </video>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <div class="col-12 col-md-6 mt-5 mt-md-0">--}}
{{--                                    <div class="collection-preview-left-side">--}}
{{--                                        <img src="img/Bitmap-4.jpeg" alt="Bitmap">--}}
{{--                                        <div class="wrap-circle">--}}
{{--                                            <img src="img/brand/york.png" alt="York">--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </section>--}}
            @guest
                @if(!\Illuminate\Support\Facades\Session::exists('email_subscription_sent'))
                    <x-email-subscription-form/>
                @endif
            @endguest
            @if(count($productsByField))
                <section class="wallpaper-preview mb-17 mb-md-16 mb-lg-23">
                    <div class="container">
                        <div class="head text-center mb-4 mb-md-7 mb-lg-8">{{ trans('base.wallpapers') }} {{ $fieldFilterString }}</div>
                        <div class="cards-types">
                            <div class="row">
                                <div class="col">
                                    <div class="row d-flex justify-content-center">
                                        @foreach($productsByField as $productByField)
                                            @if(isset($productByField['product']) && $productByField['product'])
                                                <div class="col-12 col-md-2">
                                                    <div class="card card-type">
                                                        <div class="card-content">
                                                            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.filter.page', ['productTypeSlug' => config('domain.wallpaper_product_type_slug')]) }}/{{ $config->productField->slug }}={{ $productByField['option']->slug }}" class="card-link">
                                                                <span class="card-link-image">
                                                                    <img src="{{ $productByField['product']->preview_image_url }}" alt="product">
                                                                </span>
                                                                            <span class="card-link-title">
                                                                    {{ $productByField['option']->name }}
                                                                </span>
                                                                            <span class="card-link-content">
                                                                    {{ trans('base.options') }}: <span class="card-link-count">{{ $productByField['count'] }}</span>
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mt-6 mt-md-8 mt-lg-10 text-center">
                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => config('domain.wallpaper_product_type_slug')]) }}" class="btn btn-outline-black-custom">{{ trans('base.all_wallpapers') }}</a>
                            </div>
                        </div>
                    </div>
                </section>
            @endif
            <section class="article-preview mb-17">
                <div class="container">
                    <div class="head text-center mb-3">{{ trans('base.know_about_wallpapers') }}</div>
                    <div class="subhead text-center mb-12 mb-md-10">{{ trans('base.be_in_trands') }}</div>
                    <div class="cards-articles">
                        <div class="row flex-column flex-md-row">
                            @foreach($articles as $article)
                                <div class="col mb-8 mb-md-0">
                                    <div class="card card-article">
                                        <div class="card-content">
                                            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('blog.article.page', ['blogArticleSlug' => $article->slug]) }}" class="card-link">
												<span class="card-link-image">
													<img src="{{ $article->hero_image_url }}" alt="product">
												</span>
                                                <span class="card-link-text">{{ $article->name }}</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="row">
                            <div class="col mt-md-8 mt-lg-10 text-center">
                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('blog.main.page') }}" class="btn btn-outline-black-custom">{{ trans('base.see_all_articles') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <section class="wallpaper-definition mb-17">
                <div class="container">
                    <div class="head mb-3">{{ trans('base.wallpapers') }}</div>
                    <div class="row flex-column flex-lg-row">
                        <div class="col mb-8 mb-lg-0"><strong>{{ trans('base.wallpapers') }}</strong> {{ trans('base.home_page_wallpapers_explanation_1') }}</div>
                        <div class="col">{{ trans('base.home_page_wallpapers_explanation_2') }}</div>
                    </div>
                </div>
            </section>
        </div>
    </main>
@stop
