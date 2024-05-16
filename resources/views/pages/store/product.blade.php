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
                            <div class="swiper-slide">
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
                            <div class="swiper-slide">
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
                                            @if(count($productGallery) == 0)
                                                <div class="swiper-slide" data-color-id="0">
                                                    <a data-fancybox="single-wallpaper-gallery" href="{{ $product->main_image_url }}">
                                                        <img src="{{ $product->main_image_url }}" alt="img">
                                                    </a>
                                                </div>
                                            @endif
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
                                                @if(count($productGallery) == 0)
                                                    <div class="swiper-slide" data-color-id="0">
                                                        <div class="art-swiper-slide">
                                                            <img src="{{ $product->main_image_url }}" alt="img">
                                                        </div>
                                                    </div>
                                                @endif
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

                                @if($product->sku)
                                    <div class="info-box font-two">
                                        <span class="art-option-name">{{ trans('base.sku') }}</span>
                                        <span class="art-option-value">{{ $product->sku }}</span>
                                    </div>
                                @endif

                                @foreach($product->productType->fields->where('as_image', '!=', true)->where('display_on_single', '==', true) as $customField)

                                        @if ($customField->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_STRING)
                                            <div class="info-box font-two">
                                                <span class="art-option-name">{{ $customField->field_name }}</span>
                                                <span class="art-option-value">{{ $product->getCustomFieldValue($customField->id) }}</span>
                                            </div>
                                        @elseif($customField->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION)
                                            @if ($customField->is_multiselectable)
                                                <span class="art-option-value">{{ $customField->options->whereIn('id', $product->getCustomFieldValue($customField->id))->pluck('name')->implode(', ') }}</span>
                                            @else
                                                @if($customField->options->whereIn('id', $product->getCustomFieldValue($customField->id))->first())
                                                    <div class="info-box font-two">
                                                        <span class="art-option-name">{{ $customField->field_name }}</span>
                                                        <span class="art-option-value">{{ optional( $customField->options->whereIn('id', $product->getCustomFieldValue($customField->id))->first() )->name }}</span>
                                                    </div>
                                                @endif
                                            @endif
                                        @endif

                                @endforeach

                                @if( $product->availability_status_id != 1 )
                                    <div class="info-box font-two">
                                        <span class="art-option-name">{{ trans('base.availability') }}</span>
                                        @if ($product->availability_status_id == 2)
                                            <span class="art-option-value check-square">
                                                <i class="fa fa-check-square-o"></i>
                                                {{ \App\DataClasses\ProductStatusDataClass::get($product->availability_status_id)['name'] }}
                                            </span>
                                        @elseif($product->availability_status_id == 3)
                                            <span class="art-option-value">
                                                {{ \App\DataClasses\ProductStatusDataClass::get($product->availability_status_id)['name'] }}
                                            </span>
                                        @elseif($product->availability_status_id == 4)
                                            <span class="art-option-value">
                                                <i class="fa fa-truck"></i>
                                                {{ \App\DataClasses\ProductStatusDataClass::get($product->availability_status_id)['name'] }}
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                @foreach($attributeOptions as $id => $allOptions)
                                    @foreach($allOptions as $name => $option)
                                        @if(count($option))
                                            <div class="info-box font-two">
                                                <span class="art-option-name">{{ $name }}</span>
                                                <select name="option" id="option-id-{{ $id }}" class="art-select-attribute">
                                                    <option value="">- Обрати -</option>

                                                    @foreach($option as $item)
                                                        <option value="{{ $item['name'] }}" data-price="{{$item['price']}}">
                                                            {{ $item['name'] }}
                                                            @if($item['price'])
                                                                {{ ' ' . $item['price'] .' '. $baseCurrency->name_short }}
                                                            @endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif
                                    @endforeach
                                @endforeach

                                <!-- === info-box === -->
                                @if(count($product->colors))
                                    <div class="info-box font-two">
                                        <span class="art-option-name">{{ trans('base.color') }}</span>
                                        <div class="art-colors-list">
                                            @foreach($product->colors as $color_item)
                                                @if($color_item->display_as_image)
                                                    <span class="color-btn" data-color-id="{{ $color_item->id }}" data-name="{{ $color_item->name }}" data-price="{{ ( !is_null($color_item->pivot->price) ) ? $color_item->pivot->price : 0 }}">
                                                        <img src="{{$color_item->image_url}}" alt="ColorImg">
                                                    </span>
                                                @else
                                                    <span class="color-btn{{ $color_item->hex == '#fff' ? ' art-white' : '' }}" data-color-id="{{ $color_item->id }}" data-name="{{ $color_item->name }}" data-price="{{ ( !is_null($color_item->pivot->price) ) ? $color_item->pivot->price : 0 }}" style="background-color: {{ $color_item->hex }};"></span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @foreach($categoryProducts as $cat => $subProducts)

                                    <div class="sub-product-wrapper">

                                        <div class="info-box font-two art-popup-link">
                                            <span class="art-option-name">{{ $cat }}</span>
                                            <span class="art-dialog-link" data-fancybox data-src="#dialog-content-{{ Illuminate\Support\Str::slug($cat) }}">{{ trans('base.select') }}</span>
                                        </div>

                                        {{-- SubProducts --}}
                                        <div class="added-sub-products" data-wrapper="dialog-content-{{ Illuminate\Support\Str::slug($cat) }}"></div>

                                        <div id="dialog-content-{{ Illuminate\Support\Str::slug($cat) }}" class="art-popup-single-product">
                                            <span class="art-category-title">{{ $cat }}</span>
                                            <div class="art-popup-list-sub-products">
                                                @foreach($subProducts as $subProduct)
                                                    <div class="art-product-item">
                                                        <div class="art-product-data">
                                                            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $subProduct->slug]) }}" class="art-product-link">
                                                                <div class="image">
                                                                    <img src="{{ $subProduct->preview_image_url }}" alt="SubProductImage">
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
                                                                    data-count="0"
                                                                    data-added="0"
                                                                    data-id="{{  $subProduct->id }}"
                                                                    data-slug="{{  $subProduct->slug }}"
                                                            >{{ trans('base.select') }}</button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                    </div>

                                @endforeach

                                <div class="info-box font-two">
                                    <span class="art-option-name">{{ trans('base.quantity') }}</span>
                                    <div class="" id="count-of-products-body">
                                        <div class="custom-control-number mr-2">
                                            <span class="counter minus"></span>
                                            <input type="number" class="" id="count-of-products" min="1" value="1">
                                            <span class="counter plus"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="price">
                                    <div class="price-hot-wrapper">
                                        @if($product->old_price > $product->price)
                                            <div class="art-hot-price-data">
                                                <span id="product-price" data-count="1" data-start-price="{{ $product->price }}" data-product-price="{{ $product->price }}" class="card-link-price--hot">{{ $product->price }}</span>
                                                <span class="currency">{{ $baseCurrency->name_short }}</span>
                                            </div>
                                            <span class="card-link-price--old">{{ $product->old_price }} {{ $baseCurrency->name_short }}</span>
                                        @else
                                            <span id="product-price" data-count="1" data-start-price="{{ $product->price }}" data-product-price="{{ $product->price }}">{{ $product->price }}</span>
                                            <span class="currency">{{ $baseCurrency->name_short }}</span>
                                        @endif

                                    </div>
                                    <span class="product-cost-description font-two">{{trans('base.product_cost_description')}}</span>
                                </div>

                                <div class="info-content-add d-flex align-items-center justify-content-between flex-wrap">
                                    <div class="d-flex flex-wrap align-items-center no-gutters w-100">

                                        <div class="col col-sm-auto col-lg col-xl-auto order-last order-sm-0 order-lg-last order-xl-2 mt-4 mt-sm-0 mt-lg-4 mt-xl-0">
                                            <button type="button" class="btn btn-main single-product-add-to-cart" id="{{ $product->slug }}">{{ trans('base.add_to_cart') }}</button>
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
                                    <a href="{{ route('store.cart.page') }}" class="btn btn-main">{{ trans('base.go_to_cart') }}</a>
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
                    <div class="swiper art-products-owl-items art-big-wrapper art-swiper-common">
                        <div class="swiper-wrapper">
                        @foreach($sameTypeProducts as $product)
                            <div class="swiper-slide">
                                @include('pages.store.partials.product_item', ['product' => $product, 'baseCurrency' => $baseCurrency])
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
