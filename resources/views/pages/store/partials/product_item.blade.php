<div class="art-product-item">
    <div class="art-product-data">
        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $product->slug]) }}" class="">
            <div class="image">
                <img src="{{ $product->preview_image_url }}" alt="Product Image" loading="lazy">
            </div>
            <div class="text">
{{--                <div class="product-title">{{ Illuminate\Support\Str::limit($product->name, 55) }}</div>--}}
                <div class="product-title">{{ $product->name }}</div>
                <div class="price-wrapper">
                    @if($product->old_price > $product->price)
                        <span class="card-link-price--hot">{{ $product->price.' '.$baseCurrency->name_short }}</span>
                        <span class="card-link-price--old">{{ $product->old_price.' '.$baseCurrency->name_short }}</span>
                    @else
                        <span class="price">{{ $product->price }}</span>
                        <span class="currency">{{ $baseCurrency->name_short }}</span>
                    @endif
                </div>
            </div>
        </a>
    </div>
</div>
