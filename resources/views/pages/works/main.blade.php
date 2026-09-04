@extends('layouts.store-main')

@php
    $worksTitle = trans('base.our_works');
    $worksIntro = trans('base.our_works_intro');
    $worksPageTitle = $worksTitle.' — '.trans('base.site_title');
    $worksUrl = url()->current();
    $worksHomeUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.home');
    $worksCover = count($works) > 0 ? $works->first() : null;
    $schemaFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG;
@endphp

@section('body_class', 'bona-content-body')
@section('seo_title', $worksPageTitle)
@section('meta_description', $worksIntro)
@section('og_title', $worksPageTitle)
@section('og_description', $worksIntro)

@if($worksCover?->og_image_url)
    @section('og_image', $worksCover->og_image_url)
@endif

@push('structured_data')
    @if(count($works) > 0)
        <script type="application/ld+json">{!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            '@id' => $worksUrl.'#works-page',
            'url' => $worksUrl,
            'name' => $worksTitle,
            'description' => $worksIntro,
            'inLanguage' => app()->getLocale() === 'ru' ? 'ru-UA' : 'uk-UA',
            'mainEntity' => [
                '@type' => 'ItemList',
                'itemListElement' => $works->values()->map(fn ($item, $index) => array_filter([
                    '@type' => 'ListItem',
                    'position' => (($works->currentPage() - 1) * $works->perPage()) + $index + 1,
                    'name' => (string) $item->name,
                    'url' => url(App\Helpers\MultiLangRoute::getMultiLangRoute('store.work.page', ['workSlug' => $item->slug])),
                    'image' => $item->og_image_url,
                ]))->all(),
            ],
        ], $schemaFlags) !!}</script>
    @endif
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => trans('base.home'), 'item' => url($worksHomeUrl)],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $worksTitle, 'item' => $worksUrl],
        ],
    ], $schemaFlags) !!}</script>
@endpush

@section('content')
    <div class="bona-content-page bona-works-page">
        <x-store.content-breadcrumbs :items="[['label' => $worksTitle]]" />

        <section class="bona-content-hero" aria-labelledby="works-page-title">
            <div class="bona-shell bona-content-hero__grid">
                <div class="bona-content-hero__copy">
                    <p class="bona-content-kicker">{{ trans('base.content_works_kicker') }}</p>
                    <h1 id="works-page-title">{{ $worksTitle }}</h1>
                </div>
                <p class="bona-content-hero__lead">{{ $worksIntro }}</p>
            </div>
        </section>

        <section class="bona-works-list" aria-label="{{ $worksTitle }}">
            <div class="bona-shell">
                @if(count($works) > 0)
                    <div class="bona-works-list__grid">
                        @foreach($works as $work)
                            @include('pages.works.partials.work_item', ['work' => $work, 'headingLevel' => 'h2'])
                        @endforeach
                    </div>

                    {{ $works->links('pagination.editorial') }}
                @else
                    <div class="bona-content-empty"><p>{{ trans('base.works_empty') }}</p></div>
                @endif
            </div>
        </section>
    </div>
@endsection
