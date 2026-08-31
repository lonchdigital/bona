@props(['faqs', 'section' => []])

@php
    $localized = static function ($value) {
        if (! is_array($value)) {
            return trim((string) $value);
        }

        return trim((string) ($value[app()->getLocale()] ?? collect($value)->first(fn ($text) => filled($text)) ?? ''));
    };
@endphp

@if(($section['enabled'] ?? true) && count($faqs) > 0)
    @php
        $homeFaqSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $faqs->map(fn ($faq) => [
                '@type' => 'Question',
                'name' => (string) $faq->question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => (string) $faq->answer,
                ],
            ])->values()->all(),
        ];
    @endphp

    <script type="application/ld+json">{!! json_encode($homeFaqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>

    <section class="bona-faq-section" aria-labelledby="home-faq-title">
        <div class="bona-shell">
            <header class="bona-section-heading">
                <p class="bona-kicker">{{ $localized($section['kicker'] ?? []) }}</p>
                <h2 id="home-faq-title">{{ $localized($section['title'] ?? []) }}</h2>
            </header>

            <div class="bona-faq">
                @foreach($faqs as $faq)
                    <details class="bona-faq__item" @if($loop->first) open @endif>
                        <summary>
                            <span>{{ $faq->question }}</span>
                            <span class="bona-faq__icon" aria-hidden="true">+</span>
                        </summary>
                        <div class="bona-faq__answer">{!! nl2br(e($faq->answer)) !!}</div>
                    </details>
                @endforeach
            </div>
        </div>
    </section>
@endif
