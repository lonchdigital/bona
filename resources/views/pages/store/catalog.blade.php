@extends('layouts.store-main')

@php
    $selectedBrand = $selectedBrand ?? null;
    $catalogPageTitle = isset($filterGroup)
        ? $filterGroup->name
        : ($selectedBrand
            ? trans('base.catalog_by_manufacturer_heading', [
                'product_type' => $productType->name,
                'brand' => $selectedBrand->name,
            ])
            : $productType->name);
    $breadcrumbs = [];

    if($selectedBrand || isset($filterGroup)) {
        $breadcrumbs[] = [
            'url' => App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => $productType->slug]),
            'label' => $productType->name,
        ];
    }

    $breadcrumbs[] = [
        'url' => null,
        'label' => $selectedBrand?->name ?? (isset($filterGroup) ? $filterGroup->name : $productType->name),
    ];
    $currentCatalogPage = max(1, (int) $productsPaginated->currentPage());
    $catalogCanonicalBase = $selectedBrand
        ? url(App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.manufacturer.page', [
            'productTypeSlug' => $productType->slug,
            'brandSlug' => $selectedBrand->slug,
        ]))
        : (isset($filterGroup)
            ? request()->url()
            : url(App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', [
                'productTypeSlug' => $productType->slug,
            ])));
    $catalogPageUrl = static fn (int $page) => $page > 1
        ? $catalogCanonicalBase.'?'.http_build_query(['page' => $page])
        : $catalogCanonicalBase;
    $catalogCanonicalUrl = $catalogPageUrl($currentCatalogPage);
    $paginationTitleSuffix = $currentCatalogPage > 1
        ? ' — '.trans('base.pagination_page_title', ['page' => $currentCatalogPage])
        : '';
    $catalogMetaDescription = $selectedBrand
        ? trans('base.catalog_by_manufacturer_meta_description', [
            'product_type' => $productType->name,
            'brand' => $selectedBrand->name,
        ])
        : (isset($filterGroup) ? $filterGroup->meta_description : $productType->meta_description);
@endphp

@include('pages.store.partials.catalog-structured-data')

@section('canonical', $catalogCanonicalUrl)

@push('head')
    @if($currentCatalogPage > 1)
        <link rel="prev" href="{{ $catalogPageUrl($currentCatalogPage - 1) }}">
    @endif
    @if($productsPaginated->hasMorePages())
        <link rel="next" href="{{ $catalogPageUrl($currentCatalogPage + 1) }}">
    @endif
@endpush

@section('title')
    @if($selectedBrand)
        <title>{{ trans('base.catalog_by_manufacturer_meta_title', ['product_type' => $productType->name, 'brand' => $selectedBrand->name]).$paginationTitleSuffix }}</title>
        <meta name="title" content="{{ trans('base.catalog_by_manufacturer_meta_title', ['product_type' => $productType->name, 'brand' => $selectedBrand->name]) }}">
        <meta name="description" content="{{ trans('base.catalog_by_manufacturer_meta_description', ['product_type' => $productType->name, 'brand' => $selectedBrand->name]) }}">
    @elseif(isset($filterGroup))
        <title>{{ ($filterGroup->title_tag ?: $catalogPageTitle).$paginationTitleSuffix }}</title>
        @if($filterGroup->meta_title)<meta name="title" content="{{ $filterGroup->meta_title }}">@endif
        @if($filterGroup->meta_description)<meta name="description" content="{{ $filterGroup->meta_description }}">@endif
        @if($filterGroup->meta_keywords)<meta name="keywords" content="{{ $filterGroup->meta_keywords }}">@endif
    @else
        <title>{{ ($productType->meta_title ?: $catalogPageTitle.' — '.trans('base.site_title')).$paginationTitleSuffix }}</title>
        @if($productType->meta_title)<meta name="title" content="{{ $productType->meta_title }}">@endif
        @if($productType->meta_description)<meta name="description" content="{{ $productType->meta_description }}">@endif
        @if($productType->meta_keywords)<meta name="keywords" content="{{ $productType->meta_keywords }}">@endif
        @if($productType->meta_tags){!! $productType->meta_tags !!}@endif
    @endif
    <meta property="og:title" content="{{ $catalogPageTitle.' — '.trans('base.site_title').$paginationTitleSuffix }}">
@endsection

@section('content')
    @include('pages.store.partials.catalog-content')
    @include('pages.store.partials.catalog-additional-content')
@stop

@push('dynamic_scripts')
    <script>
        const catalog = {
            product_type_slug: @json($productType->slug),
            category_slug: '',
            last_page: {{ $productsPaginated->lastPage() }},
            current_page: {{ $productsPaginated->currentPage() }},
            products_count_by_filter_endpoint: @json(App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.products.by.filters', ['productTypeSlug' => $productType->slug])),
            filter_group_filters: @json($filerGroupFilters ?? '')
        };
    </script>
@endpush
