@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - ' . $collection->name }}</title>
@endsection

@section('content')
    <main class="main collection-p" id="collection-p">
        <div class="content">
            <div class="entry-content">
                <section class="blog-banner-top mb-4 mb-md-10 mb-lg-13">
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
                                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.brands.list.page') }}">{{ trans('base.brands') }}</a>
                                                <meta itemprop="position" content="2"/>
                                            </li>
                                            <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.brand.page', ['brandSlug' => $collection->brand->slug]) }}">{{ $collection->brand->name }}</a>
                                                <meta itemprop="position" content="3"/>
                                            </li>
                                            <li class="breadcrumb-item active" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem" aria-current="page">
                                                {{ $collection->name }}
                                                <meta itemprop="position" content="4"/>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="container">
                        <div class="row">
                            <div class="col">
                                <div class="head text-lg-center mb-5 mb-md-9 mt-6 mt-md-8">{{ $collection->name }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="container container-max">
                        <div class="row">
                            <div class="col">
                                <div class="swiper-blog-banner-top">
                                    <div class="swiper-wrapper">
                                        @foreach($collection->slides as $slide)
                                            <div class="swiper-slide">
                                                <div class="bg-wrap row no-gutters">
                                                    <div class="col">
                                                        <img src="{{ $slide->image_1_url }}" alt="img">
                                                    </div>
                                                    <div class="col">
                                                        <img src="{{ $slide->image_2_url }}" alt="img">
                                                    </div>
                                                </div>
                                                <div class="content">
                                                    <a href="#">
                                                        <div class="logo">
                                                            <img src="{{ $collection->brand->logo_image_url }}" alt="img">
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="container">
                        <div class="row">
                            <div class="col">
                                <div class="swiper-control mt-6">
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
                </section>
                <section class="collection-list cards-products" id="collection-list">
                    <div class="container">
                        <div class="row mb-5 mb-md-8">
                            <div class="col">
                                <div class="products-count mb-1 mb-lg-0"><span class="count">{{ $collection->products()->whereNull('parent_product_id')->count() }}</span> {{ trans('base.products') }}</div>
                            </div>
                            <div class="col-12 col-lg-9">
                                <div class="collection-dropdown-wrap row flex-column flex-lg-row justify-content-end">
                                    <div class="collection-dropdown-item col-auto">
                                        <div class="dropdown dropdown-custom mb-1 mb-lg-0">
                                            <button
                                                class="btn btn-dropdown dropdown-toggle d-block mx-auto ml-md-auto mr-md-0 ml-lg-0"
                                                type="button" id="dropdownMenuButton" data-toggle="dropdown"
                                                aria-haspopup="true" aria-expanded="false">
                                                <span class="text-left">{{ trans('base.sort_by') }}:</span>
                                                @isset($filtersData['sort_by'])
                                                    <span
                                                        class="text-right">{{ App\DataClasses\ProductSortOptionsDataClass::get()->where('id', $filtersData['sort_by'])->first()['name'] }}</span>
                                                @else
                                                    <span
                                                        class="text-right">{{ App\DataClasses\ProductSortOptionsDataClass::get()->where('is_active_by_default')->first()['name'] }}</span>
                                                @endisset

                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                @foreach(App\DataClasses\ProductSortOptionsDataClass::get() as $sortFilter)
                                                    <a class="dropdown-item sort-by-option @if(!isset($filtersData['sort_by']) && $sortFilter['is_active_by_default'] || isset($filtersData['sort_by']) && $filtersData['sort_by'] == $sortFilter['id']) active @endif"
                                                       href="#"
                                                       id="{{ $sortFilter['id'] }}">{{ $sortFilter['name'] }}</a>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="collection-dropdown-item col-auto">
                                        <div class="dropdown dropdown-custom">
                                            <button
                                                class="btn btn-dropdown d-block mx-auto ml-md-auto mr-md-0 dropdown-toggle"
                                                type="button" id="dropdownMenuButton2" data-toggle="dropdown"
                                                aria-haspopup="true" aria-expanded="false">
                                                        <span
                                                            class="text-left">{{ trans('base.show_items_per_page') }}:</span>
                                                @isset($filtersData['per_page'])
                                                    <span
                                                        class="text-right">{{ $filtersData['per_page'] }}</span>
                                                @else
                                                    <span class="text-right">24</span>
                                                @endisset
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right"
                                                 aria-labelledby="dropdownMenuButton2">
                                                <a class="dropdown-item @if(!isset($filtersData['per_page']) || (isset($filtersData['per_page']) && $filtersData['per_page'] == 24)) active @endif"
                                                   href="#"
                                                   id="show-24-items-per-page">24 {{ trans('base.per_page') }}</a>
                                                <a class="dropdown-item @if(isset($filtersData['per_page']) && $filtersData['per_page'] == 36) active @endif"
                                                   href="#"
                                                   id="show-36-items-per-page">36 {{ trans('base.per_page') }}</a>
                                                <a class="dropdown-item @if(isset($filtersData['per_page']) && $filtersData['per_page'] == 48) active @endif"
                                                   href="#"
                                                   id="show-48-items-per-page">48 {{ trans('base.per_page') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="container">
                        <div class="row">
                            <div class="col">
                                <div class="row collection-list-content">
                                    <div class="col-auto mb-10 content">
                                        @foreach($productsPaginated as $product)
                                            <div class="card card-product">
                                                <div class="card-content">
                                                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $product->slug]) }}"
                                                       class="card-link">
                                                                <span class="card-link-image">
                                                                    <img src="{{ $product->preview_image_url }}"
                                                                         alt="product">
                                                                    @if($product->special_offers)
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
                                                                <span class="card-link-price--hot">{{ $product->price }} {{ $baseCurrency->name_short }} </span> <span class="card-link-price--old">{{ $product->old_price }} {{ $baseCurrency->name_short }}</span>
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
                                        @endforeach
                                    </div>
                                    {{ $productsPaginated->links('pagination.store') }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <nav class="bg-white">
                                    <ul class="pagination justify-content-center mb-0"></ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </section>
                <section class="collection-other">
                    <div class="container">
                        <div class="row">
                            <div class="col">
                                <div class="head text-md-center mb-5 mb-md-11">{{ trans('base.other_collections_of_the_brand') }}</div>
                            </div>
                        </div>
                        <div class="row collection-other-list mb-5 mb-md-16 justify-content-center">
                            @foreach($anotherCollections as $anotherCollection)
                                <div class="col-12 col-sm-6 col-lg-4 collection-other-item">
                                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.collection.page', ['collectionSlug' => $anotherCollection->slug]) }}">
                                        <div class="collection-other-item-innner p-4 p-md-6">
                                            <div class="collection-other-list-title text-center">{{ $anotherCollection->name }}</div>
                                            <div class="collection-other-list-count text-center mb-6">{{ trans('base.products_in_collection') }}: <span class="count">{{ $anotherCollection->products_count }}</span></div>
                                            <div class="collection-other-pic-wrap d-flex flex-wrap mb-4">
                                                @if( $anotherCollection->products_count < 4)
                                                    @if( $anotherCollection->products_count < 1)
                                                        <div class="w-100">
                                                            <img src="{{ $collection->brand->logo_image_url }}" alt="img">
                                                        </div>
                                                    @else
                                                        <div class="w-100">
                                                            <img src="{{ $anotherCollection->products->first()->preview_image_url }}" alt="img">
                                                        </div>
                                                    @endif
                                                @else
                                                    @php
                                                        $shownImages = 0;
                                                    @endphp
                                                    @foreach($anotherCollection->products as $product)
                                                        @php
                                                            if ($shownImages > 4) {
                                                                break;
                                                            }

                                                            $shownImages++;
                                                        @endphp
                                                        <div class="collection-other-pic">
                                                            <img src="{{ $product->preview_image_url }}" alt="img">
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                            <div class="collection-other-logo text-center">
                                                <img src="{{ $collection->brand->logo_image_url }}" alt="img">
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.brand.page', ['brandSlug' => $collection->brand->slug]) }}" class="btn btn-outline-black-custom d-block mx-auto">{{ trans('base.all_collections_of_brand') }}</a>
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

@push('dynamic_scripts')
    <script type="text/javascript">
        const collection = {
            slug: '{{ $collection->slug }}',
        };
    </script>
@endpush
