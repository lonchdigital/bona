@extends('layouts.store-main')

@php
    $catalogPageTitle = $selectedCategory->name;
    $breadcrumbs = [
        [
            'url' => App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => $productType->slug]),
            'label' => $productType->name,
        ],
        ['url' => null, 'label' => $selectedCategory->name],
    ];
    $currentCatalogPage = max(1, (int) $productsPaginated->currentPage());
    $catalogCanonicalBase = url(App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog-category.page', [
        'productTypeSlug' => $productType->slug,
        'categorySlug' => $selectedCategory->slug,
    ]));
    $catalogPageUrl = static fn (int $page) => $page > 1
        ? $catalogCanonicalBase.'?'.http_build_query(['page' => $page])
        : $catalogCanonicalBase;
    $catalogCanonicalUrl = $catalogPageUrl($currentCatalogPage);
    $paginationTitleSuffix = $currentCatalogPage > 1
        ? ' — '.trans('base.pagination_page_title', ['page' => $currentCatalogPage])
        : '';
    $catalogMetaDescription = isset($seogenData)
        ? $seogenData->meta_description_tag
        : (isset($filterGroup) ? $filterGroup->meta_description : $selectedCategory->meta_description);
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
    @if(isset($seogenData))
        <title>{{ $seogenData->html_title_tag.$paginationTitleSuffix }}</title>
        <meta name="title" content="{{ $seogenData->meta_title_tag }}">
        <meta name="description" content="{{ $seogenData->meta_description_tag }}">
        <meta name="keywords" content="{{ $seogenData->meta_keywords_tag }}">
    @elseif(isset($filterGroup))
        <title>{{ ($filterGroup->title_tag ?: $catalogPageTitle).$paginationTitleSuffix }}</title>
        @if($filterGroup->meta_title)<meta name="title" content="{{ $filterGroup->meta_title }}">@endif
        @if($filterGroup->meta_description)<meta name="description" content="{{ $filterGroup->meta_description }}">@endif
        @if($filterGroup->meta_keywords)<meta name="keywords" content="{{ $filterGroup->meta_keywords }}">@endif
    @else
        <title>{{ ($selectedCategory->meta_title ?: $catalogPageTitle.' — '.trans('base.site_title')).$paginationTitleSuffix }}</title>
        @if($selectedCategory->meta_title)<meta name="title" content="{{ $selectedCategory->meta_title }}">@endif
        @if($selectedCategory->meta_description)<meta name="description" content="{{ $selectedCategory->meta_description }}">@endif
        @if($selectedCategory->meta_keywords)<meta name="keywords" content="{{ $selectedCategory->meta_keywords }}">@endif
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
            category_slug: @json($selectedCategory->slug),
            last_page: {{ $productsPaginated->lastPage() }},
            current_page: {{ $productsPaginated->currentPage() }},
            products_count_by_filter_endpoint: @json(App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.category.products.by.filters', ['categorySlug' => $selectedCategory->slug, 'productTypeSlug' => $productType->slug])),
            filter_group_filters: @json($filerGroupFilters ?? '')
        };
    </script>
@endpush
