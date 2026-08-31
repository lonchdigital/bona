@props(['articles', 'section' => []])

@php
    $localized = static function ($value) {
        if (! is_array($value)) {
            return trim((string) $value);
        }

        return trim((string) ($value[app()->getLocale()] ?? collect($value)->first(fn ($text) => filled($text)) ?? ''));
    };
    $blogUrl = trim((string) ($section['link_url'] ?? ''))
        ?: App\Helpers\MultiLangRoute::getMultiLangRoute('blog.main.page');
@endphp

@if(($section['enabled'] ?? true) && count($articles) > 0)
    <section class="bona-blog" aria-labelledby="home-blog-title">
        <div class="bona-shell">
            <header class="bona-section-heading bona-section-heading--split">
                <div>
                    <p class="bona-kicker">{{ $localized($section['kicker'] ?? []) }}</p>
                    <h2 id="home-blog-title">{{ $localized($section['title'] ?? []) }}</h2>
                </div>
                @if($localized($section['link_label'] ?? []))
                    <a class="bona-text-link" href="{{ $blogUrl }}">
                        {{ $localized($section['link_label']) }} <span aria-hidden="true">&#8594;</span>
                    </a>
                @endif
            </header>

            <div class="bona-blog__grid">
                @foreach($articles as $article)
                    <article class="bona-post-card">
                        <a class="bona-post-card__image" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('blog.article.page', ['blogArticleSlug' => $article->slug]) }}">
                            @if($article->hero_image_url)
                                <img src="{{ $article->hero_image_url }}" alt="{{ $article->name }}" loading="lazy" decoding="async">
                            @endif
                            <time class="bona-post-card__date" datetime="{{ $article->created_at->toDateString() }}">
                                {{ $article->created_at->translatedFormat('d M Y') }}
                            </time>
                        </a>
                        <div class="bona-post-card__body">
                            <h3>
                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('blog.article.page', ['blogArticleSlug' => $article->slug]) }}">
                                    {{ $article->name }}
                                </a>
                            </h3>
                            @if(filled($article->preview_text))
                                <p>{{ \Illuminate\Support\Str::limit(strip_tags($article->preview_text), 170) }}</p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
