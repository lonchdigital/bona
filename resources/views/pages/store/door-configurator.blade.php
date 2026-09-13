@extends('layouts.store-main')
@php
    $title = trans('configurator.heading');
    $description = trans('configurator.description');
    $ui = trans('configurator.ui');
    $pageUrl = url(App\Helpers\MultiLangRoute::getMultiLangRoute('store.door-configurator.page'));
    $homeUrl = url(App\Helpers\MultiLangRoute::getMultiLangRoute('store.home'));
    $catalogUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.all-products.page');
    $assetBase = asset('assets/door-configurator/v1').'/';
    $schemaFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
@endphp
@section('body_class', 'bona-content-body')
@section('seo_title', trans('configurator.title'))
@section('meta_description', $description)
@section('canonical', $pageUrl)
@section('og_title', trans('configurator.title'))
@section('og_description', $description)
@section('og_image', $assetBase.'room-living-v5.webp')
@section('og_image_alt', $title)
@section('twitter_title', trans('configurator.title'))
@section('twitter_description', $description)
@push('structured_data')
    <script type="application/ld+json">{!! json_encode([
        '@'.'context' => 'https://schema.org', '@type' => 'WebPage',
        '@id' => $pageUrl.'#webpage', 'url' => $pageUrl, 'name' => $title,
        'description' => $description, 'inLanguage' => app()->getLocale() === 'ru' ? 'ru-UA' : 'uk-UA',
        'breadcrumb' => ['@id' => $pageUrl.'#breadcrumbs'],
        'publisher' => ['@id' => rtrim(config('app.url'), '/').'#organization'],
    ], $schemaFlags) !!}</script>
    <script type="application/ld+json">{!! json_encode([
        '@'.'context' => 'https://schema.org', '@type' => 'BreadcrumbList', '@id' => $pageUrl.'#breadcrumbs',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => trans('base.home'), 'item' => $homeUrl],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $title, 'item' => $pageUrl],
        ],
    ], $schemaFlags) !!}</script>
@endpush
@section('content')
<div class="bona-door-configurator">
    <x-store.content-breadcrumbs :items="[['label' => $title]]" />
    <div class="bona-shell">
        @if(count($configuratorCatalog['products']))
            <div class="bona-door-studio" data-door-studio>
                @include('pages.store.partials.door-studio')
            </div>
            <script type="application/json" id="door-studio-data">{!! json_encode(array_merge($configuratorCatalog, [
                'locale' => app()->getLocale(), 'assets' => $assetBase, 'ui' => $ui, 'csrf' => csrf_token(),
                'cartUrl' => App\Helpers\MultiLangRoute::getMultiLangRoute('store.door-configurator.cart'),
            ]), $schemaFlags) !!}</script>
        @else
            <section class="bona-configurator-empty">
                <h1>{{ $title }}</h1><p>{{ trans('configurator.empty') }}</p>
                <a class="bona-button bona-button--dark" href="{{ $catalogUrl }}">{{ trans('configurator.catalog') }}</a>
            </section>
        @endif
        <section class="bona-configurator-editorial" aria-labelledby="configurator-seo-title">
            <div class="bona-configurator-editorial__intro">
                <h2 id="configurator-seo-title">{{ trans('configurator.seo_title') }}</h2>
                <p>{{ trans('configurator.seo_intro') }}</p>
            </div>
            <div class="bona-configurator-editorial__grid">
                @foreach(trans('configurator.sections') as [$heading, $text])
                    <section><h3>{{ $heading }}</h3><p>{{ $text }}</p></section>
                @endforeach
                <nav aria-label="{{ trans('configurator.links_title') }}">
                    <h3>{{ trans('configurator.links_title') }}</h3>
                    @foreach([$catalogUrl, App\Helpers\MultiLangRoute::getMultiLangRoute('store.services'), App\Helpers\MultiLangRoute::getMultiLangRoute('store.delivery-info')] as $index => $href)
                        <a href="{{ $href }}">{{ trans('configurator.links')[$index] }} <span aria-hidden="true">→</span></a>
                    @endforeach
                </nav>
            </div>
        </section>
        <section class="bona-configurator-faq" aria-labelledby="configurator-faq-title">
            <h2 id="configurator-faq-title">{{ trans('configurator.faq_title') }}</h2>
            <div>
                @foreach(trans('configurator.faq') as [$question, $answer])
                    <details><summary>{{ $question }}</summary><p>{{ $answer }}</p></details>
                @endforeach
            </div>
        </section>
    </div>
</div>
@endsection
