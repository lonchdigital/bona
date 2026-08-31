@props(['section' => []])

@php
    $localized = static function ($value) {
        if (! is_array($value)) {
            return trim((string) $value);
        }

        return trim((string) ($value[app()->getLocale()] ?? collect($value)->first(fn ($text) => filled($text)) ?? ''));
    };
    $ideas = collect($section['items'] ?? [])->filter(fn ($item) => filled($item['image_url'] ?? null))->sortBy('sort_order')->values();
@endphp

@if(($section['enabled'] ?? true) && $ideas->isNotEmpty())
    <section class="bona-ideas" aria-labelledby="home-ideas-title">
        <div class="bona-shell">
            <header class="bona-section-heading">
                <p class="bona-kicker">{{ $localized($section['kicker'] ?? []) }}</p>
                <h2 id="home-ideas-title">{{ $localized($section['title'] ?? []) }}</h2>
            </header>
            <div class="bona-ideas__grid">
                @foreach($ideas as $idea)
                    <article class="bona-idea-card">
                        <span class="bona-idea-card__image">
                            <img src="{{ $idea['image_url'] }}" alt="{{ $localized($idea['title'] ?? []) }}" loading="lazy" decoding="async">
                        </span>
                        <h3>{{ $localized($idea['title'] ?? []) }}</h3>
                        <p>{{ $localized($idea['text'] ?? []) }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
