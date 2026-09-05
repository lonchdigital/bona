@extends('layouts.store-main')

@php
    $searchQuery = trim((string) ($searchQuery ?? ''));
    $catalogPageTitle = $searchQuery !== ''
        ? trans('base.storefront_search_results_for', ['query' => $searchQuery])
        : trans('base.all_products');
    $breadcrumbs = [['url' => null, 'label' => $catalogPageTitle]];
    $currentCatalogPage = max(1, (int) $productsPaginated->currentPage());
    $catalogCanonicalBase = url(App\Helpers\MultiLangRoute::getMultiLangRoute('store.all-products.page'));
    $catalogPageUrl = static function (int $page) use ($catalogCanonicalBase, $searchQuery) {
        $parameters = array_filter([
            'query' => $searchQuery !== '' ? $searchQuery : null,
            'page' => $page > 1 ? $page : null,
        ]);

        return $parameters === [] ? $catalogCanonicalBase : $catalogCanonicalBase.'?'.http_build_query($parameters);
    };
    $catalogCanonicalUrl = $searchQuery !== ''
        ? $catalogCanonicalBase
        : $catalogPageUrl($currentCatalogPage);
    $paginationTitleSuffix = $currentCatalogPage > 1
        ? ' — '.trans('base.pagination_page_title', ['page' => $currentCatalogPage])
        : '';
@endphp

@section('canonical', $catalogCanonicalUrl)

@push('head')
    @if($searchQuery !== '')
        <meta name="robots" content="noindex, follow">
    @endif
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
        <title>{{ $catalogPageTitle.' — '.trans('base.site_title').$paginationTitleSuffix }}</title>
    @endif
    <meta property="og:title" content="{{ $catalogPageTitle.' — '.trans('base.site_title').$paginationTitleSuffix }}">
@endsection

@section('content')
    @include('pages.store.partials.catalog-content')
@stop

@push('dynamic_scripts')
    <script>
        const catalog = {
            all_products_catalog_slug: 'shop',
            category_slug: '',
            last_page: {{ $productsPaginated->lastPage() }},
            current_page: {{ $productsPaginated->currentPage() }},
            products_count_by_filter_endpoint: @json(App\Helpers\MultiLangRoute::getMultiLangRoute('store.all-products.by.filters')),
            filter_group_filters: @json($filerGroupFilters ?? '')
        };
    </script>
@endpush
