@extends('layouts.store-main')

@php
    $worksTitle = trans('base.our_works');
    $worksIntro = trans('base.our_works_intro');
    $worksUrl = url()->current();
    $worksHomeUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.home');
@endphp

@section('title')
    <title>{{ $worksTitle . ' — ' . trans('base.site_title') }}</title>
    <meta name="title" content="{{ $worksTitle }}">
    <meta name="description" content="{{ $worksIntro }}">

    <meta property="og:title" content="{{ $worksTitle . ' — ' . trans('base.site_title') }}">
    <meta property="og:description" content="{{ $worksIntro }}">
    <meta name="twitter:card" content="summary_large_image">
@endsection

@php
    $worksCover = $works->isNotEmpty() ? $works->first() : null;
@endphp

@if($worksCover && $worksCover->og_image_url)
    @section('og_image', $worksCover->og_image_url)
@endif

@section('content')

    @include('pages.store.partials.page_header', ['links' => ['#' => 'our_works']])

    @php
        $schemaFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG;

        $worksListSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'url' => $worksUrl,
            'name' => $worksTitle,
            'description' => $worksIntro,
            'inLanguage' => app()->getLocale(),
            'mainEntity' => [
                '@type' => 'ItemList',
                'itemListElement' => $works->values()->map(fn ($item, $index) => array_filter([
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => (string) $item->name,
                    'url' => url(App\Helpers\MultiLangRoute::getMultiLangRoute('store.work.page', ['workSlug' => $item->slug])),
                    'image' => $item->og_image_url,
                ]))->all(),
            ],
        ];

        $worksBreadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => trans('base.home'), 'item' => url($worksHomeUrl)],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $worksTitle, 'item' => $worksUrl],
            ],
        ];
    @endphp

    @if($works->isNotEmpty())
        <script type="application/ld+json">{!! json_encode($worksListSchema, $schemaFlags) !!}</script>
    @endif
    <script type="application/ld+json">{!! json_encode($worksBreadcrumbSchema, $schemaFlags) !!}</script>

    <section class="blog art-section-pd">
        <div class="container">

            <div class="row">
                <header class="col-12 art-header-left">
                    <div>
                        <h1 class="title">{{ $worksTitle }}</h1>
                        <div class="subtitle font-two">
                            <p>{{ $worksIntro }}</p>
                        </div>
                    </div>
                </header>
            </div>

            <div class="row">
                @if( count($works) > 0 )
                    <div class="art-blog-archive-wrapper">
                        @foreach($works as $work)
                            <div class="col-lg-4">
                                @include('pages.works.partials.work_item', ['work' => $work])
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="col-12">
                        <p class="nothing-found-text mt-5">{{ trans('base.works_empty') }}</p>
                    </div>
                @endif
            </div>

            @if($works->hasPages())
                {{ $works->links('pagination.common') }}
            @endif

        </div>
    </section>

@endsection
