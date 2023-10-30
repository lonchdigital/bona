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

    <!-- ========================  Main header ======================== -->

    <section class="main-header" style="background-image:url({{ asset('storage/bg-images/catalog-header-bg.png') }})">
        <header>
            <div class="container">
                <h1 class="h2 title">{{ $product->name }}</h1>
                <ol class="breadcrumb breadcrumb-inverted">
                    <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}"><span class="icon icon-home"></span></a></li>
                    <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => $product->productType->slug]) }}">{{ $product->productType->name }}</a></li>
                    <li><span class="active">{{ $product->name }}</span></li>
                </ol>
            </div>
        </header>
    </section>

    <!-- ========================  Product ======================== -->

    <section class="product">

        <div class="main">
            <div class="container">
                <div class="row product-flex">

                    <div class="col col-md-7 art-single-product-gallery">

                        <div class="">
                            <div class="swiper-single-wallpaper-wrap d-flex">
                                <div class="swiper-single-wallpaper mb-md-5">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <a data-fancybox="single-wallpaper-gallery" href="{{ $product->main_image_url }}">
                                                <img src="{{ $product->main_image_url }}" alt="img">
                                            </a>
                                        </div>
                                        @foreach($productGallery as $image)
                                            <div class="swiper-slide">
                                                <a data-fancybox="single-wallpaper-gallery" href="{{ $image->gallery_image_url }}">
                                                    <img src="{{ $image->gallery_image_url }}" alt="img">
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="swiper-button-next"></div>
                                    <div class="swiper-button-prev"></div>
                                </div>
                                <div class="swiper-pagination mt-5 d-sm-none"></div>
                            </div>
                            <div class="swiper-single-wallpaper-thumbs-wrap d-none d-sm-flex align-items-center mb-md-13">
                                <div class="swiper-pagination mr-4 mr-xl-10"></div>
                                <div class="art-single-wallpaper-thumbs-wrapper">
                                    <div class="swiper-single-wallpaper-thumbs">
                                        <div class="swiper-wrapper">
                                            <div class="swiper-slide">
                                                <img src="{{ $product->main_image_url }}" alt="img">
                                            </div>
                                            @foreach($productGallery as $image)
                                                <div class="swiper-slide">
                                                    <img src="{{ $image->gallery_image_url }}" alt="img">
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>


                    <div class="col-md-5 col-sm-12 product-flex-info">
                        <div class="clearfix">

                            <!-- === product-title === -->

{{--                            @dd($product->productType->fields)--}}

                            <h1 class="title" data-title="Sofa">{{ $product->name }}</h1>

                            <div class="clearfix">

                                <hr />

                                <div class="info-box">
                                    <span><strong>{{ trans('base.sku') }}</strong></span>
                                    <span>{{ $product->sku }}</span>
                                </div>

                                @foreach($product->productType->fields->where('as_image', '!=', true)->where('display_on_single', '==', true) as $customField)
                                    <div class="info-box">
                                        <span><strong>{{ $customField->field_name }}</strong></span>

                                        @if ($customField->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_STRING)
                                            <span>{{ $product->getCustomFieldValue($customField->id) }}</span>
                                        @elseif($customField->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION)
                                            @if ($customField->is_multiselectable)
                                                <span>{{ $customField->options->whereIn('id', $product->getCustomFieldValue($customField->id))->pluck('name')->implode(', ') }}</span>
                                            @else
                                                <span>{{ $customField->options->whereIn('id', $product->getCustomFieldValue($customField->id))->first()->name }}</span>
                                            @endif
                                        @endif
                                    </div>
                                @endforeach

                                <div class="info-box">
                                    <span><strong>{{ trans('base.availability') }}</strong></span>
                                    <span>
                                        @if ($product->availability_status_id == 1)
                                            <i class="fa fa-check-square-o"></i>
                                        @elseif($product->availability_status_id == 3)
                                            <i class="fa fa-truck"></i>
                                        @endif
                                        {{ \App\DataClasses\ProductStatusDataClass::get($product->availability_status_id)['name'] }}
                                    </span>
                                </div>


                                <hr />

                                <!-- === info-box === -->

                                <div class="info-box">
                                    <span><strong>{{ trans('base.color') }}</strong></span>
                                    <div class="product-colors clearfix">
                                        <span class="color-btn color-btn-red"></span>
                                        <span class="color-btn color-btn-blue checked"></span>
                                        <span class="color-btn color-btn-green"></span>
                                        <span class="color-btn color-btn-gray"></span>
                                        <span class="color-btn color-btn-biege"></span>
                                    </div>
                                </div>


                                @foreach($categoryProducts as $cat => $subProducts)

                                    <div class="sub-product-wrapper">

                                        <div class="info-box art-popup-link">
                                            <span><strong>{{ $cat }}</strong></span>
                                            <span class="art-dialog-link" data-fancybox data-src="#dialog-content-{{ Illuminate\Support\Str::slug($cat) }}">{{ trans('base.select') }}</span>
                                        </div>

                                        <div class="added-sub-products" data-wrapper="dialog-content-{{ Illuminate\Support\Str::slug($cat) }}">
                                            @foreach($subProducts as $subProduct)
                                                @if( $cartService->isProductInCart($subProduct, $cart) )

                                                    @for($i = 0; $i < $cartService->getCountOfSpecificProduct($subProduct, $cart); $i++)
                                                        <span class="added-line" data-slug="{{ $subProduct->slug }}"><i class="fa fa-close"></i>{{ $subProduct->name }}</span>
                                                    @endfor
                                                @endif
                                            @endforeach
                                        </div>

                                        <div id="dialog-content-{{ Illuminate\Support\Str::slug($cat) }}" class="art-popup-single-product">
                                            <span class="art-category-title">{{ $cat }}</span>
                                            <div class="art-popup-list-sub-products">
                                                @foreach($subProducts as $subProduct)

                                                    <div class="art-product-item">
                                                        <div class="art-product-data">
                                                            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $subProduct->slug]) }}" class="art-product-link">
                                                                <div class="image">
                                                                    <img src="{{ $subProduct->preview_image_url }}" alt="">
                                                                </div>
                                                                <div class="text">
                                                                    <h2 class="product-title">{{ $subProduct->name }}</h2>
                                                                    <span class="price-wrapper">
                                                                        <span class="price">{{ $subProduct->price }}</span>
                                                                        <span class="currency">{{ $baseCurrency->name_short }}</span>
                                                                    </span>
                                                                </div>
                                                            </a>
                                                            <button type="button"
                                                                    class="btn w-100 single-sub-product-add-to-cart"
                                                                    id="{{ $subProduct->slug }}"
                                                                    @if( $cartService->isProductInCart($subProduct, $cart) )
                                                                        data-count="{{ $cartService->getCountOfSpecificProduct($subProduct, $cart) }}"
                                                                    @else
                                                                        data-count="0"
                                                                    @endif
                                                            >{{ trans('base.select') }}</button>
                                                        </div>
                                                    </div>

                                                @endforeach
                                            </div>
                                        </div>

                                    </div>

                                @endforeach


                                <hr />

                                <div class="info-box">
                                    <span><strong>{{ trans('base.quantity') }}</strong></span>

                                    <div class="@if($isProductInCart) d-none @endif" id="count-of-products-body">
                                        <div class="custom-control-number mr-2">
                                            <span class="counter minus"></span>
                                            <input type="number" class="" id="count-of-products" min="1" value="1">
                                            <span class="counter plus"></span>
                                        </div>
                                    </div>

                                </div>

                                <div class="info-box price">
                                    <span><strong>{{ trans('base.price') }}</strong></span>
                                    <span class="h3">{{ $product->price }} {{ $baseCurrency->name_short }}</span>
                                </div>

                                <div class="info-content-add d-flex align-items-center justify-content-between flex-wrap">
                                    <div class="d-flex flex-wrap align-items-center no-gutters w-100">

                                        <!--id="count-of-products-body-->

                                        <div class="col col-sm-auto col-lg col-xl-auto order-last order-sm-0 order-lg-last order-xl-2 mt-4 mt-sm-0 mt-lg-4 mt-xl-0 @if($isProductInCart) d-none @endif">
                                            <button type="button" class="btn btn-black-custom w-100 single-product-add-to-cart" id="{{ $product->slug }}">{{ trans('base.add_to_cart') }}</button>
                                        </div>


                                        <div class="go-to-cart-body col-6 col-sm-auto col-lg-6 col-xl-auto order-xl-1 @if(!$isProductInCart) d-none @endif">
                                            <span class="mr-2">{{ trans('base.in_cart') }}</span>
                                        </div>
                                        <div class="go-to-cart-body col col-sm-auto col-lg col-xl-auto order-last order-sm-0 order-lg-last order-xl-2 mt-4 mt-sm-0 mt-lg-4 mt-xl-0 @if(!$isProductInCart) d-none @endif">
                                            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.cart.page') }}" class="btn btn-black-custom w-100">{{ trans('base.go_to_cart') }}</a>
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

                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active">
                                <a href="#characteristics" aria-controls="characteristics" role="tab" data-toggle="tab">
                                    <span>{{ trans('base.characteristics') }}</span>
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#open-systems" aria-controls="open-systems" role="tab" data-toggle="tab">
                                    <span>{{ trans('base.open_systems') }}</span>
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#description" aria-controls="description" role="tab" data-toggle="tab">
                                    <span>{{ trans('base.description') }}</span>
                                </a>
                            </li>
                        </ul>

                        <!-- === tab-panes === -->
                        <div class="tab-content">

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
                                    </div> <!--/row-->

                                </div> <!--/content-->
                            </div> <!--/tab-pane-->

                            <div role="tabpanel" class="tab-pane" id="open-systems">
                                <div class="content">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="art-product-video">
                                                <h3>{{ trans('base.open_systems') }}</h3>

                                                @if($productVideos)
                                                    <ul class="nav nav-tabs art-product-video-tabs" role="tablist">
                                                        @foreach($productVideos as $item)
                                                            <li role="presentation" class="{{ $loop->first ? 'active' : '' }}">
                                                                <a href="#{{ Illuminate\Support\Str::slug($item->tab) }}" aria-controls="{{ Illuminate\Support\Str::slug($item->tab) }}" role="tab" data-toggle="tab">
                                                                    <span>{{ $item->tab }}</span>
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                    @foreach($productVideos as $item)
                                                        <div role="tabpanel" class="tab-pane{{ $loop->first ? ' active' : '' }}" id="{{ Illuminate\Support\Str::slug($item->tab) }}">
                                                            {!! $item->iframe !!}
                                                        </div>
                                                    @endforeach
                                                @endif

                                            </div>
                                        </div>
                                    </div> <!--/row-->
                                </div> <!--/content-->
                            </div> <!--/tab-pane-->

                            <div role="tabpanel" class="tab-pane" id="description">

                                <!-- ============ ratings ============ -->

                                <div class="content">
                                    <h3>{{ trans('base.description') }}</h3>

                                    <div class="row">

                                        <div class="col-md-12">
                                            {!! $productText['content'] !!}
                                        </div>

                                    </div> <!--/row-->
                                </div> <!--/content-->
                            </div> <!--/tab-pane-->
                        </div> <!--/tab-content-->


                    </div>
                </div> <!--/row-->
            </div> <!--/container-->
        </div> <!--/info-->
    </section>


    <!-- ======================== Contact Form ======================== -->

    <section class="art-contact-form-section" style="background-image:url({{ asset('storage/bg-images/form-bg.png') }})">
        <div class="container">
            <div class="row">
                <div class="col-md-offset-2 col-md-8 text-center">
                    <h2 class="title">Не знаєте які двері обрати?</h2>
                    <p>
                        We believe in creativity as one of the major forces of progress. With this idea, we traveled throughout Italy
                        to find exceptional artisans and bring their unique handcrafted objects to connoisseurs everywhere.
                    </p>

                    <form action="#" method="post" class="art-contact-form">
                        @csrf
                        <p class="art-fields-row">
                            <input type="text" class="art-light-field" placeholder="Ім’я">
                            <input type="text" class="art-light-field" placeholder="Телефон">
                        </p>
                        <p><a href="about.html" class="btn btn-clean">Відправити</a></p>
                    </form>


                </div>
            </div>
        </div>
    </section>


    @if(count($sameTypeProducts))
        <!-- ======================== Products  ======================== -->
        <section class="products">

            <div class="container">

                <header>
                    <div class="row">
                        <div class="col-md-offset-2 col-md-8 text-center">
                            <h2 class="title">{{ trans('base.see_more') }}</h2>
                            <div class="text">
                                <p>Check out our latest collections</p>
                            </div>
                        </div>
                    </div>
                </header>


                <div class="art-products-slider-wrapper">
                    <div class="art-products-owl-items">
                        @foreach($sameTypeProducts as $product)
                            <div class="item">

                                <div class="art-product-item">
                                    <div class="art-product-data">
                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $product->slug]) }}" class="">
                                            <div class="image">
                                                {{--                                        <img src="{{ $product->product->preview_image_url }}" alt="">--}}
                                                <img src="{{ $product->main_image_url }}" alt="">
                                            </div>
                                            <div class="text">
                                                <h2 class="product-title">{{ $product->name }}</h2>
                                                <span class="price-wrapper">
                                                <span class="price">{{ $product->price }}</span>
                                                <span class="currency">{{ $baseCurrency->name_short }}</span>
                                            </span>
                                            </div>
                                        </a>
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>
                </div> <!--/row-->
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
