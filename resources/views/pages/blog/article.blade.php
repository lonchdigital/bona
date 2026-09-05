@extends('layouts.store-main')

@php
    $homeUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.home');
    $blogUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('blog.main.page');
    $articleUrl = url()->current();
    $articleDescription = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) ($blogArticle->meta_description ?: $blogArticle->preview_text)))));
    $articleAuthor = $articleAuthor ?? null;
    $authorPageUrl = $articleAuthor ? App\Helpers\MultiLangRoute::getMultiLangRoute('store.author.page', ['authorSlug' => $articleAuthor->slug]) : null;
    $authorName = $articleAuthor ? (string) $articleAuthor->name : ($applicationGlobalOptions['authorName'][app()->getLocale()] ?? null);
    $authorJobTitle = $articleAuthor ? (string) $articleAuthor->job_title : ($applicationGlobalOptions['authorDescription'][app()->getLocale()] ?? null);
    $authorAbout = $articleAuthor ? trim((string) $articleAuthor->short_description) : null;
    $authorAvatar = $articleAuthor?->og_image_url ?: (($applicationGlobalOptions['authorAvatar'] ?? null) ? url('/storage/'.$applicationGlobalOptions['authorAvatar']) : null);
    $isRussian = app()->getLocale() === 'ru';
    $schemaFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG;
@endphp

@section('body_class', 'bona-content-body bona-article-body')
@section('seo_title', $blogArticle->meta_title ?: $blogArticle->name.' — '.trans('base.site_title'))
@section('meta_description', $articleDescription)
@section('meta_keywords', $blogArticle->meta_keywords ?: '')
@section('og_type', 'article')
@section('og_title', $blogArticle->name.' — '.trans('base.site_title'))
@section('og_description', $articleDescription)
@section('twitter_title', $blogArticle->name)
@section('twitter_description', $articleDescription)
@if($blogArticle->og_image_url)
    @section('og_image', $blogArticle->og_image_url)
    @section('og_image_alt', $blogArticle->name)
@endif

@push('head')
    @if($blogArticle->meta_tags){!! $blogArticle->meta_tags !!}@endif
@endpush

@push('structured_data')
    <script type="application/ld+json">{!! json_encode(array_filter([
        '@'.'context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        '@id' => $articleUrl.'#article',
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $articleUrl],
        'url' => $articleUrl,
        'headline' => (string) $blogArticle->name,
        'description' => $articleDescription ?: null,
        'image' => $blogArticle->og_image_url ? [$blogArticle->og_image_url] : null,
        'inLanguage' => app()->getLocale() === 'ru' ? 'ru-UA' : 'uk-UA',
        'datePublished' => $blogArticle->created_at?->toAtomString(),
        'dateModified' => $blogArticle->updated_at?->toAtomString(),
        'articleSection' => trans('base.blog'),
        'author' => $authorName ? array_filter([
            '@type' => 'Person',
            '@id' => $authorPageUrl ? url($authorPageUrl).'#person' : null,
            'name' => $authorName,
            'jobTitle' => $authorJobTitle ?: null,
            'image' => $authorAvatar ?: null,
            'url' => $authorPageUrl ? url($authorPageUrl) : null,
            'sameAs' => $articleAuthor?->sameAsLinks() ?: null,
        ]) : ['@type' => 'Organization', '@id' => app(\App\Services\Seo\OrganizationSchemaService::class)->organizationId()],
        'publisher' => ['@id' => app(\App\Services\Seo\OrganizationSchemaService::class)->organizationId()],
    ]), $schemaFlags) !!}</script>
    <script type="application/ld+json">{!! json_encode([
        '@'.'context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => trans('base.home'), 'item' => url($homeUrl)],
            ['@type' => 'ListItem', 'position' => 2, 'name' => trans('base.blog'), 'item' => url($blogUrl)],
            ['@type' => 'ListItem', 'position' => 3, 'name' => (string) $blogArticle->name, 'item' => $articleUrl],
        ],
    ], $schemaFlags) !!}</script>
    @if($articleFaq)
        <script type="application/ld+json">{!! json_encode([
            '@'.'context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($entry) => [
                '@type' => 'Question',
                'name' => $entry['question'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $entry['answer']],
            ], $articleFaq),
        ], $schemaFlags) !!}</script>
    @endif
@endpush

@section('content')
    <article class="bona-content-page bona-article-page">
        <header class="bona-article-hero{{ $blogArticle->hero_image_url ? '' : ' bona-article-hero--without-image' }}">
            @if($blogArticle->hero_image_url)
                <figure class="bona-article-hero__media">
                    <img src="{{ $blogArticle->hero_image_url }}" alt="{{ $blogArticle->name }}" width="1600" height="1000" fetchpriority="high" decoding="async">
                </figure>
            @endif

            <x-store.content-breadcrumbs :items="[
                ['label' => trans('base.blog'), 'url' => $blogUrl],
                ['label' => $blogArticle->name],
            ]" />

            <div class="bona-shell bona-article-hero__copy">
                <p class="bona-article-hero__edition">Bona Doors Editorial</p>
                <h1>{{ $blogArticle->name }}</h1>
                <div class="bona-article-hero__meta">
                    <time datetime="{{ $blogArticle->created_at->toDateString() }}">{{ $blogArticle->created_at->translatedFormat('d F Y') }}</time>
                    @if($authorName)<span>{{ $authorName }}</span>@endif
                </div>
                @if(filled($blogArticle->preview_text))
                    <p class="bona-article-hero__lead">{{ $blogArticle->preview_text }}</p>
                @endif
            </div>
        </header>

        <div class="bona-shell bona-article-layout">
            <div class="bona-article-content">
                <div class="bona-article-main">
                    @foreach($articleBlocks ?? $blogArticle->blocks as $block)
                    @php
                        $blockType = (int) data_get($block, 'type_id');
                        $content = data_get($block, 'content', []);
                        $content = is_array($content) ? $content : [];
                    @endphp

                    @if($blockType === \App\DataClasses\BlogArticleBlockTypesDataClass::TYPE_TEXT)
                        @php($html = (string) ($content[app()->getLocale()] ?? ''))
                        @if(filled(strip_tags($html)))
                            <section class="bona-content-richtext bona-article-block bona-article-block--text">{!! $html !!}</section>
                        @endif

                    @elseif($blockType === \App\DataClasses\BlogArticleBlockTypesDataClass::TYPE_IMAGE)
                        @if(! empty($content['images']))
                            <section class="bona-article-block bona-article-images{{ count($content['images']) > 1 ? ' bona-article-images--grid' : '' }}">
                                @foreach($content['images'] as $image)
                                    @if(filled($image['image_url'] ?? null))
                                        <figure>
                                            <img src="{{ $image['image_url'] }}" alt="{{ $blogArticle->name }}" width="1100" height="760" loading="lazy" decoding="async">
                                            @if(data_get($image, 'selected_product.slug'))
                                                <figcaption><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => data_get($image, 'selected_product.slug')]) }}">{{ data_get($image, 'selected_product.name') }} <span aria-hidden="true">→</span></a></figcaption>
                                            @endif
                                        </figure>
                                    @endif
                                @endforeach
                            </section>
                        @endif

                    @elseif($blockType === \App\DataClasses\BlogArticleBlockTypesDataClass::TYPE_QUOTE)
                        @php($quote = (string) ($content['quote'][app()->getLocale()] ?? ''))
                        @if(filled(strip_tags($quote)))
                            <figure class="bona-article-block bona-article-quote">
                                @if(filled($content['quote_author_image_url'] ?? null))
                                    <img src="{{ $content['quote_author_image_url'] }}" alt="{{ $content['quote_author'][app()->getLocale()] ?? '' }}" width="96" height="96" loading="lazy">
                                @endif
                                <blockquote>{!! $quote !!}</blockquote>
                                @if(filled($content['quote_author'][app()->getLocale()] ?? null))
                                    <figcaption>
                                        <strong>{{ $content['quote_author'][app()->getLocale()] }}</strong>
                                        @if(filled($content['quote_author_position'][app()->getLocale()] ?? null))<span>{{ $content['quote_author_position'][app()->getLocale()] }}</span>@endif
                                    </figcaption>
                                @endif
                            </figure>
                        @endif

                    @elseif($blockType === \App\DataClasses\BlogArticleBlockTypesDataClass::TYPE_SPONSOR)
                        @php($sponsorText = (string) ($content['sponsor_text'][app()->getLocale()] ?? ''))
                        @if(filled(strip_tags($sponsorText)) || filled($content['sponsor_image_url'] ?? null))
                            <aside class="bona-article-block bona-article-sponsor">
                                @if(filled($content['sponsor_image_url'] ?? null))<img src="{{ $content['sponsor_image_url'] }}" alt="" width="180" height="120" loading="lazy">@endif
                                <div>{!! $sponsorText !!}</div>
                                @if(filled($content['sponsor_link'] ?? null))<a href="{{ $content['sponsor_link'] }}" target="_blank" rel="sponsored noopener">{{ app()->getLocale() === 'ru' ? 'Подробнее' : 'Докладніше' }} →</a>@endif
                            </aside>
                        @endif

                    @elseif($blockType === \App\DataClasses\BlogArticleBlockTypesDataClass::TYPE_VIDEO)
                        @if(filled($content['video_link'] ?? null))
                            <section class="bona-article-block bona-article-video">
                                <iframe src="{{ $content['video_link'] }}" title="{{ $blogArticle->name }}" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            </section>
                        @endif

                    @elseif($blockType === \App\DataClasses\BlogArticleBlockTypesDataClass::TYPE_SLIDER)
                        @if(! empty($content['images']))
                            <section class="bona-article-block bona-article-slider" aria-label="{{ app()->getLocale() === 'ru' ? 'Галерея статьи' : 'Галерея статті' }}">
                                @foreach($content['images'] as $image)
                                    @if(filled($image['image_url'] ?? null))
                                        <figure><img src="{{ $image['image_url'] }}" alt="{{ $blogArticle->name }}" width="820" height="820" loading="lazy" decoding="async"></figure>
                                    @endif
                                @endforeach
                            </section>
                        @endif

                    @elseif($blockType === \App\DataClasses\BlogArticleBlockTypesDataClass::TYPE_QUESTIONS_AND_ANSWERS)
                        @php($questions = collect($content['questions'] ?? [])->filter(fn ($item) => filled(strip_tags((string) ($item['question'][app()->getLocale()] ?? ''))) && filled(strip_tags((string) ($item['answer'][app()->getLocale()] ?? '')))))
                        @if($questions->isNotEmpty())
                            <section class="bona-article-block bona-article-faq">
                                <h2>{{ app()->getLocale() === 'ru' ? 'Частые вопросы' : 'Часті запитання' }}</h2>
                                @foreach($questions as $item)
                                    <details>
                                        <summary>{!! $item['question'][app()->getLocale()] !!}<span aria-hidden="true">+</span></summary>
                                        <div class="bona-content-richtext">{!! $item['answer'][app()->getLocale()] !!}</div>
                                    </details>
                                @endforeach
                            </section>
                        @endif
                    @endif
                    @endforeach
                </div>

                @include('pages.blog.partials.article-utilities')

                @if($authorName)
                    <footer class="bona-article-author">
                        <div class="bona-article-author__portrait">
                            @if($authorAvatar)
                                <img src="{{ $authorAvatar }}" alt="{{ $authorName }}" width="128" height="128" loading="lazy" decoding="async">
                            @else
                                <span aria-hidden="true">BD</span>
                            @endif
                        </div>
                        <div class="bona-article-author__copy">
                            <p class="bona-article-author__label">{{ trans('base.author') }}</p>
                            <h2>@if($authorPageUrl)<a href="{{ $authorPageUrl }}" rel="author">{{ $authorName }}</a>@else{{ $authorName }}@endif</h2>
                            @if($authorJobTitle)<p class="bona-article-author__role">{{ $authorJobTitle }}</p>@endif
                            @if($authorAbout && $authorAbout !== trim((string) $authorJobTitle))<p class="bona-article-author__about">{{ $authorAbout }}</p>@endif
                            @if($authorPageUrl)<a class="bona-article-author__link" href="{{ $authorPageUrl }}">{{ trans('base.author_read_more') }}<span aria-hidden="true">→</span></a>@endif
                        </div>
                    </footer>
                @endif
            </div>
        </div>

        @if($latestArticles->isNotEmpty())
            <section class="bona-article-related" aria-labelledby="related-articles-title">
                <div class="bona-shell">
                    <header class="bona-content-heading">
                        <div><p class="bona-content-kicker">Bona Doors</p><h2 id="related-articles-title">{{ trans('base.article_read_also') }}</h2></div>
                    </header>
                    <div class="bona-editorial-grid">
                        @foreach($latestArticles as $latestArticle)
                            @include('pages.store.partials.article_item', ['article' => $latestArticle])
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </article>
@endsection
