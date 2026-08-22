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

        @php
            /*
             * Written out as a JSON string, this block fed Google the shop's
             * own labels: "грн." where an ISO currency code belongs and
             * "В наявності" where a schema.org URL belongs. Both are rejected,
             * which is why the catalogue earns no rich results. Built from an
             * array, the values can only be the ones schema.org accepts.
             */
            $availabilityMap = [
                \App\DataClasses\ProductStatusDataClass::PRODUCT_STATUS_STOCK => 'https://schema.org/InStock',
                \App\DataClasses\ProductStatusDataClass::PRODUCT_STATUS_ORDER => 'https://schema.org/BackOrder',
                \App\DataClasses\ProductStatusDataClass::PRODUCT_STATUS_OUT_OF_STOCK => 'https://schema.org/OutOfStock',
                \App\DataClasses\ProductStatusDataClass::PRODUCT_STATUS_OUT_ASK_MANAGER => 'https://schema.org/LimitedAvailability',
            ];

            $productUrl = url()->current();
            /*
             * Built from the product's own pictures. main_image_url falls back
             * to the category image for sub products, which would describe the
             * item with a picture of something else, so the stored path is read
             * directly. Google takes several images gladly, and 346 products
             * here have a gallery.
             */
            $productImages = collect([$product->main_image_path])
                ->merge($productGallery->pluck('image_path'))
                ->filter()
                ->map(fn ($path) => url(\Illuminate\Support\Facades\Storage::url($path)))
                ->unique()
                ->values()
                ->all();

            $productDescription = trim(preg_replace('/\s+/u', ' ', html_entity_decode(
                strip_tags((string) ($productText['content'] ?? '')),
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            )));

            $productSchema = array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                '@id' => $productUrl . '#product',
                'name' => (string) $product->name,
                'url' => $productUrl,
                'sku' => $product->sku ?: null,
                'image' => $productImages ?: null,
                'description' => $productDescription !== '' ? \Illuminate\Support\Str::limit($productDescription, 900) : null,
                'brand' => $product->brand ? ['@type' => 'Brand', 'name' => (string) $product->brand->name] : null,
                /*
                 * Present only when a real, approved review exists. An empty
                 * or invented rating is exactly what earns a manual penalty,
                 * and it would take the rest of the site's markup with it.
                 */
                'aggregateRating' => $productRatingSummary ? [
                    '@type' => 'AggregateRating',
                    'ratingValue' => $productRatingSummary['average'],
                    'reviewCount' => $productRatingSummary['count'],
                    'bestRating' => $productRatingSummary['best'],
                    'worstRating' => $productRatingSummary['worst'],
                ] : null,
                'review' => $productReviews->take(10)->map(fn ($item) => [
                    '@type' => 'Review',
                    'author' => ['@type' => 'Person', 'name' => $item->author_name],
                    'datePublished' => optional($item->publishedDate())->toDateString(),
                    'reviewRating' => [
                        '@type' => 'Rating',
                        'ratingValue' => $item->rating,
                        'bestRating' => 5,
                        'worstRating' => 1,
                    ],
                    'reviewBody' => $item->review,
                ])->values()->all() ?: null,
                'offers' => $product->price ? array_filter([
                    '@type' => 'Offer',
                    'url' => $productUrl,
                    'price' => (string) $product->price,
                    'priceCurrency' => $baseCurrency->code ?: 'UAH',
                    'availability' => $availabilityMap[$product->availability_status_id] ?? null,
                    'itemCondition' => 'https://schema.org/NewCondition',
                    'seller' => ['@id' => app(\App\Services\Seo\OrganizationSchemaService::class)->organizationId()],
                ]) : null,
            ]);

            $schemaFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG;
        @endphp

        <script type="application/ld+json">{!! json_encode($productSchema, $schemaFlags) !!}</script>

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
                                        @elseif($product->availability_status_id == 5)
                                            <span class="art-option-value">
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
                                                        <option value="{{ json_encode(['id' => $item['id'], 'name' => $item->getRawOriginal('name')]) }}" data-price="{{$item['price']}}">
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
                                                    <span class="color-btn" data-color-id="{{ $color_item->id }}" data-name="{{ json_encode(['id' => $color_item->id,'name' => $color_item->getRawOriginal('name')]) }}" data-price="{{ ( !is_null($color_item->pivot->price) ) ? $color_item->pivot->price : 0 }}">
                                                        <img src="{{$color_item->image_url}}" alt="ColorImg">
                                                    </span>
                                                @else
                                                    <span class="color-btn{{ $color_item->hex == '#fff' ? ' art-white' : '' }}" data-color-id="{{ $color_item->id }}" data-name="{{ json_encode(['id' => $color_item->id,'name' => $color_item->getRawOriginal('name')]) }}" data-price="{{ ( !is_null($color_item->pivot->price) ) ? $color_item->pivot->price : 0 }}" style="background-color: {{ $color_item->hex }};"></span>
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

                                @if($product->availability_status_id != 4)
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
                                @endif

                                @if(!empty($product->price))
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
                                @endif

                                @if(empty($product->price))
                                    {{-- <button class="btn calc-btn" data-fancybox data-src="#order-count" style="max-width: 170px;">{{ trans('base.order_count') }}</button> --}}
                                    <a href="" class="btn btn-main art-header-coll-button" data-fancybox data-src="#order-count">{{ trans('base.order_count') }}</a>
                                @else
                                    @if( in_array($product->availability_status_id, [3, 4]) )
                                        <a href="" class="btn btn-main art-header-coll-button" data-fancybox data-src="#order-count">{{ trans('base.leave_request') }}</a>

                                        <div id="order-count" class="art-popup-call-measurer">
                                            <div class="art-measurer-form-wrapper">
                                                <div class="container">

                                                    <div class="row">
                                                        <div class="col-12 text-center">
                                                            <form action="#" id="order-count-form" method="post" class="art-contact-form">
                                                                @csrf

                                                                <header class="art-light">
                                                                    <div class="text-center">
                                                                        <h2 class="title h2">{{ trans('base.leave_request') }}</h2>
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
                                    @else
                                        <div class="info-content-add d-flex align-items-center justify-content-between flex-wrap">
                                            <div class="d-flex flex-wrap align-items-center no-gutters w-100">
                                                <div class="col col-sm-auto col-lg col-xl-auto order-last order-sm-0 order-lg-last order-xl-2 mt-4 mt-sm-0 mt-lg-4 mt-xl-0">
                                                    <button type="button" class="btn btn-main single-product-add-to-cart" id="{{ $product->slug }}">
                                                        {{ trans('base.add_to_cart') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
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
                        @include('pages.store.partials.order-count')
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

    <section class="art-section-pd art-product-reviews" id="product-reviews">
        <div class="container">

            <div class="row">
                <div class="col-12">
                    <h2 class="title h2">{{ trans('base.product_reviews_title') }}</h2>

                    @if($productRatingSummary)
                        <div class="art-product-reviews__summary">
                            <span class="art-product-reviews__average">{{ $productRatingSummary['average'] }}</span>
                            <span class="art-product-reviews__of">/ 5</span>
                            <span class="art-product-reviews__count">
                                {{ trans('base.product_review_based_on', ['COUNT' => $productRatingSummary['count']]) }}
                            </span>
                        </div>
                    @endif

                    @if(Session::has('review_success'))
                        <div class="art-product-reviews__notice art-product-reviews__notice--ok">{{ Session::get('review_success') }}</div>
                    @endif
                    @if(Session::has('review_error'))
                        <div class="art-product-reviews__notice art-product-reviews__notice--fail">{{ Session::get('review_error') }}</div>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-lg-7">
                    @if($productReviews->isEmpty())
                        <p class="art-product-reviews__empty">{{ trans('base.product_reviews_empty') }}</p>
                    @else
                        <ul class="art-product-reviews__list">
                            @foreach($productReviews as $review)
                                <li class="art-product-reviews__item">
                                    <div class="art-product-reviews__head">
                                        <span class="art-product-reviews__author">{{ $review->author_name }}</span>
                                        <span class="art-product-reviews__rating">{{ $review->rating }}/5</span>
                                        <time datetime="{{ optional($review->publishedDate())->toDateString() }}">
                                            {{ optional($review->publishedDate())->translatedFormat('d F Y') }}
                                        </time>
                                    </div>
                                    <p class="art-product-reviews__text">{{ $review->review }}</p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="col-lg-5">
                    <form action="{{ route('store.product-review.submit') }}" method="POST" class="art-product-reviews__form">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                        <p class="art-product-reviews__form-title">{{ trans('base.product_review_leave') }}</p>
                        <p class="art-product-reviews__hint">{{ trans('base.product_review_about_hint') }}</p>

                        <div class="form-group">
                            <label for="review-rating">{{ trans('base.product_review_rating') }}</label>
                            <select id="review-rating" name="rating" class="art-light-field" required>
                                @foreach([5, 4, 3, 2, 1] as $ratingOption)
                                    <option value="{{ $ratingOption }}" @selected(old('rating', 5) == $ratingOption)>{{ $ratingOption }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="review-name">{{ trans('base.name') }}</label>
                            <input id="review-name" type="text" name="author_name" class="art-light-field" value="{{ old('author_name') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="review-email">{{ trans('base.email') }}</label>
                            <input id="review-email" type="email" name="author_email" class="art-light-field" value="{{ old('author_email') }}">
                        </div>

                        <div class="form-group">
                            <label for="review-text">{{ trans('base.product_review_text') }}</label>
                            <textarea id="review-text" name="review" class="art-light-field" rows="5" required>{{ old('review') }}</textarea>
                        </div>

                        {{-- Honeypot: hidden from people, irresistible to bots. --}}
                        <div class="art-product-reviews__trap" aria-hidden="true">
                            <label for="review-website">Website</label>
                            <input id="review-website" type="text" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        @if($errors->any())
                            <ul class="art-product-reviews__errors">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif

                        <button type="submit" class="btn btn-main">{{ trans('base.send') }}</button>
                    </form>
                </div>
            </div>

        </div>
    </section>

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

@push('head')
    <style>
        .art-product-reviews__summary { display: flex; align-items: baseline; margin-bottom: 20px; }
        .art-product-reviews__average { font-size: 34px; font-weight: 500; line-height: 1; }
        .art-product-reviews__of { margin: 0 10px 0 4px; color: #777777; }
        .art-product-reviews__count { font-size: 14px; font-weight: 300; color: #777777; }

        .art-product-reviews__notice { padding: 12px 16px; margin-bottom: 20px; font-size: 14px; }
        .art-product-reviews__notice--ok { background-color: #eef7ee; border-left: 3px solid #4b9b52; }
        .art-product-reviews__notice--fail { background-color: #fbeeee; border-left: 3px solid #c05c5c; }

        .art-product-reviews__list { list-style: none; margin: 0; padding: 0; }
        .art-product-reviews__item { padding: 18px 0; border-bottom: 1px solid #dddddd; }
        .art-product-reviews__head { display: flex; flex-wrap: wrap; align-items: baseline; margin-bottom: 8px; font-size: 14px; }
        .art-product-reviews__author { font-weight: 500; margin-right: 12px; }
        .art-product-reviews__rating { margin-right: 12px; font-weight: 500; }
        .art-product-reviews__head time { color: #777777; font-weight: 300; }
        .art-product-reviews__text { margin: 0; font-weight: 300; }
        .art-product-reviews__empty { font-weight: 300; color: #777777; }

        .art-product-reviews__form { padding: 24px; background-color: #f5f5f5; }
        .art-product-reviews__form-title { font-size: 18px; font-weight: 500; margin-bottom: 6px; }
        .art-product-reviews__hint { font-size: 13px; font-weight: 300; color: #777777; margin-bottom: 18px; }
        .art-product-reviews__form .form-group { margin-bottom: 14px; }
        .art-product-reviews__form label { display: block; font-size: 13px; margin-bottom: 4px; }
        .art-product-reviews__form .art-light-field { width: 100%; }
        .art-product-reviews__errors { list-style: none; margin: 0 0 14px; padding: 0; font-size: 13px; color: #c05c5c; }

        /* Kept out of sight and out of the tab order: only a bot fills it in. */
        .art-product-reviews__trap { position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden; }

        @media (max-width: 991px) {
            .art-product-reviews__form { margin-top: 25px; }
        }
    </style>
@endpush
