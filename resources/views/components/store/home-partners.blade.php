@props(['brands'])

@php
    $partnerBrands = $brands->pluck('brand')->filter();
@endphp

@if($partnerBrands->isNotEmpty())
    <section class="bona-partners" aria-labelledby="home-partners-title">
        <div class="bona-shell">
            <header class="bona-section-heading">
                <p class="bona-kicker">{{ trans('base.partners_kicker') }}</p>
                <h2 id="home-partners-title">{{ trans('base.our_partners') }}</h2>
            </header>

            <div class="bona-partners__grid">
                @foreach($partnerBrands as $brand)
                    <a
                        class="bona-partner-card"
                        href="{{ app(App\Services\Brand\BrandCatalogUrlService::class)->storefrontUrl($brand) }}"
                        aria-label="{{ $brand->name }}"
                    >
                        @if(filled($brand->logo_image_path))
                            <img src="{{ $brand->logo_image_url }}" alt="{{ $brand->name }}" loading="lazy" decoding="async">
                        @else
                            <span>{{ $brand->name }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
