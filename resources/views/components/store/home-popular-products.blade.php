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
        ?: App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', [
            'productTypeSlug' => 'interior-doors',
        ]);
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
                        <x-store.product-card
                            class="swiper-slide"
                            :product="$product"
                            :base-currency="$baseCurrency"
                            variant="slider"
                        />
                    @endforeach
                </div>
                <div class="swiper-pagination bona-popular__pagination"></div>
            </div>
        </div>
    </section>
@endif
