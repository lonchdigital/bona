@extends('layouts.store-main')

@php
    $catalogPageTitle = trans('base.all_products');
    $breadcrumbs = [['url' => null, 'label' => $catalogPageTitle]];
@endphp

@section('title')
    @if(isset($seogenData))
        <title>{{ $seogenData->html_title_tag }}</title>
        <meta name="title" content="{{ $seogenData->meta_title_tag }}">
        <meta name="description" content="{{ $seogenData->meta_description_tag }}">
        <meta name="keywords" content="{{ $seogenData->meta_keywords_tag }}">
    @elseif(isset($filterGroup))
        <title>{{ $filterGroup->title_tag ?: $catalogPageTitle }}</title>
        @if($filterGroup->meta_title)<meta name="title" content="{{ $filterGroup->meta_title }}">@endif
        @if($filterGroup->meta_description)<meta name="description" content="{{ $filterGroup->meta_description }}">@endif
        @if($filterGroup->meta_keywords)<meta name="keywords" content="{{ $filterGroup->meta_keywords }}">@endif
    @else
        <title>{{ $catalogPageTitle.' — '.trans('base.site_title') }}</title>
    @endif
    <meta property="og:title" content="{{ $catalogPageTitle.' — '.trans('base.site_title') }}">
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
