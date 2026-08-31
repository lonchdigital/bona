@props(['section' => []])

@php
    $localized = static function ($value) {
        if (! is_array($value)) {
            return trim((string) $value);
        }

        return trim((string) ($value[app()->getLocale()] ?? collect($value)->first(fn ($text) => filled($text)) ?? ''));
    };
    $works = collect($section['items'] ?? [])->filter(fn ($item) => filled($item['image_url'] ?? null))->sortBy('sort_order')->values();
    $sectionUrl = trim((string) ($section['link_url'] ?? ''))
        ?: App\Helpers\MultiLangRoute::getMultiLangRoute('store.works.page');
@endphp

@if(($section['enabled'] ?? true) && $works->isNotEmpty())
<section class="bona-works" aria-labelledby="home-works-title">
    <div class="bona-shell">
        <header class="bona-section-heading bona-section-heading--split">
            <div>
                <p class="bona-kicker">{{ $localized($section['kicker'] ?? []) }}</p>
                <h2 id="home-works-title">{{ $localized($section['title'] ?? []) }}</h2>
            </div>
            @if($localized($section['link_label'] ?? []))
                <a class="bona-text-link" href="{{ $sectionUrl }}">
                    {{ $localized($section['link_label']) }} <span aria-hidden="true">→</span>
                </a>
            @endif
        </header>

        <div class="bona-works__grid">
            @foreach($works as $work)
                @php
                    $workUrl = trim((string) ($work['url'] ?? ''));
                    $workTitle = $localized($work['title'] ?? []);
                @endphp
                <article class="bona-work-card">
                    @if($workUrl)
                        <a class="bona-work-card__image" href="{{ $workUrl }}">
                            <img src="{{ $work['image_url'] }}" alt="{{ $workTitle }}" loading="lazy" decoding="async">
                        </a>
                    @else
                        <span class="bona-work-card__image">
                            <img src="{{ $work['image_url'] }}" alt="{{ $workTitle }}" loading="lazy" decoding="async">
                        </span>
                    @endif
                    <h3>
                        @if($workUrl)<a href="{{ $workUrl }}">@endif
                            {{ $workTitle }}
                        @if($workUrl)</a>@endif
                    </h3>
                    <p>{{ $localized($work['text'] ?? []) }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
