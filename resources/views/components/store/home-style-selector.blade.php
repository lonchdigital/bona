@props([
    'section' => [],
])

@php
    $locale = app()->getLocale();
    $localized = static function ($value) use ($locale) {
        if (!is_array($value)) {
            return trim((string) $value);
        }

        $current = trim((string) ($value[$locale] ?? ''));

        return $current !== ''
            ? $current
            : (collect($value)->first(fn ($translation) => filled($translation)) ?? '');
    };
    $items = collect($section['items'] ?? [])->filter(fn ($item) => filled($item['image_url'] ?? null));
@endphp

@if(($section['enabled'] ?? false) && $items->isNotEmpty())
    <section class="bona-style-band" data-home-style-selector aria-labelledby="home-style-title">
        <div class="bona-shell bona-style-band__grid">
            <div class="bona-style-band__content">
                @if($localized($section['kicker'] ?? []))
                    <p class="bona-kicker">{{ $localized($section['kicker']) }}</p>
                @endif
                @if($localized($section['title'] ?? []))
                    <h2 id="home-style-title">{{ $localized($section['title']) }}</h2>
                @endif
                @if($localized($section['description'] ?? []))
                    <p class="bona-style-band__text">{{ $localized($section['description']) }}</p>
                @endif

                <div class="bona-style-list" role="tablist" aria-label="{{ $localized($section['kicker'] ?? []) }}">
                    @foreach($items as $item)
                        <button
                            class="bona-style-list__item{{ $loop->first ? ' is-active' : '' }}"
                            id="home-style-tab-{{ $loop->index }}"
                            type="button"
                            role="tab"
                            aria-controls="home-style-pane-{{ $loop->index }}"
                            aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                            tabindex="{{ $loop->first ? '0' : '-1' }}"
                            data-style-tab="{{ $loop->index }}"
                        >
                            {{ $localized($item['name'] ?? []) }}
                            <span aria-hidden="true">→</span>
                        </button>
                    @endforeach
                </div>

                @if($localized($section['cta_label'] ?? []) && filled($section['cta_url'] ?? null))
                    <a class="bona-style-band__cta" href="{{ $section['cta_url'] }}">
                        {{ $localized($section['cta_label']) }} <span aria-hidden="true">→</span>
                    </a>
                @endif
            </div>

            <div class="bona-style-band__view">
                @foreach($items as $item)
                    <div
                        class="bona-style-pane{{ $loop->first ? ' is-active' : '' }}"
                        id="home-style-pane-{{ $loop->index }}"
                        role="tabpanel"
                        aria-labelledby="home-style-tab-{{ $loop->index }}"
                        data-style-pane="{{ $loop->index }}"
                        @if(!$loop->first) hidden @endif
                    >
                        <img src="{{ $item['image_url'] }}" alt="{{ $localized($item['name'] ?? []) }}" loading="lazy">
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
