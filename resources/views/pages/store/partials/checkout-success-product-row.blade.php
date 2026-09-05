@php
    $unitPrice = (float) $product->pivot->price + (float) ($product->pivot->attributes_price ?? 0);
    $imageUrl = $product->pivot->current_image_path
        ? '/storage/'.$product->pivot->current_image_path
        : ($product->main_image_url ?: $product->preview_image_url);
    $isBundleItem = $isBundleItem ?? false;
    $bundleCategory = $isBundleItem
        ? \App\Support\Commerce\ProductBundle::localizedCategory($product->pivot->bundle_category)
        : '';
@endphp

<a @class(['bona-checkout-success__item', 'is-bundle-item' => $isBundleItem]) href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $product->slug]) }}">
    <span><img src="{{ $imageUrl }}" alt="{{ $product->name }}"></span>
    <div>
        @if($bundleCategory)<em>{{ $bundleCategory }}</em>@endif
        <strong>{{ $product->name }}</strong>
        @include('pages.store.partials.product-configuration', ['product' => $product, 'class' => 'bona-checkout-success__config'])
        <small>{{ $product->pivot->count }} × {{ $formatPrice($unitPrice) }}</small>
    </div>
    <b>{{ $formatPrice($unitPrice * $product->pivot->count) }}</b>
</a>
