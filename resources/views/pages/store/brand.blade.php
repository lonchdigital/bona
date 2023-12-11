@extends('layouts.store-main')

@section('title')
    @if (isset($seogenData))
        <title>{{ $seogenData->html_title_tag }}</title>
        <meta name="title" content="{{ $seogenData->meta_title_tag }}">
        <meta name="description" content="{{ $seogenData->meta_description_tag }}">
        <meta name="keywords" content="{{ $seogenData->meta_keywords_tag }}">
    @else
        <title>{{ config('app.name') . ' - ' . trans('base.catalog') }}</title>
    @endif
@endsection

@section('content')
    <main class="main single-brand">
        <div class="content">
            <div class="entry-content">
                <div id="b-breadcrumbs" class="b-breadcrumbs">
                    <div class="container">
                        <div class="row">
                            <div class="col mt-4 mt-md-0 mb-4">
                                <nav aria-label="breadcrumb">
                                    <ul class="breadcrumb mb-0" id="breadcrumblist" itemscope itemtype="https://schema.org/BreadcrumbList">
                                        <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                                            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}">Malina Design Studio</a>
                                            <meta itemprop="position" content="1"/>
                                        </li>
                                        <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                                            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.brands.list.page') }}">{{ trans('base.brands') }}</a>
                                            <meta itemprop="position" content="2"/>
                                        </li>
                                        <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                                            {{ $brand->name }}
                                            <meta itemprop="position" content="3"/>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
                <section class="single-brand-banner-top mb-10 mb-lg-14">
                    <div class="container">
                        <div class="row">
                            <div class="col">
                                <div class="single-brand-banner-top-content position-relative">
                                    <div class="main-pic">
                                        <img src="{{ $brand->head_image_url }}" alt="img">
                                    </div>
                                    <div class="single-brand-logo px-4 py-6">
                                        <img src="{{ $brand->logo_image_url }}" alt="img">
                                    </div>
                                </div>
                            </div>
                            <div class="w-100"></div>
                            <div class="col col-md-10 col-xl-6 mx-auto">
                                <div class="spoiler">
                                    <p class="hidden-text text-center mb-6">{{ $brand->description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <section class="product-collection-slider">
                    <div class="container">
                        <div class="row pb-10 pb-md-11 pb-lg-12 overflow-hidden">
                            <div class="col-12">
                                <div class="head text-center mb-4 mb-md-7 mb-lg-8 pt-3">{{ trans('base.bestsellers') }}</div>
                                <div class="cards-products">
                                    <div class="swiper-cards-products">
                                        <div class="swiper-wrapper">
                                            @foreach($bestsellers as $product)
                                                <div class="swiper-slide">
                                                    <div class="card card-product">
                                                        <div class="card-content">
                                                            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $product->slug]) }}"
                                                               class="card-link">
                                                                    <span class="card-link-image">
                                                                        <img src="{{ $product->preview_image_url }}"
                                                                             alt="product">
                                                                        @if ($product->special_offers)
                                                                            <div class="card-link-container">
                                                                                @foreach($product->special_offers as $specialOffer)
                                                                                    <span class="card-link-offer">{{ \App\DataClasses\ProductSpecialOfferOptionsDataClass::get($specialOffer)['name'] ?? '' }}</span>
                                                                                @endforeach
                                                                            </div>
                                                                        @endif
                                                                        @auth
                                                                            <span class="link-heart @if($wishListProducts->contains($product->id)) link-heart-active @endif" id="{{ $product->slug }}">
                                                                            <span>{{ trans('base.add_to_wish_list') }}</span>
                                                                            <svg>
                                                                                <use
                                                                                    xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-heart-hover"></use>
                                                                            </svg>
                                                                        </span>
                                                                        @endauth
                                                                    </span>
                                                                <span class="card-link-title">
								                                        {{ $product->id }} {{ $product->name }}
							                                        </span>
                                                                <span class="card-link-price">
                                                                    @if($product->old_price)
                                                                        <span class="card-link-price--hot">{{ $product->price }} {{ $product->name_short }} </span> <span class="card-link-price--old">{{ $product->old_price }} {{ $baseCurrency->name_short }}</span>
                                                                    @else
                                                                        {{ $product->price }} {{ $baseCurrency->name_short }}
                                                                    @endif
                                                                    @if($product->productType->product_point_name)
                                                                        <span class="card-link-price--small">/ {{ $productType->product_point_name }}</span>
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
                                        <div class="swiper-pagination"></div>
                                        <div class="swiper-buttons ml-auto d-none d-md-flex">
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
                <section class="single-brand-slider mb-10 mb-md-14 mb-lg-22">
                    <div class="container container-max">
                        <div class="row">
                            <div class="col d-flex flex-column-reverse d-lg-block">
                                <div class="swiper-single-brand mb-26 my-lg-13">
                                    <div class="swiper-wrapper">
                                        @php
                                            $displayedSlides = 0;
                                        @endphp
                                        @if (count($brand->slides) < 10 && count($brand->slides) > 0)
                                            @while($displayedSlides < 10)
                                                @foreach($brand->slides as $slide)
                                                    @php
                                                        $displayedSlides++;
                                                    @endphp
                                                    <div class="swiper-slide">
                                                        <img src="{{ $slide->image_url }}" alt="img">
                                                    </div>
                                                @endforeach
                                            @endwhile
                                        @else
                                            @foreach($brand->slides as $slide)
                                                <div class="swiper-slide">
                                                    <img src="{{ $slide->image_url }}" alt="img">
                                                </div>
                                            @endforeach
                                        @endif

                                    </div>
                                </div>
                                <div class="single-brand-slider-nav text-white text-center mt-6 mt-lg-0 px-4 px-xxl-8">
                                    <div class="single-brand-slider-title mb-2 mb-lg-7">{{ $brand->slider_main_text }}</div>
                                    <p class="mb-10 mb-lg-9">{{ $brand->slider_description_text }}</p>
                                    <div class="swiper-control">
                                        <div class="button-slider-prev">
                                            <svg>
                                                <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-arrow-circle"></use>
                                            </svg>
                                        </div>
                                        <div class="swiper-pagination"></div>
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
                </section>
                <section class="collection-other collection-all">
                    <div class="container">
                        <div class="row">
                            <div class="col">
                                <div class="head text-md-center mb-5 mb-md-11">{{ trans('base.brand_all_collection') }}:</div>
                            </div>
                        </div>
                        <div class="row collection-other-list justify-content-center">

                        </div>
                    </div>
                </section>
                <section class="brand-other">
                    <div class="container">
                        <div class="row">
                            <div class="col">
                                <div class="head text-md-center mb-4">{{ trans('base.discover_other_brands') }}</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mx-auto">
                                <div class="brand-other-list d-flex flex-wrap justify-content-center mb-6 mb-xl-0">
                                    @foreach($discoverBrands as $brand)
                                        <div class="brand-other-item">
                                            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.brand.page', ['brandSlug' => $brand->slug]) }}">
                                                <img src="{{ $brand->logo_image_url }}" alt="img">
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
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
        </div>
    </main>
@endsection
