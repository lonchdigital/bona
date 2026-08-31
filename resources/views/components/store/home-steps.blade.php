@props(['section' => []])

@php
    $localized = static function ($value) {
        if (! is_array($value)) {
            return trim((string) $value);
        }

        return trim((string) ($value[app()->getLocale()] ?? collect($value)->first(fn ($text) => filled($text)) ?? ''));
    };
    $steps = collect($section['items'] ?? [])->sortBy('sort_order')->values();
    $ctaUrl = trim((string) ($section['cta_url'] ?? ''));
@endphp

@if(($section['enabled'] ?? true) && $steps->isNotEmpty())
    <section class="bona-steps" aria-labelledby="home-steps-title">
        <div class="bona-shell">
            <header class="bona-section-heading">
                <p class="bona-kicker">{{ $localized($section['kicker'] ?? []) }}</p>
                <h2 id="home-steps-title">{{ $localized($section['title'] ?? []) }}</h2>
            </header>
            <div class="bona-steps__grid">
                @foreach($steps as $step)
                    <article class="bona-step-card">
                        <span class="bona-step-card__number" aria-hidden="true">{{ $step['number'] ?? '' }}</span>
                        <h3>{{ $localized($step['title'] ?? []) }}</h3>
                        <p>{{ $localized($step['text'] ?? []) }}</p>
                    </article>
                @endforeach
            </div>
            @if($localized($section['cta_label'] ?? []) && $ctaUrl)
                <div class="bona-steps__action">
                    <a
                        class="bona-button bona-button--dark"
                        href="{{ $ctaUrl }}"
                        @if(str_starts_with($ctaUrl, '#dialog-')) data-fancybox data-src="{{ $ctaUrl }}" @endif
                    >
                        {{ $localized($section['cta_label']) }}
                    </a>
                </div>
            @endif
        </div>
    </section>
@endif
