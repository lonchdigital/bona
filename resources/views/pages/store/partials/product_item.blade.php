<div class="art-product-item">
    <div class="art-product-data">
        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $product->slug]) }}" class="">
            <div class="image">
                <img src="{{ $product->preview_image_url }}" alt="Product Image" loading="lazy">
            </div>
            <div class="text">
                <div class="product-title">{{ $product->name }}</div>
                <div class="price-wrapper">
                    <div class="data-left">
                        @if($product->old_price > $product->price)
                            <span class="card-link-price--hot">{{ $product->price.' '.$baseCurrency->name_short }}</span>
                            <span class="card-link-price--old">{{ $product->old_price.' '.$baseCurrency->name_short }}</span>
                        @else
                            <span class="price">{{ $product->price }}</span>
                            <span class="currency">{{ $baseCurrency->name_short }}</span>
                        @endif
                    </div>
                    <div class="data-right">
                        <span class="cart-icon-block">
                            <i class="icon icon-cart"></i>
                        </span>

                    </div>

                </div>
                <span>{{ \App\DataClasses\ProductStatusDataClass::get($product->availability_status_id)['name'] }}</span>
            </div>
        </a>
    </div>
</div>
