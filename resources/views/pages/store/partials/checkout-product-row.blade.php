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

<a @class(['bona-checkout-item', 'is-bundle-item' => $isBundleItem]) href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $product->slug]) }}">
    <span class="bona-checkout-item__image"><img src="{{ $imageUrl }}" alt="{{ $product->name }}"></span>
    <span class="bona-checkout-item__body">
        @if($bundleCategory)<em>{{ $bundleCategory }}</em>@endif
        <b>{{ $product->name }}</b>
        @include('pages.store.partials.product-configuration', ['product' => $product, 'class' => 'bona-checkout-item__config'])
        <small>{{ $product->pivot->count }} × {{ $formatPrice($unitPrice) }}</small>
    </span>
    <strong>{{ $formatPrice($unitPrice * $product->pivot->count) }}</strong>
</a>
