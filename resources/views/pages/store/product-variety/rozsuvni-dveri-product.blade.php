@extends('layouts.store-main')

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

        <script type="application/ld+json">
            {
                "@context": "https://schema.org/",
                "@type": "Product",
                "name": "{{ $product->name }}",
                "image": "{{ url('/') . $product->main_image_url }}",
                @if( !is_null($productText['content']))
                "description": "{{ $productText['content'] }}",
                @endif
            @if( !is_null($product->brand) )
                "brand": {
                    "@type": "Brand",
                    "name": "{{ $product->brand->name }}"
                },
                @endif
            "offers": {
                "@type": "Offer",
                "priceCurrency": "{{ $baseCurrency->name_short }}",
                    "price": "{{ $product->price }}",
                    "availability": "{{ ($product->availability_status_id == 2) ? trans('shop.product_status_stock') : trans('shop.product_status_out_of_stock') }}"
                }
            }
        </script>

        <div class="main">
            <div class="container">
                <div class="row product-flex 22">

                    <div class="art-gallery-all-slides-container d-none">

                        <div class="art-swiper-single-wallpaper">
                            <div class="swiper-slide" data-color-id="{{ $product->main_color_id ?? 0 }}">
                                <a data-fancybox="single-wallpaper-gallery" href="{{ $product->main_image_url }}">
                                    <img src="{{ $product->main_image_url }}" alt="img">
                                </a>
                            </div>
                            @foreach($productGallery as $image)
                                <div class="swiper-slide" data-color-id="{{ $image->color_id ?? 0 }}">
                                    <a data-fancybox="single-wallpaper-gallery" href="{{ $image->gallery_image_url }}">
                                        <img src="{{ $image->gallery_image_url }}" alt="img">
                                    </a>
                                </div>
                            @endforeach
                        </div>

                        <div class="art-swiper-single-wallpaper-thumbs">
                            <div class="swiper-slide" data-color-id="{{ $product->main_color_id ?? 0 }}">
                                <div class="art-swiper-slide">
                                    <img src="{{ $product->main_image_url }}" alt="img">
                                </div>
                            </div>
                            @foreach($productGallery as $image)
                                <div class="swiper-slide" data-color-id="{{ $image->color_id ?? 0 }}">
                                    <div class="art-swiper-slide">
                                        <img src="{{ $image->gallery_image_url }}" alt="img">
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

                                <div class="info-content-add d-flex align-items-center justify-content-between flex-wrap">
                                    <div class="d-flex flex-wrap align-items-center no-gutters w-100">

                                        <a href="" class="btn btn-main art-header-coll-button" data-fancybox data-src="#order-count">{{ trans('base.order_count') }}</a>

                                        <div id="order-count" class="art-popup-call-measurer">
                                            <div class="art-measurer-form-wrapper">
                                                <div class="container">

                                                    <div class="row">
                                                        <div class="col-12 text-center">
                                                            <form action="#" id="order-count-form" method="post" class="art-contact-form">
                                                                @csrf

                                                                <header class="art-light">
                                                                    <div class="text-center">
                                                                        <h2 class="title h2">{{ trans('base.order_count') }}</h2>
                                                                        <div class="subtitle font-two">
                                                                            <p class="art-form-description">{{ trans('base.call_measurer_description') }}</p>
                                                                        </div>
                                                                    </div>
                                                                </header>

                                                                <div class="art-fields-row">
                                                                    <div>
                                                                        <input type="text" class="art-light-field name-field" name="name" placeholder="{{ trans('base.name') }}">
                                                                    </div>
                                                                    <div>
                                                                        <input type="text" class="art-light-field phone-field" name="phone" placeholder="{{ trans('base.phone') }}">
                                                                    </div>
                                                                </div>
                                                                <div class="checkbox checkbox-white agreement-line agree-field">
                                                                    <input type="checkbox" name="agree" value="1">
                                                                    <label>{{ trans('base.agreement_line_start') . ' ' }}<a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.static-page.page', ['staticPageSlug' => 'dogovir-publichnoyi-oferti']) }}" class="color-white">{{ trans('base.agreement_line_end') }}</a></label>
                                                                </div>
                                                                <input type="hidden" name="event" value="submit_form_order_count">
                                                                <p><button type="submit" class="btn btn-empty">{{ trans('base.send') }}</button></p>

                                                                <a href="{{ url()->current() }}" class="d-none art-current-product-link">{{ $product->name }}</a>

                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>



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

    <!-- Popup Added Product -->
    <a href="" class="btn btn-main art-header-coll-button d-none" id="product-added-to-cart-button" data-fancybox data-src="#product-added-to-cart"></a>
    <div id="product-added-to-cart" class="art-popup-window">
        <div class="art-measurer-form-wrapper">
            <div class="container">

                <header class="art-light">
                    <div class="text-center">
                        <h2 class="title h2">{{ trans('base.product_add_to_cart_success') }}</h2>
                        <div class="art-popup-content font-two">
                            <div class="art-buttons-line">
                                <div>
                                    <a href="#" data-fancybox-close class="btn btn-empty is-close-btn" title="Close">{{ trans('base.continue_shopping') }}</a>
                                </div>
                                <div>
                                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.cart.page') }}" class="btn btn-main">{{ trans('base.go_to_cart') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

            </div>
        </div>
    </div>
    <!-- /Popup Added Product -->

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
    <script type="text/javascript">
        const product = {
            similar_products_route: '{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.similar-products', ['productSlug' => $product->slug]) }}',
            add_to_wish_list_text: '{{ trans('base.add_to_wish_list') }}',
        }
    </script>
@endpush
