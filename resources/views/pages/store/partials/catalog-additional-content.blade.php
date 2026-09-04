@if(isset($faqs) && count($faqs))
    @php
        $categoryFaqSchema = [
            '@'.'context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($faqs)->map(fn ($faq) => [
                '@type' => 'Question',
                'name' => (string) $faq->question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => (string) $faq->answer,
                ],
            ])->values()->all(),
        ];
    @endphp

    <script type="application/ld+json">{!! json_encode($categoryFaqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>

    <section class="bona-catalog-faq" aria-labelledby="catalog-faq-title">
        <div class="bona-shell">
            <header class="bona-section-heading">
                <p class="bona-kicker">{{ trans('base.catalog_faq_kicker') }}</p>
                <h2 id="catalog-faq-title">{{ trans('base.faqs') }}</h2>
            </header>
            <div class="accordion-faqs">
                @foreach($faqs as $faq)
                    <div class="accordion-item-wrapper">
                        <button class="accordion" type="button">
                            <span class="question">{{ $faq->question }}</span>
                        </button>
                        <div class="art-panel"><div class="panel-data">{{ $faq->answer }}</div></div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

@if(isset($seoText) && ! is_null($seoText) && (filled($seoText['title'] ?? null) || filled($seoText['content'] ?? null)))
    <section class="bona-catalog-seo" aria-labelledby="catalog-seo-title">
        <div class="bona-shell">
            <header>
                <div>
                    <p class="bona-kicker">{{ trans('base.catalog_seo_kicker') }}</p>
                    @if(filled($seoText['title'] ?? null))
                        <h2 id="catalog-seo-title">{{ $seoText['title'] }}</h2>
                    @endif
                </div>
            </header>
            @if(filled($seoText['content'] ?? null))
                <div class="bona-catalog-seo__content">{!! $seoText['content'] !!}</div>
            @endif
        </div>
    </section>
@endif
