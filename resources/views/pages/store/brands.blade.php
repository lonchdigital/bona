@extends('layouts.store-main')

@php
    $pageTitle = trans('base.brands_page_seo_title');
    $pageDescription = trans('base.brands_page_description');
    $schemaFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG;
@endphp

@section('seo_title', $pageTitle)
@section('meta_description', $pageDescription)
@section('og_title', $pageTitle)
@section('og_description', $pageDescription)

@push('structured_data')
    <script type="application/ld+json">{!! json_encode([
        '@'.'context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        '@id' => url()->current().'#brands',
        'url' => url()->current(),
        'name' => $pageTitle,
        'description' => $pageDescription,
        'inLanguage' => app()->getLocale() === 'ru' ? 'ru-UA' : 'uk-UA',
        'mainEntity' => [
            '@type' => 'ItemList',
            'itemListElement' => $brands->values()->map(fn ($brand, $index) => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $brand['name'],
                'url' => url($brand['url']),
            ])->all(),
        ],
    ], $schemaFlags) !!}</script>
@endpush

@section('content')
    <div class="bona-content-page bona-brands-page">
        <x-store.content-breadcrumbs :items="[['label' => trans('base.brands_page_title')]]" />

        <section class="bona-content-hero" aria-labelledby="brands-page-title">
            <div class="bona-shell bona-content-hero__grid">
                <div class="bona-content-hero__copy">
                    <p class="bona-content-kicker">Bona Doors</p>
                    <h1 id="brands-page-title">{{ trans('base.brands_page_title') }}</h1>
                </div>
                <p class="bona-content-hero__lead">{{ $pageDescription }}</p>
            </div>
        </section>

        <section class="bona-brands-list" aria-label="{{ trans('base.brands_page_title') }}">
            <div class="bona-shell">
                <ul class="bona-brands-list__grid">
                    @foreach($brands as $brand)
                        <li class="bona-brand-card">
                            <a href="{{ $brand['url'] }}">
                                @if($brand['logo'])
                                    <img src="{{ $brand['logo'] }}" alt="" width="160" height="80" loading="lazy" decoding="async">
                                @endif
                                <strong>{{ $brand['name'] }}</strong>
                                <span>{{ $brand['type'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    </div>
@endsection
