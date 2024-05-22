<div class="art-product-item art-product-minimal">
    <div class="art-product-data">
        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $product->slug]) }}" class="">
            <div class="image">
                <img src="{{ $product->preview_image_url }}" alt="Product Image" loading="lazy">
            </div>
            <div class="text">
                <span class="product-type-name">{{ $productTypeName }}</span>
                <span class="product-title font-title">{{ $product->name }}</span>
            </div>
        </a>
    </div>
</div>
