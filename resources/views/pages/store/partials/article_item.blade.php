@php
    $articleUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('blog.article.page', ['blogArticleSlug' => $article->slug]);
@endphp

<article class="bona-editorial-card">
    <a class="bona-editorial-card__media" href="{{ $articleUrl }}">
        @if($article->hero_image_url)
            <img src="{{ $article->hero_image_url }}" alt="{{ $article->name }}" width="720" height="480" loading="lazy" decoding="async">
        @endif
        <time datetime="{{ $article->created_at->toDateString() }}">
            {{ $article->created_at->translatedFormat('d M Y') }}
        </time>
    </a>
    <div class="bona-editorial-card__body">
        <h2><a href="{{ $articleUrl }}">{{ $article->name }}</a></h2>
        @if(filled($article->preview_text))
            <p>{{ \Illuminate\Support\Str::limit(trim(strip_tags((string) $article->preview_text)), 180) }}</p>
        @endif
        <a class="bona-editorial-card__link" href="{{ $articleUrl }}">
            {{ app()->getLocale() === 'ru' ? 'Читать статью' : 'Читати статтю' }} <span aria-hidden="true">→</span>
        </a>
    </div>
</article>
