@props([
    'products' => collect(),
    'baseCurrency' => null,
    'section' => [],
])

@php
    $localized = static function ($value) {
        if (! is_array($value)) {
            return trim((string) $value);
        }

        return trim((string) ($value[app()->getLocale()] ?? collect($value)->first(fn ($text) => filled($text)) ?? ''));
    };
    $title = $localized($section['title'] ?? []) ?: trans('base.home_popular_title');
    $linkUrl = trim((string) ($section['link_url'] ?? ''))
        ?: App\Helpers\MultiLangRoute::getMultiLangRoute('store.all-products.page');
@endphp

@if(($section['enabled'] ?? true) && $products->isNotEmpty())
    <section class="bona-popular" aria-labelledby="home-popular-title">
        <div class="bona-shell">
            <header class="bona-section-heading bona-section-heading--split">
                <div>
                    <p class="bona-kicker">{{ $localized($section['kicker'] ?? []) }}</p>
                    <h2 id="home-popular-title">{{ $title }}</h2>
                </div>
                @if($localized($section['link_label'] ?? []))
                    <a class="bona-text-link" href="{{ $linkUrl }}">
                        {{ $localized($section['link_label']) }} <span aria-hidden="true">→</span>
                    </a>
                @endif
            </header>

            <div class="swiper bona-popular__slider" data-popular-slider aria-label="{{ $title }}">
                <div class="swiper-wrapper">
                    @foreach($products as $product)
                        @php
                            $productUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $product->slug]);
                            $colors = $product->relationLoaded('colors') ? $product->colors : collect();
                        @endphp
                        <article class="swiper-slide bona-product-card">
                            <div class="bona-product-card__media">
                                <a href="{{ $productUrl }}" tabindex="-1" aria-hidden="true">
                                    <img src="{{ $product->main_image_url }}" alt="" loading="lazy">
                                </a>

                                @if($product->availability_status_id === \App\DataClasses\ProductStatusDataClass::PRODUCT_STATUS_STOCK)
                                    <span class="bona-product-card__badge">{{ trans('shop.product_status_stock') }}</span>
                                @endif

                                <button
                                    class="bona-product-card__wish link-heart"
                                    id="{{ $product->slug }}"
                                    type="button"
                                    aria-label="{{ trans('base.add_to_wish_list') }}"
                                >
                                    <x-wish-heart />
                                </button>
                            </div>

                            @if($colors->isNotEmpty())
                                <div class="bona-product-card__swatches" aria-label="{{ trans('base.home_available_colors') }}">
                                    @foreach($colors->take(4) as $color)
                                        <span
                                            class="bona-product-card__swatch"
                                            style="--bona-swatch: {{ $color->hex ?: '#d7d1c5' }}"
                                            title="{{ $color->name }}"
                                        ></span>
                                    @endforeach
                                    @if($colors->count() > 4)
                                        <span class="bona-product-card__more">+{{ $colors->count() - 4 }}</span>
                                    @endif
                                </div>
                            @endif

                            <div class="bona-product-card__info">
                                <p class="bona-product-card__meta">
                                    {{ collect([$product->brand?->name, $product->productType?->name])->filter()->join(' / ') }}
                                </p>
                                <h3><a href="{{ $productUrl }}">{{ $product->name }}</a></h3>
                            </div>

                            <div class="bona-product-card__footer">
                                <span class="bona-product-card__price">
                                    {{ number_format((float) $product->price, 0, ',', ' ') }}
                                    {{ $baseCurrency?->name_short }}
                                </span>
                                <a class="bona-product-card__open" href="{{ $productUrl }}" aria-label="{{ trans('base.home_view_model', ['MODEL' => $product->name]) }}">
                                    <i class="icon icon-cart" aria-hidden="true"></i>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="swiper-pagination bona-popular__pagination"></div>
            </div>
        </div>
    </section>
@endif
