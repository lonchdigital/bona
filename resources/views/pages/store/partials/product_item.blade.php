<div class="art-product-item">
    <div class="art-product-data">
        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $product->slug]) }}" class="">
            <div class="image">
                <span class="link-heart" id="{{ $product->slug }}" aria-label="{{ trans('base.add_to_wish_list') }}">
                    <svg>
                        <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-heart-hover"></use>
                    </svg>
                </span>
                <img src="{{ $product->preview_image_url }}" alt="Product Image" loading="lazy">
            </div>
            <div class="text">
                <div class="product-title">{{ $product->name }}</div>
                <div class="price-wrapper">

                    @if(!empty($product->price))
                        @if($product->old_price > $product->price)
                            <span class="card-link-price--hot">{{ $product->price.' '.$baseCurrency->name_short }}</span>
                            <span class="card-link-price--old">{{ $product->old_price.' '.$baseCurrency->name_short }}</span>
                        @else
                            <span class="price">{{ $product->price }}</span>
                            <span class="currency">{{ $baseCurrency->name_short }}</span>
                        @endif

                        <span class="cart-icon-block">
                            <i class="icon icon-cart"></i>
                        </span>
                    @else
                        <button class="btn calc-btn" data-fancybox data-src="#order-count"
                                data-product-name="{{ $product->name }}"
                                data-product-url="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $product->slug]) }}">{{ trans('base.order_count') }}</button>
                    @endif

                </div>
                <span class="availability-status{{ ($product->availability_status_id === \App\DataClasses\ProductStatusDataClass::PRODUCT_STATUS_STOCK) ? ' art-available' : '' }}">{{ \App\DataClasses\ProductStatusDataClass::get($product->availability_status_id)['name'] }}</span>
            </div>
        </a>
    </div>
</div>
