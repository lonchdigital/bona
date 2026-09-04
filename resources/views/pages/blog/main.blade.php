@extends('layouts.store-main')

@php
    $blogTitle = filled($blogPageConfig?->title) ? (string) $blogPageConfig->title : trans('base.blog');
    $blogDescription = trim((string) ($blogPageConfig?->meta_description ?: trans('base.blog_head_text')));
    $blogUrl = url()->current();
    $homeUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.home');
    $featuredArticle = $articles->onFirstPage() ? $articles->first() : null;
    $schemaFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG;
@endphp

@section('body_class', 'bona-content-body')
@section('seo_title', $blogPageConfig?->meta_title ?: $blogTitle.' — '.trans('base.site_title'))
@section('meta_description', $blogDescription)
@section('meta_keywords', $blogPageConfig?->meta_keywords ?: '')
@section('og_title', $blogTitle.' — '.trans('base.site_title'))
@section('og_description', $blogDescription)
@if($featuredArticle?->og_image_url)
    @section('og_image', $featuredArticle->og_image_url)
    @section('og_image_alt', $featuredArticle->name)
@endif

@push('head')
    @if($blogPageConfig?->meta_tags)
        {!! $blogPageConfig->meta_tags !!}
    @endif
    @if($articles->previousPageUrl())<link rel="prev" href="{{ $articles->previousPageUrl() }}">@endif
    @if($articles->nextPageUrl())<link rel="next" href="{{ $articles->nextPageUrl() }}">@endif
@endpush

@push('structured_data')
    <script type="application/ld+json">{!! json_encode(array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Blog',
        '@id' => $blogUrl.'#blog',
        'url' => $blogUrl,
        'name' => $blogTitle,
        'description' => $blogDescription ?: null,
        'inLanguage' => app()->getLocale() === 'ru' ? 'ru-UA' : 'uk-UA',
        'publisher' => ['@id' => app(\App\Services\Seo\OrganizationSchemaService::class)->organizationId()],
        'blogPost' => $articles->map(fn ($article) => array_filter([
            '@type' => 'BlogPosting',
            'headline' => (string) $article->name,
            'url' => url(App\Helpers\MultiLangRoute::getMultiLangRoute('blog.article.page', ['blogArticleSlug' => $article->slug])),
            'datePublished' => $article->created_at?->toAtomString(),
            'dateModified' => $article->updated_at?->toAtomString(),
            'image' => $article->og_image_url ?: null,
            'description' => filled($article->preview_text) ? trim(strip_tags((string) $article->preview_text)) : null,
        ]))->values()->all(),
    ]), $schemaFlags) !!}</script>
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => trans('base.home'), 'item' => url($homeUrl)],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $blogTitle, 'item' => $blogUrl],
        ],
    ], $schemaFlags) !!}</script>
@endpush

@section('content')
    <div class="bona-content-page bona-blog-index">
        <x-store.content-breadcrumbs :items="[['label' => $blogTitle]]" />

        <section class="bona-blog-index__hero">
            <div class="bona-shell bona-blog-index__hero-grid">
                <div>
                    <p class="bona-content-kicker">Bona Doors · Editorial</p>
                    <h1>{{ $blogTitle }}</h1>
                </div>
                @if($blogDescription)
                    <p>{{ $blogDescription }}</p>
                @endif
            </div>
        </section>

        @if($featuredArticle)
            @php($featuredUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('blog.article.page', ['blogArticleSlug' => $featuredArticle->slug]))
            <section class="bona-blog-index__featured" aria-labelledby="featured-article-title">
                <div class="bona-shell bona-blog-feature">
                    <a class="bona-blog-feature__media" href="{{ $featuredUrl }}">
                        @if($featuredArticle->hero_image_url)
                            <img src="{{ $featuredArticle->hero_image_url }}" alt="{{ $featuredArticle->name }}" width="1200" height="800" fetchpriority="high" decoding="async">
                        @endif
                    </a>
                    <div class="bona-blog-feature__copy">
                        <p class="bona-content-kicker">{{ app()->getLocale() === 'ru' ? 'Новая публикация' : 'Нова публікація' }}</p>
                        <time datetime="{{ $featuredArticle->created_at->toDateString() }}">{{ $featuredArticle->created_at->translatedFormat('d F Y') }}</time>
                        <h2 id="featured-article-title"><a href="{{ $featuredUrl }}">{{ $featuredArticle->name }}</a></h2>
                        @if(filled($featuredArticle->preview_text))
                            <p>{{ \Illuminate\Support\Str::limit(trim(strip_tags((string) $featuredArticle->preview_text)), 260) }}</p>
                        @endif
                        <a class="bona-button bona-button--dark" href="{{ $featuredUrl }}">{{ app()->getLocale() === 'ru' ? 'Читать' : 'Читати' }}</a>
                    </div>
                </div>
            </section>
        @endif

        <section class="bona-blog-index__archive" aria-labelledby="blog-archive-title">
            <div class="bona-shell">
                <header class="bona-content-heading">
                    <div>
                        <p class="bona-content-kicker">{{ app()->getLocale() === 'ru' ? 'Все материалы' : 'Усі матеріали' }}</p>
                        <h2 id="blog-archive-title">{{ app()->getLocale() === 'ru' ? 'Идеи, советы и детали' : 'Ідеї, поради й деталі' }}</h2>
                    </div>
                </header>

                @if($articles->isNotEmpty())
                    <div class="bona-editorial-grid">
                        @foreach($articles as $article)
                            @if(! $featuredArticle || $article->isNot($featuredArticle))
                                @include('pages.store.partials.article_item', ['article' => $article])
                            @endif
                        @endforeach
                    </div>
                    {{ $articles->links('pagination.editorial') }}
                @else
                    <p class="bona-blog-index__empty">{{ trans('base.nothing_found') }}</p>
                @endif
            </div>
        </section>
    </div>
@endsection
