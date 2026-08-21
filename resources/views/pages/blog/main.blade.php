@extends('layouts.store-main')

@section('title')

    @if(isset($blogPageConfig))
        @if($blogPageConfig->meta_title)
            <title>{{ $blogPageConfig->meta_title }}</title>
            <meta name="title" content="{{ $blogPageConfig->meta_title }}">
        @endif

        @if($blogPageConfig->meta_description)
            <meta name="description" content="{{ $blogPageConfig->meta_description }}">
        @endif
        @if($blogPageConfig->meta_keywords)
            <meta name="keywords" content="{{ $blogPageConfig->meta_keywords }}">
        @endif

        @if($blogPageConfig->meta_tags)
            {!! $blogPageConfig->meta_tags !!}
        @endif
    @endif

    <meta property="og:title" content="{{ trans('base.blog') . ' - ' . trans('base.site_title') }}">

    @if(isset($blogPageConfig) && $blogPageConfig->meta_description)
        <meta property="og:description" content="{{ $blogPageConfig->meta_description }}">
    @endif

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ trans('base.blog') . ' - ' . trans('base.site_title') }}">

    {{-- Paginated listings still say which page comes before and after. --}}
    @if($articles->previousPageUrl())
        <link rel="prev" href="{{ $articles->previousPageUrl() }}">
    @endif
    @if($articles->nextPageUrl())
        <link rel="next" href="{{ $articles->nextPageUrl() }}">
    @endif

@endsection

@php($blogCoverArticle = $articles->first())

@if($blogCoverArticle && $blogCoverArticle->og_image_url)
    @section('og_image', $blogCoverArticle->og_image_url)
@endif

@section('content')

    @include('pages.store.partials.page_header', ['links' => ['#' => 'blog']])

    @php
        $blogUrl = url()->current();
        $homeUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.home');

        $publisher = [
            '@type' => 'Organization',
            'name' => trans('base.organization'),
            'url' => url('/'),
        ];

        $blogSchema = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Blog',
            '@id' => $blogUrl . '#blog',
            'url' => $blogUrl,
            'name' => (isset($blogPageConfig) && $blogPageConfig->title) ? (string) $blogPageConfig->title : trans('base.blog'),
            'description' => (isset($blogPageConfig) && $blogPageConfig->meta_description) ? (string) $blogPageConfig->meta_description : null,
            'inLanguage' => app()->getLocale(),
            'publisher' => $publisher,
            'blogPost' => $articles->map(fn ($article) => array_filter([
                '@type' => 'BlogPosting',
                'headline' => (string) $article->name,
                'url' => url(App\Helpers\MultiLangRoute::getMultiLangRoute('blog.article.page', ['blogArticleSlug' => $article->slug])),
                'datePublished' => $article->created_at?->toAtomString(),
                'dateModified' => $article->updated_at?->toAtomString(),
                'image' => $article->og_image_url,
                'description' => (string) $article->preview_text ?: null,
            ]))->values()->all(),
        ]);

        $blogBreadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => trans('base.home'), 'item' => url($homeUrl)],
                ['@type' => 'ListItem', 'position' => 2, 'name' => trans('base.blog'), 'item' => $blogUrl],
            ],
        ];

        $schemaFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG;
    @endphp

    <script type="application/ld+json">{!! json_encode($blogSchema, $schemaFlags) !!}</script>
    <script type="application/ld+json">{!! json_encode($blogBreadcrumbSchema, $schemaFlags) !!}</script>

    <!-- ========================  Blog ======================== -->
    <section class="blog art-section-pd">

        <div class="container">

            <div class="row">
                <header class=" col-12 art-header-left">
                    <div>
                        <h1 class="title">{{ (isset($blogPageConfig)) ? $blogPageConfig->title : trans('base.blog') }}</h1>
                    </div>
                </header>
            </div>

            <div class="row">

                @if( count($articles) > 0 )
                    <div class="art-blog-archive-wrapper">
                        @foreach($articles as $article)
                            <div class="col-lg-4">
                                @include('pages.store.partials.article_item', ['article' => $article])
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="col-12">
                        <p class="nothing-found-text mt-5">{{ trans('base.nothing_found') }}</p>
                    </div>
                @endif

            </div> <!--/row-->

            <!-- === pagination === -->
            {{ $articles->links('pagination.common') }}

        </div><!--/container-->
    </section>

@endsection
