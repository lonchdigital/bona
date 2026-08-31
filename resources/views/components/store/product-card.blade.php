@props([
    'product',
    'baseCurrency' => null,
    'variant' => 'catalog',
])

@php
    $productUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $product->slug]);
    $colors = $product->relationLoaded('colors')
        ? $product->colors->sortByDesc(fn ($color) => $color->id === $product->main_color_id)->values()
        : collect();
    $galleries = $product->relationLoaded('galleries') ? $product->galleries : collect();
    $visibleColors = $colors->take(4)->values();
    $activeColor = $visibleColors->firstWhere('id', $product->main_color_id) ?? $visibleColors->first();
    $defaultImage = $product->main_image_url ?: $product->preview_image_url;
    $activeAdjustment = (float) ($activeColor?->pivot?->price ?? 0);
    $basePrice = (float) ($product->price ?? 0);
    $baseOldPrice = (float) ($product->old_price ?? 0);
    $currentPrice = $basePrice + $activeAdjustment;
    $currentOldPrice = $baseOldPrice > $basePrice ? $baseOldPrice + $activeAdjustment : 0;
    $currency = $baseCurrency?->name_short ?? trans('base.uah');
    $status = App\DataClasses\ProductStatusDataClass::get($product->availability_status_id);
    $isStock = $product->availability_status_id === App\DataClasses\ProductStatusDataClass::PRODUCT_STATUS_STOCK;
    $specialOffer = collect($product->special_offers ?? [])->first();
    $specialOfferLabel = $specialOffer
        ? data_get(App\DataClasses\ProductSpecialOfferOptionsDataClass::get((int) $specialOffer), 'name')
        : null;
@endphp

<article
    {{ $attributes->class([
        'bona-product-card',
        'bona-product-card--catalog' => $variant === 'catalog',
        'bona-product-card--slider' => $variant === 'slider',
    ]) }}
    data-product-card
    data-base-price="{{ $basePrice }}"
    data-base-old-price="{{ $baseOldPrice }}"
    data-currency="{{ $currency }}"
>
    <div class="bona-product-card__media">
        <a href="{{ $productUrl }}">
            <img
                src="{{ $defaultImage }}"
                data-product-card-image
                data-default-image="{{ $defaultImage }}"
                data-default-alt="{{ $product->name }}"
                alt="{{ $product->name }}"
                loading="lazy"
            >
        </a>

        <div class="bona-product-card__badges">
            @if($currentOldPrice > $currentPrice && $currentPrice > 0)
                <span class="bona-product-card__badge">-{{ (int) round((1 - ($currentPrice / $currentOldPrice)) * 100) }}%</span>
            @elseif($specialOfferLabel)
                <span class="bona-product-card__badge">{{ $specialOfferLabel }}</span>
            @endif

            @if($status)
                <span class="bona-product-card__badge bona-product-card__badge--status {{ $isStock ? 'is-stock' : '' }}">
                    {{ $status['name'] }}
                </span>
            @endif
        </div>

        <button
            class="bona-product-card__wish link-heart"
            id="{{ $product->slug }}"
            type="button"
            aria-label="{{ trans('base.add_to_wish_list') }}"
        >
            <x-wish-heart />
        </button>
    </div>

    @if($visibleColors->isNotEmpty())
        <div class="bona-product-card__swatches" aria-label="{{ trans('base.home_available_colors') }}">
            @foreach($visibleColors as $color)
                @php
                    $colorGallery = $galleries->firstWhere('color_id', $color->id);
                    $isActive = $activeColor?->id === $color->id;
                @endphp
                <button
                    class="bona-product-card__swatch {{ $isActive ? 'is-active' : '' }}"
                    type="button"
                    style="--bona-swatch: {{ $color->hex ?: '#d7d1c5' }}; @if($color->display_as_image && $color->image_url) --bona-swatch-image: url('{{ $color->image_url }}'); @endif"
                    data-product-card-swatch
                    data-color-id="{{ $color->id }}"
                    data-color-name="{{ $color->name }}"
                    data-image="{{ $isActive ? '' : $colorGallery?->gallery_image_url }}"
                    data-price-adjustment="{{ (float) ($color->pivot?->price ?? 0) }}"
                    aria-label="{{ trans('base.catalog_select_color', ['color' => $color->name]) }}"
                    aria-pressed="{{ $isActive ? 'true' : 'false' }}"
                    title="{{ $color->name }}"
                ></button>
            @endforeach

            @if($colors->count() > $visibleColors->count())
                <a class="bona-product-card__more" href="{{ $productUrl }}" aria-label="{{ trans('base.catalog_more_colors', ['count' => $colors->count() - $visibleColors->count()]) }}">
                    +{{ $colors->count() - $visibleColors->count() }}
                </a>
            @endif

            <span class="sr-only" data-product-card-color-name aria-live="polite">{{ $activeColor?->name }}</span>
        </div>
    @endif

    <div class="bona-product-card__info">
        <p class="bona-product-card__meta">
            {{ collect([$product->brand?->name, $product->productType?->name])->filter()->join(' · ') }}
        </p>
        <h3><a href="{{ $productUrl }}">{{ $product->name }}</a></h3>
    </div>

    <div class="bona-product-card__footer">
        @if($basePrice > 0)
            <div class="bona-product-card__pricing">
                @if($currentOldPrice > $currentPrice)
                    <span class="bona-product-card__old-price" data-product-card-old-price>
                        {{ number_format($currentOldPrice, 0, ',', ' ') }} {{ $currency }}
                    </span>
                @endif
                <span class="bona-product-card__price">
                    @unless($currentOldPrice > $currentPrice)<small>{{ trans('base.catalog_price_from') }}</small>@endunless
                    <strong data-product-card-price>{{ number_format($currentPrice, 0, ',', ' ') }} {{ $currency }}</strong>
                </span>
                @if($product->productType?->product_point_name)
                    <span class="bona-product-card__unit">/ {{ $product->productType->product_point_name }}</span>
                @endif
            </div>
        @endif

        <a class="bona-product-card__open" href="{{ $productUrl }}" aria-label="{{ trans('base.home_view_model', ['MODEL' => $product->name]) }}">
            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <rect x="4" y="6.5" width="12" height="10" rx="2"></rect>
                <path d="M7 6.5v-1a3 3 0 0 1 6 0v1"></path>
            </svg>
        </a>
    </div>
</article>
