@extends('layouts.store-main')

@php
    $plainContent = trim(preg_replace(
        '/\s+/u',
        ' ',
        html_entity_decode(strip_tags((string) ($allData['content'] ?? '')))
    ));
    $pageDescription = trim((string) ($allData['meta_description'] ?? ''))
        ?: Illuminate\Support\Str::limit($plainContent, 220)
        ?: trans('base.legal_page_intro');
    $pageTitle = trim((string) ($allData['meta_title'] ?? ''))
        ?: $heading.' — '.trans('base.site_title');
    $documentContent = preg_replace(
        '/<(\/?)h1(\s[^>]*)?>/iu',
        '<$1h2$2>',
        (string) ($allData['content'] ?? '')
    );
    $homeUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.home');
    $schemaFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG;
@endphp

@section('body_class', 'bona-content-body')
@section('seo_title', $pageTitle)
@section('meta_description', $pageDescription)
@section('meta_keywords', $allData['meta_keywords'] ?? '')
@section('og_title', $pageTitle)
@section('og_description', $pageDescription)

@push('head')
    {!! $allData['meta_tags'] ?? '' !!}
@endpush

@push('structured_data')
    <script type="application/ld+json">{!! json_encode(array_filter([
        '@'.'context' => 'https://schema.org',
        '@type' => 'WebPage',
        '@id' => url()->current().'#legal-page',
        'url' => url()->current(),
        'name' => $pageTitle,
        'description' => $pageDescription,
        'inLanguage' => app()->getLocale() === 'ru' ? 'ru-UA' : 'uk-UA',
        'isPartOf' => ['@id' => app(App\Services\Seo\OrganizationSchemaService::class)->organizationId()],
    ]), $schemaFlags) !!}</script>
    <script type="application/ld+json">{!! json_encode([
        '@'.'context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => trans('base.home'), 'item' => url($homeUrl)],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $heading, 'item' => url()->current()],
        ],
    ], $schemaFlags) !!}</script>
@endpush

@section('content')
    <div class="bona-content-page bona-legal-page">
        <x-store.content-breadcrumbs :items="[['label' => $heading]]" />

        <section class="bona-content-hero" aria-labelledby="legal-page-title">
            <div class="bona-shell bona-content-hero__grid">
                <div class="bona-content-hero__copy">
                    <p class="bona-content-kicker">{{ trans('base.legal_page_kicker') }}</p>
                    <h1 id="legal-page-title">{{ $heading }}</h1>
                </div>
                <p class="bona-content-hero__lead">{{ $pageDescription }}</p>
            </div>
        </section>

        <section class="bona-legal-document">
            <div class="bona-shell bona-legal-document__layout">
                <aside class="bona-legal-nav">
                    <p>{{ trans('base.legal_documents') }}</p>
                    <nav aria-label="{{ trans('base.legal_documents') }}">
                        @foreach($staticPageTypes as $pageType)
                            <a
                                href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.static-page.page', ['staticPageSlug' => $pageType['slug']]) }}"
                                @if((int) $pageType['id'] === (int) $staticPageType['id']) aria-current="page" @endif
                            >
                                <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                {{ $pageType['name'] }}
                            </a>
                        @endforeach
                    </nav>
                </aside>

                <article class="bona-legal-document__paper">
                    @if(filled($plainContent))
                        <div class="bona-content-richtext bona-legal-document__content">
                            {!! $documentContent !!}
                        </div>
                    @else
                        <div class="bona-content-empty">
                            <p>{{ trans('base.legal_page_empty') }}</p>
                        </div>
                    @endif
                </article>
            </div>
        </section>
    </div>
@endsection
