<div class="art-product-item">
    <div class="art-product-data">
        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $product->slug]) }}" class="">
            <div class="image">
                <img src="{{ $product->preview_image_url }}" alt="">
            </div>
            <div class="text">
                <span class="product-title">{{ $product->name }}</span>
                <span class="price-wrapper">
                    @if($product->old_price > $product->price)
                        <span class="card-link-price--hot">{{ $product->price }} {{ $baseCurrency->name_short }} </span>
                        <span class="card-link-price--old">{{ $product->old_price }} {{ $baseCurrency->name_short }}</span>
                    @else
                        <span class="price">{{ $product->price }}</span>
                        <span class="currency">{{ $baseCurrency->name_short }}</span>
                    @endif
                </span>
            </div>
        </a>
    </div>
</div>
