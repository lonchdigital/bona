@props(['brands', 'section' => []])

@php
    $partnerBrands = $brands->pluck('brand')->filter();
    $localized = static function ($value) {
        if (! is_array($value)) {
            return trim((string) $value);
        }

        return trim((string) ($value[app()->getLocale()] ?? collect($value)->first(fn ($text) => filled($text)) ?? ''));
    };
@endphp

@if(($section['enabled'] ?? true) && $partnerBrands->isNotEmpty())
    <section class="bona-partners" aria-labelledby="home-partners-title">
        <div class="bona-shell">
            <header class="bona-section-heading">
                <p class="bona-kicker">{{ $localized($section['kicker'] ?? []) }}</p>
                <h2 id="home-partners-title">{{ $localized($section['title'] ?? []) }}</h2>
            </header>

            <div class="bona-partners__grid">
                @foreach($partnerBrands as $brand)
                    <a
                        class="bona-partner-card"
                        href="{{ app(App\Services\Brand\BrandCatalogUrlService::class)->storefrontUrl($brand) }}"
                        aria-label="{{ $brand->name }}"
                    >
                        @if(filled($brand->logo_image_path))
                            <img src="{{ $brand->logo_image_url }}" alt="{{ $brand->name }}" loading="lazy" decoding="async" width="240" height="80">
                        @else
                            <span>{{ $brand->name }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
