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
                    @php
                        $title = $localized($idea['title'] ?? []);
                        $url = trim((string) ($idea['url'] ?? ''));
                    @endphp

                    @if($url !== '')
                        <a class="bona-idea-card" href="{{ $url }}" aria-label="{{ $title }}">
                            <span class="bona-idea-card__image">
                                <img src="{{ $idea['image_url'] }}" alt="{{ $title }}" loading="lazy" decoding="async" width="480" height="560">
                            </span>
                            <h3>
                                <span>{{ $title }}</span>
                                <svg class="bona-idea-card__arrow" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M5 12h14M13 6l6 6-6 6"/>
                                </svg>
                            </h3>
                            <p>{{ $localized($idea['text'] ?? []) }}</p>
                        </a>
                    @else
                        <article class="bona-idea-card">
                            <span class="bona-idea-card__image">
                                <img src="{{ $idea['image_url'] }}" alt="{{ $title }}" loading="lazy" decoding="async" width="480" height="560">
                            </span>
                            <h3>{{ $title }}</h3>
                            <p>{{ $localized($idea['text'] ?? []) }}</p>
                        </article>
                    @endif
                @endforeach
            </div>
        </div>
    </section>
@endif
