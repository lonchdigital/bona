@extends('layouts.store-main')

@section('title')
    @if (isset($seogenData))
        <title>{{ $seogenData->html_title_tag }}</title>
    @else
        <title>{{ config('app.name') . ' - ' . trans('base.catalog') }}</title>
    @endif

    @if($product->meta_title)
        <meta name="title" content="{{ $product->meta_title }}">
    @elseif(isset($seogenData))
        <meta name="title" content="{{ $seogenData->meta_title_tag }}">
    @endif

    @if($product->meta_description)
        <meta name="title" content="{{ $product->meta_description }}">
    @elseif(isset($seogenData))
        <meta name="title" content="{{ $seogenData->meta_description_tag }}">
    @endif

    @if($product->meta_keywords)
        <meta name="title" content="{{ $product->meta_keywords }}">
    @elseif(isset($seogenData))
        <meta name="title" content="{{ $seogenData->meta_keywords_tag }}">
    @endif
@endsection

@section('content')
<main id="single-product" class="single-product">
    <div class="content">
        <div class="entry-content">
            <div id="b-breadcrumbs" class="b-breadcrumbs">
                <div class="container">
                    <div class="row">
                        <div class="col mt-4 mt-md-0 mb-4">
                            <nav aria-label="breadcrumb">
                                <ul class="breadcrumb mb-0" id="breadcrumblist" itemscope itemtype="http://schema.org/BreadcrumbList">
                                    <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}">Malina Design Studio</a>
                                        <meta itemprop="position" content="1"/>
                                    </li>
                                    <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => $product->productType->slug]) }}">{{ trans('base.catalog') }}</a>
                                        <meta itemprop="position" content="2"/>
                                    </li>
                                    <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => $product->productType->slug]) }}">{{ $product->productType->name }}</a>
                                        <meta itemprop="position" content="3"/>
                                    </li>
                                    @if($product->productType->has_brand)
                                        <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.brand.page', ['brandSlug' => $product->brand->slug]) }}">{{ $product->brand->name }}</a>
                                            <meta itemprop="position" content="4"/>
                                        </li>
                                    @endif
                                    <li class="breadcrumb-item active" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem" aria-current="page">
                                        {{ $product->name }}
                                        @if($product->productType->has_brand)
                                            <meta itemprop="position" content="5"/>
                                        @else
                                            <meta itemprop="position" content="4"/>
                                       @endif
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <div id="b-single-product-info" class="b-single-product-info position-relative overflow-hidden mb-11 mb-md-14 mb-lg-16">
                <img src="{{ $product->pattern_image_url }}" alt="bg" class="bg-down d-none d-sm-block">
                <div class="row">
                    <div class="col">
                        <div class="container">
                            <div class="product-info-wrapper bg-white position-relative">
                                <div class="row flex-column flex-lg-row">
                                    <div class="col col-lg-7">
                                        <div class="row">
                                            <div class="col d-flex align-items-center justify-content-center justify-content-lg-start">
                                                <div class="swiper-single-wallpaper-thumbs-wrap d-none d-sm-flex align-items-center mb-md-13">
                                                    <div class="swiper-pagination mr-4 mr-xl-10"></div>
                                                    <div class="inner d-flex flex-column align-items-center mr-7">
                                                        <a href="#" class="button-slider-prev button-slider-up mb-5">
                                                            <svg>
                                                                <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-arrow-circle"></use>
                                                            </svg>
                                                        </a>
                                                        <div class="swiper-single-wallpaper-thumbs">
                                                            <div class="swiper-wrapper">
                                                                <div class="swiper-slide">
                                                                    <img src="{{ $product->main_image_url }}" alt="img">
                                                                </div>
                                                                <div class="swiper-slide">
                                                                    <img src="{{ $product->pattern_image_url }}" alt="img">
                                                                </div>
                                                                @if($product->gallery_image1_url)
                                                                    <div class="swiper-slide">
                                                                        <img src="{{ $product->gallery_image1_url }}" alt="img">
                                                                    </div>
                                                                @endif
                                                                @if($product->gallery_image2_url)
                                                                    <div class="swiper-slide">
                                                                        <img src="{{ $product->gallery_image2_url }}" alt="img">
                                                                    </div>
                                                                @endif
                                                                @if($product->gallery_image3_url)
                                                                    <div class="swiper-slide">
                                                                        <img src="{{ $product->gallery_image3_url }}" alt="img">
                                                                    </div>
                                                                @endif
                                                                @if($product->gallery_image14_url)
                                                                    <div class="swiper-slide">
                                                                        <img src="{{ $product->gallery_image4_url }}" alt="img">
                                                                    </div>
                                                                @endif
                                                                @if($product->gallery_image5_url)
                                                                    <div class="swiper-slide">
                                                                        <img src="{{ $product->gallery_image5_url }}" alt="img">
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <a href="#" class="button-slider-next button-slider-down mt-5">
                                                            <svg>
                                                                <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-arrow-circle"></use>
                                                            </svg>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="swiper-single-wallpaper-wrap d-flex flex-column">
                                                    <div class="swiper-single-wallpaper mb-md-5">
                                                        <div class="swiper-wrapper">
                                                            <div class="swiper-slide">
                                                                <a data-fancybox="single-wallpaper-gallery" href="{{ $product->main_image_url }}">
                                                                    <img src="{{ $product->main_image_url }}" alt="img">
                                                                </a>
                                                            </div>
                                                            <div class="swiper-slide">
                                                                <a data-fancybox="single-wallpaper-gallery" href="{{ $product->pattern_image_url }}">
                                                                    <img src="{{ $product->pattern_image_url }}" alt="img">
                                                                </a>
                                                            </div>
                                                            @if($product->gallery_image1_url)
                                                                <div class="swiper-slide">
                                                                    <a data-fancybox="single-wallpaper-gallery" href="{{ $product->gallery_image1_url }}">
                                                                        <img src="{{ $product->gallery_image1_url }}" alt="img">
                                                                    </a>
                                                                </div>
                                                            @endif
                                                            @if($product->gallery_image2_url)
                                                                <div class="swiper-slide">
                                                                    <a data-fancybox="single-wallpaper-gallery" href="{{ $product->gallery_image2_url }}">
                                                                        <img src="{{ $product->gallery_image2_url }}" alt="img">
                                                                    </a>
                                                                </div>
                                                            @endif
                                                            @if($product->gallery_image3_url)
                                                                <div class="swiper-slide">
                                                                    <a data-fancybox="single-wallpaper-gallery" href="{{ $product->gallery_image3_url }}">
                                                                        <img src="{{ $product->gallery_image3_url }}" alt="img">
                                                                    </a>
                                                                </div>
                                                            @endif
                                                            @if($product->gallery_image4_url)
                                                                <div class="swiper-slide">
                                                                    <a data-fancybox="single-wallpaper-gallery" href="{{ $product->gallery_image4_url }}">
                                                                        <img src="{{ $product->gallery_image4_url }}" alt="img">
                                                                    </a>
                                                                </div>
                                                            @endif
                                                            @if($product->gallery_image5_url)
                                                                <div class="swiper-slide">
                                                                    <a data-fancybox="single-wallpaper-gallery" href="{{ $product->gallery_image5_url }}">
                                                                        <img src="{{ $product->gallery_image5_url }}" alt="img">
                                                                    </a>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <a href="#single-wallpaper-gallery-1" id="fancybox-trigger">
                                                            <svg class="i-open-fancybox">
                                                                <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-open-fancybox"></use>
                                                            </svg>
                                                        </a>
                                                    </div>
                                                    <div class="swiper-pagination mt-5 d-sm-none"></div>
                                                    <ul class="cta-socials list-inline mx-auto mb-0 d-none d-md-block">
                                                        <li class="list-inline-item">
                                                            <a href="viber://forward?text={{ url()->full() }}" class="link-soc" rel="noffolow" target="_blank">
                                                                <svg class="i-viber">
                                                                    <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-viber"></use>
                                                                </svg>
                                                            </a>
                                                        </li>
                                                        <li class="list-inline-item">
                                                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->full()) }}" class="link-soc" rel="noffolow" target="_blank">
                                                                <svg class="i-facebook">
                                                                    <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-facebook"></use>
                                                                </svg>
                                                            </a>
                                                        </li>
                                                        <li class="list-inline-item">
                                                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->full()) }}" class="link-soc" rel="noffolow" target="_blank">
                                                                <svg class="i-messenger">
                                                                    <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-messenger"></use>
                                                                </svg>
                                                            </a>
                                                        </li>
                                                        <li class="list-inline-item">
                                                            <a href="https://www.instagram.com/?url={{ url()->full() }}" class="link-soc" rel="noffolow" target="_blank">
                                                                <svg class="i-instagram">
                                                                    <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-instagram"></use>
                                                                </svg>
                                                            </a>
                                                        </li>
                                                        <li class="list-inline-item">
                                                            <a href="https://t.me/share/url?url={{ url()->full() }}" class="link-soc" rel="noffolow" target="_blank">
                                                                <svg class="i-telegram">
                                                                    <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-telegram"></use>
                                                                </svg>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col col-lg-5 col-xxxl-4 mt-5 mt-lg-0">
                                        <div class="product-info-content">
                                            <div class="info-content-code mb-3">
                                                {{ trans('base.sku') }} {{ $product->sku }}
                                            </div>
                                            <h1 class="info-content-name h1 mb-6 mb-sm-2">
                                                @if (isset($seogenData))
                                                    {{ $seogenData->html_h1_tag }}
                                                @else
                                                    {{ $product->name }}
                                                @endif
                                            </h1>
                                            <div class="info-content-price mb-2">
                                                <span class="info-content-hot-price">@if($product->old_price) {{ $product->price }} {{ $baseCurrency->name_short }} </span> / <span class="info-content-old-price">{{ $product->old_price }} {{ $baseCurrency->name_short }}</span>
                                                @else
                                                    {{ $product->price }} {{ $baseCurrency->name_short }}
                                                @endif
                                            </div>
                                            <div class="info-content-stock mb-4">
                                                {{ \App\DataClasses\ProductStatusDataClass::get($product->availability_status_id)['name'] }}
                                            </div>
                                            <div class="info-content-table mb-6">
                                                <div class="table-color table-item d-flex flex-column flex-sm-row flex-lg-column flex-xl-row justify-content-between">
                                                    <div class="table-name mr-6">
                                                        {{ trans('base.color') }}
                                                    </div>
                                                    <div class="table-value text-right d-flex align-items-center justify-content-sm-end justify-content-lg-start justify-content-xl-end flex-wrap">
                                                        @if(count($product->children))
                                                            <div class="link-color active" style="background-color: {{$product->color->hex}};" data-toggle="tooltip" title="<span class='help'>{{ $product->color->name }}</span>"><span class="before border-silver-custom"></span></div>
                                                            @foreach($product->children as $subProduct)
                                                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $subProduct->slug]) }}" class="link-color" style="background-color: {{ $subProduct->color->hex }};" data-toggle="tooltip" title="<span class='help'>{{ $subProduct->color->name }}</span>"><span class="before border-silver-custom"></span></a>
                                                            @endforeach
                                                        @elseif($product->parent_product_id)
                                                            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $product->parent->slug]) }}" class="link-color" style="background-color: {{ $product->parent->color->hex }};" data-toggle="tooltip" title="<span class='help'>{{ $product->parent->color->name }}</span>"><span class="before border-silver-custom"></span></a>
                                                            @foreach($product->parent->children as $subProduct)
                                                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $subProduct->slug]) }}" class="link-color @if($product->id === $subProduct->id) active @endif" style="background-color: {{ $subProduct->color->hex }};" data-toggle="tooltip" title="<span class='help'>{{ $subProduct->color->name }}</span>"><span class="before border-silver-custom"></span></a>
                                                            @endforeach
                                                        @endif

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="info-content-add d-flex align-items-center justify-content-between flex-wrap mb-10 mb-md-6">
                                                <div class="d-flex flex-wrap align-items-center no-gutters w-100">
                                                    <div class="col-6 col-sm-auto col-lg-6 col-xl-auto order-xl-1 @if($isProductInCart) d-none @endif" id="count-of-products-body">
                                                        <div class="custom-control-number mr-2">
                                                            <span class="counter minus"></span>
                                                            <input type="number" class="form-control" id="count-of-products" min="1" value="1">
                                                            <span class="counter plus"></span>
                                                        </div>
                                                    </div>
                                                    <div class="col col-sm-auto col-lg col-xl-auto order-last order-sm-0 order-lg-last order-xl-2 mt-4 mt-sm-0 mt-lg-4 mt-xl-0 @if($isProductInCart) d-none @endif">
                                                        <button type="button" class="btn btn-black-custom w-100 single-product-add-to-cart" id="{{ $product->slug }}">{{ trans('base.add_to_cart') }}</button>
                                                    </div>

                                                    <div class="go-to-cart-body col-6 col-sm-auto col-lg-6 col-xl-auto order-xl-1 @if(!$isProductInCart) d-none @endif">
                                                        <span class="mr-2">{{ trans('base.in_cart') }}</span>
                                                    </div>
                                                    <div class="go-to-cart-body col col-sm-auto col-lg col-xl-auto order-last order-sm-0 order-lg-last order-xl-2 mt-4 mt-sm-0 mt-lg-4 mt-xl-0 @if(!$isProductInCart) d-none @endif">
                                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.cart.page') }}" class="btn btn-black-custom w-100">{{ trans('base.go_to_cart') }}</a>
                                                    </div>

                                                    @auth
                                                        <div class="col-6 col-sm-auto col-lg-6 col-xl-auto d-flex ml-auto order-xl-3">
                                                            <div class="link-wish-list ml-auto">
                                                            <span class="wrapper-wish-list">
                                                                <div class="i-heart @if($wishListProducts->contains($product->id)) i-heart-active @endif product-wish-list-button" id="{{ $product->slug }}">
                                                                    <svg>
                                                                        <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-heart-hover"></use>
                                                                    </svg>
                                                                </div>
                                                                <span class=" text-remove @if(!$wishListProducts->contains($product->id)) d-none @endif">{{ trans('base.remove_from_wish_list') }}</span>
                                                                <span class="text-add @if($wishListProducts->contains($product->id)) d-none @endif">{{ trans('base.add_to_wish_list') }}</span>
                                                            </span>
                                                            </div>
                                                        </div>
                                                    @endauth
                                                </div>
                                            </div>
                                            <div class="info-content-table">
                                                @if($product->productType->has_brand)
                                                    <div class="table-item d-flex flex-column flex-sm-row flex-lg-column flex-xl-row justify-content-between">
                                                        <div class="table-name mr-6">
                                                            {{ trans('base.brand') }}
                                                        </div>
                                                        <div class="table-value text-sm-right text-lg-left text-xl-right">
                                                            {{ $product->brand->name }}
                                                        </div>
                                                    </div>
                                                @endif
                                                @if($product->productType->has_collection)
                                                    <div class="table-item d-flex flex-column flex-sm-row flex-lg-column flex-xl-row justify-content-between">
                                                        <div class="table-name mr-6">
                                                            {{ trans('base.collection') }}
                                                        </div>
                                                        <div class="table-value text-sm-right text-lg-left text-xl-right">
                                                            {{ $product->collection->name }}
                                                        </div>
                                                    </div>
                                                @endif
                                                @if($product->productType->has_size)
                                                    <div class="table-item d-flex flex-column flex-sm-row flex-lg-column flex-xl-row justify-content-between">
                                                        <div class="table-name mr-6">
                                                            {{ trans('base.size') }}
                                                        </div>
                                                        <div class="table-value text-sm-right text-lg-left text-xl-right">
                                                            @if($product->productType->has_length)
                                                                {{ $product->length }}
                                                            @endif

                                                            @if($product->productType->has_width)
                                                                @if($product->productType->has_length)
                                                                    x
                                                                @endif
                                                                {{ $product->width }}
                                                            @endif

                                                            @if($product->productType->has_height)
                                                                @if($product->productType->has_width || $product->productType->has_length)
                                                                    x
                                                                @endif
                                                                {{ $product->width }}
                                                            @endif
                                                            {{ $product->productType->size_points }}
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="table-item d-flex flex-column flex-sm-row flex-lg-column flex-xl-row justify-content-between">
                                                    <div class="table-name mr-6">
                                                        {{ trans('base.country') }}
                                                    </div>
                                                    <div class="table-value text-sm-right text-lg-left text-xl-right">
                                                        {{ $product->country->name }}
                                                    </div>
                                                </div>

                                                @foreach($product->productType->fields->where('as_image', '!=', true) as $customField)
                                                    <div class="table-item d-flex flex-column flex-sm-row flex-lg-column flex-xl-row justify-content-between">
                                                        <div class="table-name mr-6">
                                                            {{ $customField->field_name }}
                                                        </div>
                                                        <div class="table-value text-sm-right text-lg-left text-xl-right">
                                                            @if ($customField->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_STRING)
                                                                {{ $product->getCustomFieldValue($customField->id) }}
                                                            @elseif($customField->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_NUMBER)
                                                                {{ $product->getCustomFieldValue($customField->id) }}
                                                            @elseif($customField->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_SIZE)
                                                                {{ $product->getCustomFieldValue($customField->id)}} {{ $customField->field_size_name }}
                                                            @elseif($customField->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION)
                                                                @if ($customField->is_multiselectable)
                                                                    {{ $customField->options->whereIn('id', $product->getCustomFieldValue($customField->id))->pluck('name')->implode(', ') }}
                                                                @else
                                                                    {{ $customField->options->whereIn('id', $product->getCustomFieldValue($customField->id))->first()->name }}
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach

                                                @if($product->productType->has_category)
                                                    <div class="table-item table-room d-flex flex-column flex-sm-row flex-lg-column flex-xl-row justify-content-between">
                                                        <div class="table-name mr-6">
                                                            {{ trans('base.for_room') }}
                                                        </div>
                                                        <div class="table-value text-sm-right text-lg-left text-xl-right">
                                                            {{ $product->categories->pluck('name')->implode(', ') }}
                                                        </div>
                                                    </div>
                                                @endif

                                                @foreach($product->productType->fields->where('as_image', true) as $customField)
                                                    <div class="table-item table-room d-flex flex-column flex-sm-row flex-lg-column flex-xl-row justify-content-between">
                                                        <div class="table-name mr-6">
                                                            {{ $customField->field_name }}
                                                        </div>
                                                        <div class="table-value text-right d-flex">
                                                            @if ($customField->is_multiselectable)
                                                                @foreach ($customField->options->whereIn('id', $product->getCustomFieldValue($customField->id)) as $imageOption)
                                                                    <div class="product-option">
                                                                        <img class="product-option-image" src="{{ $imageOption->image_url }}" alt="img" data-toggle="tooltip" title="<span class='help'>{{ $imageOption->name }}</span>">
                                                                    </div>
                                                                @endforeach
                                                            @else
                                                                {{ $customField->options->where('id', $product->getCustomFieldValue($customField->id))->first()->name }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <section class="product-collection-slider">
                <div class="container">
                    <div class="row pb-8 pb-md-11 pb-lg-17 overflow-hidden">
                        <div class="col-12">
                            <div class="head text-center mb-4 mb-md-7 mb-lg-8 pt-3">{{ trans('base.products_from_same_collection', ['PRODUCT_NAME' => $product->productType->name]) }}</div>
                            <div class="cards-products">
                                <div class="swiper-cards-products loading-skeleton">
                                    <div class="swiper-wrapper">
                                        @foreach($productsInSameCollection as $sameCollectionProduct)
                                            <div class="swiper-slide">
                                                <div class="card card-product">
                                                    <div class="card-content">
                                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $sameCollectionProduct->slug]) }}" class="card-link">
                                                            <span class="card-link-image">
                                                                <img src="{{ $sameCollectionProduct->preview_image_url }}" alt="product">
                                                                @if($sameCollectionProduct->special_offers)
                                                                    <div class="card-link-container">
                                                                        @foreach($sameCollectionProduct->special_offers as $specialOffer)
                                                                            <span class="card-link-offer">{{ \App\DataClasses\ProductSpecialOfferOptionsDataClass::get($specialOffer)['name'] ?? '' }}</span>
                                                                      @endforeach
                                                                    </div>
                                                                @endif
                                                                @auth
                                                                    <span class="link-heart @if($wishListProducts->contains($sameCollectionProduct->id)) link-heart-active @endif" id="{{ $sameCollectionProduct->slug }}">
                                                                            <span>{{ trans('base.add_to_wish_list') }}</span>
                                                                            <svg>
                                                                                <use
                                                                                    xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-heart-hover"></use>
                                                                            </svg>
                                                                        </span>
                                                                @endauth
                                                            </span>
                                                            <span class="card-link-title">
                                                                {{ $sameCollectionProduct->name }}
                                                            </span>
                                                            <span class="card-link-price">
                                                               @if($sameCollectionProduct->old_price)
                                                                    <span class="card-link-price--hot">{{ $sameCollectionProduct->price }} {{ $baseCurrency->name_short }} </span> <span class="card-link-price--old">{{ $sameCollectionProduct->old_price }} {{ $baseCurrency->name_short }}</span>
                                                                @else
                                                                    {{ $sameCollectionProduct->price }} {{ $baseCurrency->name_short }}
                                                                @endif
                                                                @if($sameCollectionProduct->productType->product_point_name)
                                                                    <span class="card-link-price--small">/ {{ $sameCollectionProduct->productType->product_point_name }}</span>
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
            <div id="b-single-product-related" class="b-single-product-related section mb-10 mb-md-16">
                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <div class="head text-center mb-4 mb-md-7 mb-lg-8">{{ trans('base.similar_products', ['PRODUCT_NAME' => $product->productType->name]) }}</div>
                            <div class="cards-products card-products-more">
                            </div>
                            <div class="row">
                                <div class="col-12 d-flex align-items-center flex-column">
                                    <button type="button" id="load-similar-products" class="btn btn-more mt-10 mt-md-15 mb-7">
                                        <svg width="37" height="37" viewBox="0 0 37 37" fill="" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M34.6126 2.95344C34.656 3.3662 34.3852 3.73957 33.993 3.83538L33.8831 3.85445L30.2766 4.23213C34.4821 7.70436 37 12.8917 37 18.5043C37 28.505 29.0556 36.6883 19.0874 36.9995C18.5703 37.0157 18.138 36.6095 18.1219 36.0923C18.1058 35.5751 18.5118 35.1427 19.0289 35.1266C27.9865 34.8469 35.1266 27.4921 35.1266 18.5043C35.1266 13.425 32.8336 8.73516 29.0065 5.61367L29.4022 9.38926C29.4456 9.80203 29.1748 10.1754 28.7826 10.2712L28.6727 10.2903C28.2601 10.3337 27.8868 10.0628 27.791 9.6705L27.7719 9.56065L27.16 3.73692C27.1166 3.32416 27.3874 2.95079 27.7796 2.85498L27.8894 2.83591L33.7118 2.22381C34.162 2.17648 34.5653 2.50315 34.6126 2.95344ZM18.5 0C18.7752 0 19.0497 0.00601658 19.3234 0.0180176C19.8402 0.0406793 20.2409 0.478125 20.2182 0.99508C20.1955 1.51204 19.7582 1.91274 19.2414 1.89008C18.995 1.87927 18.7478 1.87386 18.5 1.87386C9.31739 1.87386 1.87342 9.31958 1.87342 18.5043C1.87342 24.1321 4.69071 29.2693 9.24842 32.325L8.53298 28.6339C8.4538 28.2265 8.69105 27.831 9.0734 27.7013L9.18115 27.6728C9.58847 27.5936 9.98393 27.8309 10.1135 28.2133L10.1421 28.3211L11.2592 34.0693C11.3384 34.4767 11.1011 34.8723 10.7188 35.0019L10.611 35.0305L4.86414 36.1478C4.4198 36.2342 3.98956 35.944 3.90319 35.4995C3.82401 35.0921 4.06126 34.6965 4.44361 34.5669L4.55136 34.5383L8.14519 33.8411C3.11068 30.4372 0 24.7406 0 18.5043C0 8.28468 8.28273 0 18.5 0Z" fill=""></path>
                                        </svg>
                                        <span class="btn-more-text">{{ trans('base.filter_show_more') }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('dynamic_scripts')
    <script type="text/javascript">
        const product = {
            similar_products_route: '{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.similar-products', ['productSlug' => $product->slug]) }}',
            add_to_wish_list_text: '{{ trans('base.add_to_wish_list') }}',
        }
    </script>
@endpush
