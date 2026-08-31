@props(['section' => []])

@php
    $localized = static function ($value) {
        if (! is_array($value)) {
            return trim((string) $value);
        }

        return trim((string) ($value[app()->getLocale()] ?? collect($value)->first(fn ($text) => filled($text)) ?? ''));
    };
    $items = collect($section['items'] ?? [])->sortBy('sort_order')->values();
@endphp

@if(($section['enabled'] ?? true) && $items->isNotEmpty())
    <section class="bona-numbers" aria-labelledby="home-numbers-title">
        <div class="bona-shell">
            <div class="bona-numbers__card">
                <div class="bona-numbers__intro">
                    <p class="bona-kicker">{{ $localized($section['kicker'] ?? []) }}</p>
                    <h2 id="home-numbers-title">{{ $localized($section['title'] ?? []) }}</h2>
                </div>
                @foreach($items as $item)
                    <div class="bona-numbers__item">
                        <strong>{{ $item['value'] ?? '' }}</strong>
                        <span>{{ $localized($item['label'] ?? []) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
