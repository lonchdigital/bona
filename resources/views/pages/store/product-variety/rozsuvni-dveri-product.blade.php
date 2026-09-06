@extends('layouts.store-main')

@php
    $slidingProductUrl = url()->current();
    $slidingCatalogUrl = url(App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => $product->productType->slug]));
    $slidingDescription = trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) data_get($productText, 'content', '')))));
    $slidingImage = $product->main_image_url ? url($product->main_image_url) : null;
    $slidingAvailability = [
        \App\DataClasses\ProductStatusDataClass::PRODUCT_STATUS_STOCK => 'https://schema.org/InStock',
        \App\DataClasses\ProductStatusDataClass::PRODUCT_STATUS_ORDER => 'https://schema.org/BackOrder',
        \App\DataClasses\ProductStatusDataClass::PRODUCT_STATUS_OUT_OF_STOCK => 'https://schema.org/OutOfStock',
        \App\DataClasses\ProductStatusDataClass::PRODUCT_STATUS_OUT_ASK_MANAGER => 'https://schema.org/LimitedAvailability',
    ];
    $slidingSchemaFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG;
@endphp

@push('structured_data')
    <script type="application/ld+json">{!! json_encode([
        '@'.'context' => 'https://schema.org',
        '@graph' => [
            array_filter([
                '@type' => 'Product',
                '@id' => $slidingProductUrl.'#product',
                'mainEntityOfPage' => ['@id' => $slidingProductUrl.'#webpage'],
                'name' => (string) $product->name,
                'url' => $slidingProductUrl,
                'sku' => $product->sku ?: null,
                'image' => $slidingImage ? [$slidingImage] : null,
                'description' => $slidingDescription ?: null,
                'category' => (string) $product->productType->name,
                'brand' => $product->brand ? ['@type' => 'Brand', 'name' => (string) $product->brand->name] : null,
                'offers' => is_numeric($product->price) && (float) $product->price > 0 ? [
                    '@type' => 'Offer',
                    'url' => $slidingProductUrl,
                    'priceCurrency' => $baseCurrency->code ?: 'UAH',
                    'price' => (string) $product->price,
                    'availability' => $slidingAvailability[$product->availability_status_id] ?? null,
                    'itemCondition' => 'https://schema.org/NewCondition',
                    'seller' => ['@id' => app(\App\Services\Seo\OrganizationSchemaService::class)->organizationId()],
                    'hasMerchantReturnPolicy' => ['@id' => app(\App\Services\Seo\OrganizationSchemaService::class)->merchantReturnPolicyId()],
                ] : null,
            ]),
            [
                '@type' => 'BreadcrumbList',
                '@id' => $slidingProductUrl.'#breadcrumb',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => trans('base.home'), 'item' => url(App\Helpers\MultiLangRoute::getMultiLangRoute('store.home'))],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => (string) $product->productType->name, 'item' => $slidingCatalogUrl],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => (string) $product->name, 'item' => $slidingProductUrl],
                ],
            ],
            array_filter([
                '@type' => 'WebPage',
                '@id' => $slidingProductUrl.'#webpage',
                'url' => $slidingProductUrl,
                'name' => (string) ($product->meta_title ?: $product->name),
                'description' => $product->meta_description ?: ($slidingDescription ?: null),
                'inLanguage' => app()->getLocale() === 'ru' ? 'ru-UA' : 'uk-UA',
                'isPartOf' => ['@id' => app(\App\Services\Seo\OrganizationSchemaService::class)->websiteId()],
                'breadcrumb' => ['@id' => $slidingProductUrl.'#breadcrumb'],
                'mainEntity' => ['@id' => $slidingProductUrl.'#product'],
            ]),
        ],
    ], $slidingSchemaFlags) !!}</script>
@endpush

@section('title')
    @if($product->meta_title)
        <title>{{ $product->meta_title }}</title>
        <meta name="title" content="{{ $product->meta_title }}">
    @endif

    @if($product->meta_description)
        <meta name="description" content="{{ $product->meta_description }}">
    @endif
    @if($product->meta_keywords)
        <meta name="keywords" content="{{ $product->meta_keywords }}">
    @endif

    @if($product->meta_tags)
        {!! $product->meta_tags !!}
    @endif

    <meta property="og:title" content="{{ $product->name . ' - ' . trans('base.site_title') }}">

@endsection

@section('content')

    @include('pages.store.partials.page_header', ['links' => [App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => $product->productType->slug]) => $product->productType->name, 'own' => $product->name]])


    <!-- ========================  Product ======================== -->
    <section class="product">

        <div class="main">
            <div class="container">
                <div class="row product-flex 22">

                    <div class="art-gallery-all-slides-container d-none">

                        <div class="art-swiper-single-wallpaper">
                            <div class="swiper-slide" data-color-id="{{ $product->main_color_id ?? 0 }}">
                                <a data-fancybox="single-wallpaper-gallery" href="{{ $product->main_image_url }}">
                                    <img src="{{ $product->main_image_url }}" alt="{{ $product->name }}">
                                </a>
                            </div>
                            @foreach($productGallery as $image)
                                <div class="swiper-slide" data-color-id="{{ $image->color_id ?? 0 }}">
                                    <a data-fancybox="single-wallpaper-gallery" href="{{ $image->gallery_image_url }}">
                                        <img src="{{ $image->gallery_image_url }}" alt="{{ $product->name }}">
                                    </a>
                                </div>
                            @endforeach
                        </div>

                        <div class="art-swiper-single-wallpaper-thumbs">
                            <div class="swiper-slide" data-color-id="{{ $product->main_color_id ?? 0 }}">
                                <div class="art-swiper-slide">
                                    <img src="{{ $product->main_image_url }}" alt="{{ $product->name }}">
                                </div>
                            </div>
                            @foreach($productGallery as $image)
                                <div class="swiper-slide" data-color-id="{{ $image->color_id ?? 0 }}">
                                    <div class="art-swiper-slide">
                                        <img src="{{ $image->gallery_image_url }}" alt="{{ $product->name }}">
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>


                    <div class="col col-md-7 art-single-product-gallery">
                        @if( !is_null($product->main_image_url) )
                            <div class="">
                                <div class="swiper-single-wallpaper-wrap d-flex">
                                    <div class="swiper-single-wallpaper mb-md-5">
                                        <div class="swiper-wrapper">
                                            {{-- Got from js --}}
                                        </div>
                                        <div class="swiper-button-next"></div>
                                        <div class="swiper-button-prev"></div>
                                    </div>
                                    <div class="swiper-pagination mt-5 d-sm-none"></div>
                                </div>

                                <div class="swiper-single-wallpaper-thumbs-wrap d-none-1111 d-sm-flex align-items-center mb-md-13">
                                    <div class="swiper-pagination mr-4 mr-xl-10"></div>
                                    <div class="art-single-wallpaper-thumbs-wrapper">
                                        <div class="swiper-single-wallpaper-thumbs swiper swiper-initialized swiper-horizontal swiper-free-mode swiper-watch-progress swiper-backface-hidden swiper-thumbs">
                                            <div class="swiper-wrapper">
                                                {{-- Got from js --}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="col-md-5 col-sm-12 product-flex-info">
                        <div class="clearfix">

                            <h1 class="title" data-title="Sofa">{{ $product->name }}</h1>
                            <div class="clearfix">

                                @if( !is_null($productText['short_content']))
                                    <div class="short-description">
                                        {!! $productText['short_content'] !!}
                                    </div>
                                @endif




                            </div> <!--/clearfix-->
                        </div> <!--/product-info-wrapper-->
                    </div>

                </div>
            </div>
        </div>

        <!-- === Product tabs === -->
        <div class="info art-product-tabs">
            <div class="container">
                <div class="row">

                    <!-- === nav-tabs === -->
                    <div class="col-md-12">
                        <ul class="nav nav-tabs product-tabs-nav" role="tablist">
                            @if( count( $characteristics ) > 0 )
                                <li role="presentation" class="active">
                                    <a href="#characteristics" aria-controls="characteristics" role="tab" data-toggle="tab">
                                        <span>{{ trans('base.characteristics') }}</span>
                                    </a>
                                </li>
                            @endif

                            @if( count( $productVideos ) > 0 )
                                <li role="presentation" class="{{ count($characteristics) == 0 ? 'active' : '' }}">
                                    <a href="#open-systems" aria-controls="open-systems" role="tab" data-toggle="tab">
                                        <span>{{ trans('base.open_systems') }}</span>
                                    </a>
                                </li>
                            @endif

                            @if( !is_null($productText['content']))
                                <li role="presentation" class="{{ (count($characteristics) == 0 && count($productVideos) == 0) ? 'active' : '' }}">
                                    <a href="#description" aria-controls="description" role="tab" data-toggle="tab">
                                        <span>{{ trans('base.description') }}</span>
                                    </a>
                                </li>
                            @endif
                        </ul>

                        <!-- === tab-panes === -->
                        <div class="tab-content">

                            @if( count( $characteristics ) > 0 )
                                <div role="tabpanel" class="tab-pane active" id="characteristics">
                                    <div class="content">
                                        <h3>{{ trans('base.characteristics') }}</h3>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="art-product-characteristics">
                                                    @foreach($characteristics as $characteristic)
                                                        <div class="art-characteristic-line">
                                                            <span class="art-characteristic-name">{{ $characteristic['name'] }}</span>
                                                            <span class="art-characteristic-value">{{ $characteristic['value'] }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if( count( $productVideos ) > 0 )
                                <div role="tabpanel" class="tab-pane {{ count($characteristics) == 0 ? 'active' : '' }}" id="open-systems">
                                    <div class="content">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="art-product-video">
                                                    <h3>{{ trans('base.open_systems') }}</h3>
                                                    <ul class="nav nav-tabs art-product-video-tabs" role="tablist">
                                                        @foreach($productVideos as $item)
                                                            <li role="presentation" class="{{ $loop->first ? 'active' : '' }}">
                                                                <a href="#{{ Illuminate\Support\Str::slug($item->tab) .'-'. $loop->index }}" aria-controls="{{ Illuminate\Support\Str::slug($item->tab) .'-'. $loop->index }}" role="tab" data-toggle="tab">
                                                                    <span>{{ $item->tab }}</span>
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                    @foreach($productVideos as $item)
                                                        <div role="tabpanel" class="tab-pane{{ $loop->first ? ' active' : '' }}" id="{{ Illuminate\Support\Str::slug($item->tab) .'-'. $loop->index }}">
                                                            {!! $item->iframe !!}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div> <!--/row-->
                                    </div> <!--/content-->
                                </div> <!--/tab-pane-->
                            @endif

                            @if( !is_null($productText['content']))
                                <div role="tabpanel" class="tab-pane {{ (count($characteristics) == 0 && count($productVideos) == 0) ? 'active' : '' }}" id="description">
                                    <div class="content">
                                        <h3>{{ trans('base.description') }}</h3>
                                        <div class="row">
                                            <div class="col-md-12">
                                                {!! $productText['content'] !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                        </div> <!--/tab-content-->


                    </div>
                </div> <!--/row-->
            </div> <!--/container-->
        </div> <!--/info-->
    </section>

    <x-precise-form-component />

    @if(count($sameTypeProducts))
        <!-- ======================== Products  ======================== -->
        <section class="products">

            <div class="container">

                <div class="row">
                    <header class="col-12 art-header-left">
                        <div>
                            <h2 class="title">{{trans('base.see_more')}}</h2>
                        </div>
                    </header>
                </div>

                <div class="art-products-slider-wrapper art-big-wrapper art-carousel">
                    <div class="swiper art-products-owl-items art-three-in-row art-big-wrapper art-swiper-common">
                        <div class="swiper-wrapper">
                            @foreach($sameTypeProducts as $product)
                                <div class="swiper-slide">
                                    @include('pages.store.partials.product_item_minimal', ['product' => $product, 'productTypeName' => $product->productType->name])
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                </div>

            </div> <!--/container-->
        </section>
    @endif

@endsection

@push('dynamic_scripts')
@endpush
