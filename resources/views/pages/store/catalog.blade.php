@extends('layouts.store-main')

@section('title')
    @if (isset($seogenData))
        <title>{{ $seogenData->html_title_tag }}</title>
        <meta name="title" content="{{ $seogenData->meta_title_tag }}">
        <meta name="description" content="{{ $seogenData->meta_description_tag }}">
        <meta name="keywords" content="{{ $seogenData->meta_keywords_tag }}">
    @elseif(isset($filterGroup))
        <title>{{ $filterGroup->title_tag }}</title>
        <meta name="title" content="{{ $filterGroup->meta_title }}">
        <meta name="description" content="{{ $filterGroup->meta_description }}">
        <meta name="keywords" content="{{ $filterGroup->meta_keywords }}">
    @else
        <title>{{ config('app.name') . ' - ' . trans('base.catalog') }}</title>
        <meta name="title" content="{{ $productType->meta_title }}">
        <meta name="description" content="{{ $productType->meta_description }}">
        <meta name="keywords" content="{{ $productType->meta_keywords }}">
    @endif
@endsection

@section('content')
    <main id="arсhive-catalog" class="arсhive-catalog">
        <div class="content">
            <article>
                <div class="entry-content">
                    <div id="b-breadcrumbs" class="b-breadcrumbs">
                        <div class="container">
                            <div class="row">
                                <div class="col mt-4 mt-md-0 mb-4">
                                    <nav aria-label="breadcrumb">
                                        <ul class="breadcrumb mb-0" id="breadcrumblist" itemscope itemtype="https://schema.org/BreadcrumbList">
                                            <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}">Malina Design Studio</a>
                                                <meta itemprop="position" content="1"/>
                                            </li>
                                            <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => $productType->slug]) }}">{{ trans('base.catalog') }}</a>
                                                <meta itemprop="position" content="2"/>
                                            </li>
                                            @isset($selectedCategory)
                                                <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                                                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => $productType->slug]) }}">{{ $productType->name }}</a>
                                                    <meta itemprop="position" content="3"/>
                                                </li>
                                                <li class="breadcrumb-item active" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"
                                                    aria-current="page">
                                                    {{ $selectedCategory->name }}
                                                    <meta itemprop="position" content="4"/>
                                                </li>
                                            @else
                                                <li class="breadcrumb-item active" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"
                                                    aria-current="page">
                                                    {{ $productType->name }}
                                                    <meta itemprop="position" content="3"/>
                                                </li>
                                            @endisset

                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="archive-catalog-filter-top">
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <h1 class="archive-catalog-title head mb-4 mt-6">
                                        @isset($selectedCategory)
                                            @if (isset($seogenData))
                                                {{ $seogenData->html_h1_tag }}
                                            @else
                                                {{ $selectedCategory->name }}
                                            @endif
                                        @elseif(isset($filterGroup))
                                            {{ $filterGroup->name }}
                                        @else
                                            {{ $productType->name }}
                                        @endisset
                                    </h1>
                                </div>
                            </div>
                            <div class="d-flex flex-column-reverse flex-md-column">
                                <div class="row align-items-lg-center mb-8 mb-md-4">
                                    <div class="col-12 col-md-5 col-lg-4 col-xl-3">
                                        <div class="nav nav-pills mb-3 mb-md-0" id="pills-tab" role="tablist">
                                            <a class="btn btn-filter active"
                                                    id="archive-catalog-main-tab" data-toggle="pill"
                                                    href="#archive-catalog-main" role="tab"
                                                    aria-controls="archive-catalog-main" aria-selected="true">
                                                <span class="btn-filter-svg">
                                                    <svg>
                                                        <use
                                                            xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-filter"></use>
                                                    </svg>
                                                </span>
                                                <span class="btn-filter-text">{{ trans('base.extended_filter') }}</span>
                                            </a>
                                            <a class="btn btn-filter"
                                                    id="archive-catalog-filter-full-tab" data-toggle="pill"
                                                    href="#archive-catalog-filter-full" role="tab"
                                                    aria-controls="archive-catalog-filter-full" aria-selected="false">
                                                <span class="btn-filter-svg">
                                                    <svg>
                                                        <use
                                                            xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-filter"></use>
                                                    </svg>
                                                </span>
                                                <span class="btn-filter-text">{{ trans('base.extended_filter') }}</span>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-7 col-lg-8 col-xl-9 menu-right-dropdown">
                                        <div class="row flex-column flex-lg-row">
                                            <div class="col">
                                                <div class="dropdown dropdown-custom mb-1 mb-md-0">
                                                    <button
                                                        class="btn btn-dropdown dropdown-toggle d-block mx-auto ml-md-auto mr-md-0 ml-lg-0"
                                                        type="button" id="dropdownMenuButton" data-toggle="dropdown"
                                                        aria-haspopup="true" aria-expanded="false">
                                                        <span class="text-left">{{ trans('base.sort_by') }}:</span>
                                                        @isset($filtersData['sort_by'])
                                                            <span
                                                                class="text-right">{{ App\DataClasses\ProductSortOptionsDataClass::get()->where('id', $filtersData['sort_by'])->first()['name'] }}</span>
                                                        @else
                                                            <span
                                                                class="text-right">{{ App\DataClasses\ProductSortOptionsDataClass::get()->where('is_active_by_default')->first()['name'] }}</span>
                                                        @endisset

                                                    </button>
                                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                        @foreach(App\DataClasses\ProductSortOptionsDataClass::get() as $sortFilter)
                                                            <a class="dropdown-item sort-by-option @if(!isset($filtersData['sort_by']) && $sortFilter['is_active_by_default'] || isset($filtersData['sort_by']) && $filtersData['sort_by'] == $sortFilter['id']) active @endif"
                                                               href="#"
                                                               id="{{ $sortFilter['id'] }}">{{ $sortFilter['name'] }}</a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="dropdown dropdown-custom">
                                                    <button
                                                        class="btn btn-dropdown d-block mx-auto ml-md-auto mr-md-0 dropdown-toggle"
                                                        type="button" id="dropdownMenuButton2" data-toggle="dropdown"
                                                        aria-haspopup="true" aria-expanded="false">
                                                        <span
                                                            class="text-left">{{ trans('base.show_items_per_page') }}:</span>
                                                        @isset($filtersData['per_page'])
                                                            <span
                                                                class="text-right">{{ $filtersData['per_page'] }}</span>
                                                        @else
                                                            <span class="text-right">24</span>
                                                        @endisset
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right"
                                                         aria-labelledby="dropdownMenuButton2">
                                                        <a class="dropdown-item @if(!isset($filtersData['per_page']) || (isset($filtersData['per_page']) && $filtersData['per_page'] == 24)) active @endif"
                                                           href="#"
                                                           id="show-24-items-per-page">24 {{ trans('base.per_page') }}</a>
                                                        <a class="dropdown-item @if(isset($filtersData['per_page']) && $filtersData['per_page'] == 36) active @endif"
                                                           href="#"
                                                           id="show-36-items-per-page">36 {{ trans('base.per_page') }}</a>
                                                        <a class="dropdown-item @if(isset($filtersData['per_page']) && $filtersData['per_page'] == 48) active @endif"
                                                           href="#"
                                                           id="show-48-items-per-page">48 {{ trans('base.per_page') }}</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="catalog-category-slider mb-4">
                                            <div id="category-swiper-inner" class="inner rounded">
                                                <div id="category-swiper-body"
                                                     class="swiper-catalog-category invisible">
                                                    <div class="swiper-wrapper">
                                                        @foreach($categories as $category)
                                                            @if ($category->count_of_products > 0)
                                                                <div class="swiper-slide">
                                                                    <div class="slide-category">
                                                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog-category.page', ['productTypeSlug' => $productType->slug, 'categorySlug' => $category->slug]) }}"
                                                                           class="slide-category-link d-flex flex-column flex-sm-row align-items-center">
                                                                            <span
                                                                                class="slide-category-image mr-1 overflow-hidden d-flex align-items-center justify-content-center">
                                                                                <img src="{{ $category->image_url }}"
                                                                                     alt="image">
                                                                            </span>
                                                                            <span
                                                                                class="slide-category-info d-block text-center text-sm-left">
                                                                                <span
                                                                                    class="slide-category-name d-block">
                                                                                    {{ $category->name }}
                                                                                </span>
                                                                                <span
                                                                                    class="slide-category-options d-block">
                                                                                    {{ trans('base.products_by_category') }}: {{ $category->count_of_products }}
                                                                                </span>
												                            </span>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div id="category-swiper-controls" class="swiper-control invisible">
                                                    <div class="button-slider-prev">
                                                        <svg>
                                                            <use
                                                                xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-arrow-circle"></use>
                                                        </svg>
                                                    </div>
                                                    <div class="swiper-pagination"></div>
                                                    <div class="button-slider-next">
                                                        <svg>
                                                            <use
                                                                xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-arrow-circle"></use>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="container">
                        <div class="tab-content mb-12 mb-md-24" id="pills-tabContent">
                            <div class="archive-catalog-filter-full tab-pane fade" id="archive-catalog-filter-full"
                                 role="tabpanel" aria-labelledby="archive-catalog-filter-full-tab">
                                <form id="filter-full-form">
                                    <div class="row">
                                        <div class="col">
                                            <div class="search-filter mb-10 mb-md-6 mt-n4 mt-md-0">
                                                <div class="input-search">
                                                    <input name="search" type="search"
                                                           placeholder="{{ trans('base.search_by_article_or_name') }}"
                                                           autocomplete="off" @if(isset($filtersData['search'])) value="{{ $filtersData['search'] }}" @endif>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="filter-item filter-item--price d-lg-none">
                                        <div class="row">
                                            <div class="col">
                                                <div class="filter-title mb-4">
                                                    {{ trans('base.price') }}
                                                </div>
                                                <div id="price-slider-full-m"
                                                     class="price-slider-full-m slider-range mb-3">
                                                    <div class="currency-wrap">
                                                        <div class="input-currency">
                                                            <span
                                                                class="currency">{{ $baseCurrency->name_short }}</span>
                                                            <input id="currency-first-full-m"
                                                                   class="currency-first-full-m sync-input"
                                                                   type="number" name="price_from"
                                                                   @isset($filtersData['price_from']) value="{{ $filtersData['price_from'] }}"
                                                                   @endisset min="0" max="10000" step="1"
                                                                   placeholder="0">
                                                        </div>
                                                        <div class="input-currency">
                                                            <input id="currency-last-full-m"
                                                                   class="currency-last-full-m sync-input" type="number"
                                                                   name="price_to"
                                                                   @isset($filtersData['price_to']) value="{{ $filtersData['price_to'] }}"
                                                                   @endisset min="0" max="10000" step="1"
                                                                   placeholder="10000">
                                                        </div>
                                                    </div>
                                                    <div class="rangeBar-full"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-lg-13">
                                        <div class="col-12 col-lg-3 order-2 order-lg-1 mb-10 mb-lg-0">
                                            @if(count($filters['full']['left']))
                                                <div class="filter-item filter-item--brands mb-6 pb-6">
                                                    <div class="filter-title mb-4">
                                                        {{ $filters['full']['left'][0]->pivot->filter_name }}
                                                    </div>
                                                    <div class="fstElement fstMultipleMode">
                                                        <div class="fstControls">
                                                            <input class="fstQueryInput search-input"
                                                                   placeholder="{{ $filters['full']['left'][0]->pivot->filter_name }}">
                                                        </div>
                                                    </div>
                                                    <div class="position-relative checkbox-preview-wrap">
                                                        <div class="brands mt-3">
                                                            <div class="brand">
                                                                @foreach($filters['full']['left'][0]->options as $option)
                                                                    <div class="checkbox-preview" data-toggle="tooltip">
                                                                        <div
                                                                            class="custom-control custom-checkbox position-relative @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, $filters['full']['left'][0]->slug, $option->slug)) checked @endif">
                                                                            <input type="checkbox"
                                                                                   class="custom-control-input sync-input"
                                                                                   id="custom-field-checkbox-{{ $filters['full']['left'][0]->id }}-{{ $option->id }}-full"
                                                                                   name="{{ $filters['full']['left'][0]->slug }}"
                                                                                   @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, $filters['full']['left'][0]->slug, $option->slug)) checked
                                                                                   @endif
                                                                                   value="{{ $option->slug }}">
                                                                            <label
                                                                                class="custom-control-label sync-input"
                                                                                for="custom-field-checkbox-{{ $filters['full']['left'][0]->id }}-{{ $option->id }}-full">{{ $option->name }}</label>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-12 col-lg-6 order-3 order-lg-2">
                                            <div class="filter-item filter-item--price d-none d-lg-block">
                                                <div class="filter-title mb-4">
                                                    {{ trans('base.price') }}
                                                </div>
                                                <div class="row">
                                                    <div class="col col-xl-12">
                                                        <div class="position-relative">
                                                            <div id="price-slider-full"
                                                                 class="price-slider slider-range mb-6">
                                                                <div class="currency-wrap">
                                                                    <div
                                                                        class="currency-content d-flex align-items-center">
                                                                        <div
                                                                            class="text mr-5">{{ trans('base.from') }}</div>
                                                                        <div class="input-currency">
                                                                            <span
                                                                                class="currency">{{ $baseCurrency->name_short }}</span>
                                                                            <input id="currency-first-full"
                                                                                   class="currency-first-full sync-input"
                                                                                   type="number" name="price_from"
                                                                                   @isset($filtersData['price_from']) value="{{ $filtersData['price_from'] }}"
                                                                                   @endisset min="0" max="10000"
                                                                                   step="1" placeholder="0">
                                                                        </div>
                                                                    </div>
                                                                    <div
                                                                        class="currency-content d-flex align-items-center">
                                                                        <div
                                                                            class="text mr-5">{{ trans('base.to') }}</div>
                                                                        <div class="input-currency">
                                                                            <span
                                                                                class="currency">{{ $baseCurrency->name_short }}</span>
                                                                            <input id="currency-last-full"
                                                                                   class="currency-last-full sync-input"
                                                                                   type="number" name="price_to"
                                                                                   @isset($filtersData['price_to']) value="{{ $filtersData['price_to'] }}"
                                                                                   @endisset min="0" max="10000"
                                                                                   step="1" placeholder="10000">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="rangeBar-full"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @if($productType->has_color)
                                                <div class="filter-item filter-item--colors mb-6 pb-3">
                                                    <div class="filter-title pb-1">
                                                        {{ trans('base.color') }}
                                                    </div>
                                                    <div class="d-flex flex-wrap colors-wrapper">
                                                        @foreach($colors->whereNull('parent_color_id') as $color)
                                                            <div
                                                                class="color-wrapper d-flex align-items-center justify-content-center @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, 'color', $color->slug)) checked @endif">
                                                                <input class="sync-input" type="checkbox" name="color"
                                                                       id="color-checkbox-{{$color->id}}-full"
                                                                       value="{{ $color->slug }}" style="display: none;"
                                                                       @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, 'color', $color->slug)) checked @endif>
                                                                <label for="color-checkbox-{{$color->id}}-full"
                                                                       class="link-color @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, 'color', $color->slug)) active  @endif"
                                                                       @if(!$color->hex)
                                                                           style="background:linear-gradient(90deg, rgba(255,0,0,1) 0%, rgba(255,235,0,1) 37%, rgba(5,255,0,1) 74%, rgba(59,63,250,1) 100%, rgba(0,9,255,1) 100%);"
                                                                       @else
                                                                           style="background-color: {{$color->hex}};"
                                                                       @endif

                                                                       data-toggle="tooltip"
                                                                       title="<span class='help'>{{ $color->name }}</span>"><span
                                                                        class="before border-silver-custom"></span></label>
                                                            </div>
                                                            @foreach($color->children as $childColor)
                                                                <div
                                                                    class="color-wrapper d-flex align-items-center justify-content-center @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, 'color', $childColor->slug)) checked @endif">
                                                                    <input class="sync-input" type="checkbox" name="color"
                                                                           id="color-checkbox-{{$childColor->id}}-full"
                                                                           value="{{ $childColor->slug }}" style="display: none;"
                                                                           @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, 'color', $childColor->slug)) checked @endif>
                                                                    <label for="color-checkbox-{{$childColor->id}}-full"
                                                                           class="link-color @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, 'color', $childColor->slug)) active  @endif"
                                                                           @if(!$childColor->hex)
                                                                               style="background:linear-gradient(90deg, rgba(255,0,0,1) 0%, rgba(255,235,0,1) 37%, rgba(5,255,0,1) 74%, rgba(59,63,250,1) 100%, rgba(0,9,255,1) 100%);"
                                                                           @else
                                                                               style="background-color: {{$childColor->hex}};"
                                                                           @endif

                                                                           data-toggle="tooltip"
                                                                           title="<span class='help'>{{ $childColor->name }}</span>"><span
                                                                            class="before border-silver-custom"></span></label>
                                                                </div>
                                                            @endforeach
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            @if($productType->has_size)
                                                @foreach(\App\DataClasses\ProductSizeTypesDataClass::get()->pluck('id') as $productSizeType)
                                                    @php
                                                        $productSizeType = mb_strtolower($productSizeType);
                                                    @endphp
                                                    @if($productType['has_' . $productSizeType] && $productType['filter_by_' . $productSizeType] && $productType['product_size_' . $productSizeType . '_show_on_main_filter'])
                                                        <div class="filter-item filter-item--type-custom pb-3 mb-6">
                                                            <div class="filter-title mb-4">
                                                                {{ $productType['product_size_' . $productSizeType . '_filter_name'] }}
                                                            </div>
                                                            @if($productType['product_size_' . $productSizeType . '_filter_type_id'] === \App\DataClasses\NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_FROM_TO_INPUTS_TYPE)
                                                                <div class="px-4 position-relative">
                                                                    <div class="price-slider slider-range mb-3">
                                                                        <div class="currency-wrap">
                                                                            <div class="input-currency mb-3">
                                                                                <span
                                                                                    class="currency">{{ trans('base.from') }}</span>
                                                                                <input id="currency-first-main"
                                                                                       class="product-{{$productSizeType}}-from-full sync-input"
                                                                                       type="number"
                                                                                       min="0"
                                                                                       name="product_{{ $productSizeType }}_from"
                                                                                       @isset($filtersData['product_' .  $productSizeType . '_from']) value="{{ $filtersData['product_' .  $productSizeType . '_from'] }}"
                                                                                       @endisset placeholder="{{ trans('base.from') }}">
                                                                            </div>
                                                                            <div class="input-currency">
                                                                                <span
                                                                                    class="currency">{{ trans('base.to') }}</span>
                                                                                <input id="currency-last-main"
                                                                                       class="product-{{$productSizeType}}-to-full sync-input"
                                                                                       type="number"
                                                                                       max="0"
                                                                                       name="product_{{ $productSizeType }}_to"
                                                                                       @isset($filtersData['product_' .  $productSizeType . '_to']) value="{{ $filtersData['product_' .  $productSizeType . '_to'] }}"
                                                                                       @endisset placeholder="{{ trans('base.to') }}">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @elseif($productType['product_size_' . $productSizeType . '_filter_type_id'] === \App\DataClasses\NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_OPTIONS_TYPE)
                                                                @foreach($productType->sizeFilterOptions->where('type', mb_strtoupper($productSizeType)) as $option)
                                                                    <div class="checkbox-preview" data-toggle="tooltip">
                                                                        <div
                                                                            class="custom-control custom-checkbox position-relative @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, 'product_'. $productSizeType, $option->slug)) checked @endif">
                                                                            <input type="checkbox"
                                                                                   class="custom-control-input sync-input"
                                                                                   id="custom-field-checkbox-{{$productSizeType}}-{{$option->id}}-size-full"
                                                                                   name="product_{{ $productSizeType }}"
                                                                                   value="{{ $option->slug }}"
                                                                                   @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, 'product_'. $productSizeType, $option->slug)) checked @endif>
                                                                            <label class="custom-control-label"
                                                                                   for="custom-field-checkbox-{{$productSizeType}}-{{$option->id}}-size-full">{{ $option->name }}</label>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    @endif

                                                @endforeach
                                            @endif

                                            @if(count($filters['full']['middle']))
                                                @foreach($filters['full']['middle'] as $index => $productField)
                                                    <div class="filter-item filter-item--type-custom pb-3 mb-6">
                                                        <div class="filter-title mb-4">
                                                            {{ $productField->pivot->filter_name }}
                                                        </div>
                                                        <div class="position-relative checkbox-preview-wrap">
                                                            @if($productField->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION)
                                                                @foreach($productField->options as $option)
                                                                    <div class="checkbox-preview" data-toggle="tooltip">
                                                                        <div
                                                                            class="custom-control custom-checkbox position-relative @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, $productField->slug, $option->slug)) checked @endif">
                                                                            <input type="checkbox"
                                                                                   class="custom-control-input sync-input"
                                                                                   id="custom-field-checkbox-{{$productField->id}}-{{$option->id}}-full"
                                                                                   name="{{ $productField->slug }}"
                                                                                   value="{{ $option->slug }}"
                                                                                   @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, $productField->slug, $option->slug)) checked @endif>
                                                                            <label class="custom-control-label"
                                                                                   for="custom-field-checkbox-{{$productField->id}}-{{$option->id}}-full">{{ $option->name }}</label>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            @elseif($productField->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_NUMBER || $productField->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_SIZE)
                                                                @if($productField->numeric_field_filter_type_id === \App\DataClasses\NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_FROM_TO_INPUTS_TYPE)
                                                                    <div class="px-4 position-relative">
                                                                        <div class="price-slider slider-range mb-3">
                                                                            <div class="currency-wrap">
                                                                                <div class="input-currency mb-3">
                                                                                <span
                                                                                    class="currency">{{ trans('base.from') }}</span>
                                                                                    <input
                                                                                        id="product-{{$productField->id}}-from-full"
                                                                                           class="product-{{$productField->id}}-from-full sync-input"
                                                                                           type="number"
                                                                                           name="{{$productField->slug}}_from"
                                                                                           @isset($filtersData[$productField->slug . '_from']) value="{{ $filtersData[$productField->slug . '_from'] }}"
                                                                                           @endisset placeholder="{{ trans('base.from') }}">
                                                                                </div>
                                                                                <div class="input-currency">
                                                                                <span
                                                                                    class="currency">{{ trans('base.to') }}</span>
                                                                                    <input
                                                                                        id="product-{{$productField->id}}-to-full"
                                                                                           class="product-{{$productField->id}}-to-full sync-input"
                                                                                           type="number"
                                                                                           name="{{$productField->slug}}_to"
                                                                                           @isset($filtersData[$productField->slug . '_to']) value="{{ $filtersData[$productField->slug . '_to'] }}"
                                                                                           @endisset placeholder="{{ trans('base.to') }}">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @elseif($productField->numeric_field_filter_type_id === \App\DataClasses\NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_OPTIONS_TYPE)
                                                                    @foreach($productField->fieldFilterOptions as $filterOption)
                                                                        <div class="checkbox-preview" data-toggle="tooltip">
                                                                            <div
                                                                                class="custom-control custom-checkbox position-relative @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, $productField->slug, $filterOption->slug)) checked @endif">
                                                                                <input type="checkbox"
                                                                                       class="custom-control-input sync-input"
                                                                                       id="custom-field-checkbox-{{$productField->id}}-{{$filterOption->id}}-full"
                                                                                       name="{{ $productField->slug }}"
                                                                                       value="{{ $filterOption->slug }}"
                                                                                       @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, $productField->slug, $filterOption->slug)) checked @endif>
                                                                                <label class="custom-control-label"
                                                                                       for="custom-field-checkbox-{{$productField->id}}-{{$filterOption->id}}-full">{{ $filterOption->name }}</label>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                            <div class="filter-item filter-item--countries mb-6 pb-3">
                                                <div class="filter-title mb-4">
                                                    {{ trans('base.filter_by_country') }}
                                                </div>
                                                @foreach($countries as $country)
                                                    <div
                                                        class="custom-control custom-checkbox position-relative @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, 'country', $country->code)) checked @endif">
                                                        <input type="checkbox" class="custom-control-input sync-input"
                                                               id="custom-field-checkbox-country-{{$country->id}}-full"
                                                               name="country" value="{{ $country->code }}"
                                                               @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, 'country', $country->code)) checked @endif>
                                                        <label class="custom-control-label"
                                                               for="custom-field-checkbox-country-{{$country->id}}-full"><img
                                                                src="{{ $country->image_url }}"
                                                                alt="country">{{ $country->name }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-12 col-lg-3 order-1 order-lg-3 mb-10 mb-lg-0">
                                            @if($productType->has_brand)
                                                <div class="filter-item filter-item--brands mb-6 pb-6">
                                                    <div class="filter-title mb-4">
                                                        {{ trans('base.brands') }}
                                                    </div>
                                                    <div class="fstElement fstMultipleMode">
                                                        <div class="fstControls">
                                                            <input class="fstQueryInput search-input"
                                                                   placeholder="{{ trans('base.filter_by_brands') }}">
                                                        </div>
                                                    </div>
                                                    <div class="brands content-checkoxs show-more mt-3">

                                                        @foreach($brandsSortedByFirstLetter as $brandFirstLetter => $brands)
                                                            <div class="brand">
                                                                <div
                                                                    class="brand-letter option-letter mb-4 mt-5">{{ $brandFirstLetter }}</div>
                                                                @foreach($brands as $brand)
                                                                    <div class="checkbox-preview" data-toggle="tooltip">
                                                                        <div
                                                                            class="custom-control custom-checkbox position-relative @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, 'brand', $brand->slug)) checked @endif">
                                                                            <input type="checkbox"
                                                                                   class="custom-control-input sync-input"
                                                                                   id="custom-field-checkbox-brand-{{ $brand->id }}-full"
                                                                                   name="brand"
                                                                                   value="{{ $brand->slug }}"
                                                                                   @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, 'brand', $brand->slug)) checked @endif>
                                                                            <label class="custom-control-label"
                                                                                   for="custom-field-checkbox-brand-{{ $brand->id }}-full">{{ $brand->name }}</label>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endforeach


                                                    </div>

                                                    <div
                                                        class="btn-show-more">{{ trans('base.filter_show_more') }}</div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row filter-bottom">
                                        @if(count($selectedFiltersOptions))
                                            <div class="col">
                                                <div class="title py-3">{{ trans('base.you_viewing') }}</div>
                                                <div class="filter-views mb-4">
                                                    <div class="filter-views-content">
                                                        @foreach($selectedFiltersOptions as $selectedFilterOption)
                                                            <div class="filter-view-item filter-delete"
                                                                 id="{{ $selectedFilterOption['filter_slug'] }}={{ $selectedFilterOption['option_slug'] }}">
                                                                <div
                                                                    class="content">{{ $selectedFilterOption['option_name'] }}</div>
                                                                <div class="i-close">
                                                                    <svg>
                                                                        <use
                                                                            xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-close"></use>
                                                                    </svg>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <div class="swiper-scrollbar d-none"></div>
                                                </div>
                                            </div>
                                            <div class="w-100"></div>
                                            <div class="col">
                                                <div class="full-filter-result mb-4">{{ trans('base.filter_found') }}:
                                                    <strong>{{ $productsPaginated->total() }} {{ trans('base.filter_options')  }}</strong></div>
                                            </div>
                                            <div class="w-100"></div>
                                        @endif
                                        <div class="col">
                                            <div
                                                class="full-result-buttons d-flex flex-column flex-md-row align-items-center">
                                                <button type="button"
                                                        class="btn btn-show-result btn-dark mr-md-2 mb-2 mb-md-0 filter-submit-full">{{ trans('base.filter_show') }}</button>
                                                <button type="button"
                                                        class="btn btn-clear-result btn-outline-black filter-reset">{{ trans('base.filter_reset') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="archive-catalog-main tab-pane fade show active" id="archive-catalog-main"
                                 role="tabpanel" aria-labelledby="archive-catalog-main-tab">
                                <div class="row">
                                    <div class="col-12 col-md-5 col-lg-4 col-xl-3">
                                        <!-- b-archive-catalog-filter-left -->

                                        <form id="filter-left-form">
                                            <div class="archive-catalog-filter-left bg-white">
                                                @if(count($filters['main']))
                                                    <div class="filter-item filter-item--type-custom pb-3 mb-6">
                                                        <div class="filter-title mb-4">
                                                            {{ $filters['main'][0]->pivot->filter_name }}
                                                        </div>
                                                        <div class="position-relative checkbox-preview-wrap">
                                                            @if($filters['main'][0]->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION)
                                                                @foreach($filters['main'][0]->options as $option)
                                                                    <div class="checkbox-preview" data-toggle="tooltip">
                                                                        <div
                                                                            class="custom-control custom-checkbox position-relative @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, $filters['main'][0]->slug, $option->slug)) checked @endif">
                                                                            <input type="checkbox"
                                                                                   class="custom-control-input sync-input"
                                                                                   id="custom-field-checkbox-{{$filters['main'][0]->id}}-{{$option->id}}-main"
                                                                                   name="{{ $filters['main'][0]->slug }}"
                                                                                   value="{{ $option->slug }}"
                                                                                   @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, $filters['main'][0]->slug, $option->slug)) checked @endif>
                                                                            <label class="custom-control-label"
                                                                                   for="custom-field-checkbox-{{$filters['main'][0]->id}}-{{$option->id}}-main">{{ $option->name }}</label>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            @elseif($filters['main'][0]->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_NUMBER || $filters['main'][0]->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_SIZE)
                                                                @foreach($filters['main'][0]->fieldFilterOptions as $filterOption)
                                                                    <div class="checkbox-preview" data-toggle="tooltip">
                                                                        <div
                                                                            class="custom-control custom-checkbox position-relative @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, $filters['main'][0]->slug, $filterOption->slug)) checked @endif">
                                                                            <input type="checkbox"
                                                                                   class="custom-control-input sync-input"
                                                                                   id="custom-field-checkbox-{{$filters['main'][0]->id}}-{{$filterOption->id}}-main"
                                                                                   name="{{ $filters['main'][0]->slug }}"
                                                                                   value="{{ $filterOption->slug }}"
                                                                                   @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, $filters['main'][0]->slug, $filterOption->slug)) checked @endif>
                                                                            <label class="custom-control-label"
                                                                                   for="custom-field-checkbox-{{$filters['main'][0]->id}}-{{$filterOption->id}}-main">{{ $filterOption->name }}</label>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="filter-item filter-item--price mb-6 pb-6">
                                                    <div class="filter-title">
                                                        {{ trans('base.price') }}
                                                    </div>
                                                    <div class="px-4 position-relative">
                                                        <div id="price-slider" class="price-slider slider-range mb-3">
                                                            <div class="currency-wrap">
                                                                <div class="input-currency mb-3">
                                                                    <span
                                                                        class="currency">{{ $baseCurrency->name_short }}</span>
                                                                    <input id="currency-first-main"
                                                                           class="currency-first-main sync-input"
                                                                           type="number"
                                                                           @isset($filtersData['price_from']) value="{{ $filtersData['price_from'] }}"
                                                                           @endisset min="0" max="10000" step="1"
                                                                           name="price_from" placeholder="0">
                                                                </div>
                                                                <div class="input-currency">
                                                                    <span
                                                                        class="currency">{{ $baseCurrency->name_short }}</span>
                                                                    <input id="currency-last-main"
                                                                           class="currency-last-main sync-input"
                                                                           type="number"
                                                                           @isset($filtersData['price_to']) value="{{ $filtersData['price_to'] }}"
                                                                           @endisset min="0" max="10000" step="1"
                                                                           name="price_to" placeholder="10000">
                                                                </div>
                                                            </div>
                                                            <div class="rangeBar-full"></div>
                                                        </div>
                                                        <button type="button"
                                                                class="btn btn-black-custom btn-block filter-submit-main">{{ trans('base.apply') }}</button>
                                                    </div>
                                                </div>
                                                @if($productType->has_color)
                                                    <div class="filter-item filter-item--colors mb-6 pb-3">
                                                        <div class="filter-title pb-1">
                                                            {{ trans('base.color') }}
                                                        </div>
                                                        <div class="d-flex flex-wrap colors-wrapper">
                                                            @foreach($colors->whereNull('parent_color_id') as $color)
                                                                <div
                                                                    class="color-wrapper d-flex align-items-center justify-content-center @if(\App\Services\Product\ProductFiltersService::mainColorFilterOptionChecked($filtersData, 'color', $color)) checked @endif">
                                                                    <input class="sync-input" type="checkbox"
                                                                           name="color"
                                                                           id="color-checkbox-{{$color->id}}-main"
                                                                           value="{{ $color->slug }}"
                                                                           style="display: none;"
                                                                           @if(\App\Services\Product\ProductFiltersService::mainColorFilterOptionChecked($filtersData, 'color', $color)) checked @endif>
                                                                    <label for="color-checkbox-{{$color->id}}-main"
                                                                           class="link-color @if(\App\Services\Product\ProductFiltersService::mainColorFilterOptionChecked($filtersData, 'color', $color)) active @endif"
                                                                           @if(!$color->hex)
                                                                               style="background: linear-gradient(90deg, rgba(255,0,0,1) 0%, rgba(255,235,0,1) 37%, rgba(5,255,0,1) 74%, rgba(59,63,250,1) 100%, rgba(0,9,255,1) 100%);"
                                                                           @else
                                                                               style="background-color: {{$color->hex}};"
                                                                           @endif
                                                                           data-toggle="tooltip"
                                                                           title="<span class='help'>{{ $color->name }}</span>"><span
                                                                            class="before border-silver-custom"></span></label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                                @if($productType->has_brand)
                                                    <div class="filter-item filter-item--brands mb-6 pb-6">
                                                        <div class="filter-title mb-4">
                                                            {{ trans('base.brands') }}
                                                        </div>
                                                        <div class="fstElement fstMultipleMode">
                                                            <div class="fstControls">
                                                                <input class="fstQueryInput search-input"
                                                                       placeholder="{{ trans('base.filter_by_brands') }}">
                                                            </div>
                                                        </div>
                                                        <div class="position-relative checkbox-preview-wrap">
                                                            <div class="brands mt-3">
                                                                <div class="brand">
                                                                    @foreach($brandsSortedByFirstLetter as $brandFirstLetter => $brands)
                                                                        <div
                                                                            class="brand-letter option-letter mb-4 mt-5">{{ $brandFirstLetter }}</div>
                                                                        @foreach($brands as $brand)
                                                                            <div class="checkbox-preview"
                                                                                 data-toggle="tooltip">
                                                                                <div
                                                                                    class="custom-control custom-checkbox position-relative @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, 'brand', $brand->slug)) checked @endif">
                                                                                    <input type="checkbox"
                                                                                           class="custom-control-input sync-input"
                                                                                           id="custom-field-checkbox-brand-{{ $brand->id }}-main"
                                                                                           name="brand"
                                                                                           value="{{ $brand->slug }}"
                                                                                           @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, 'brand', $brand->slug)) checked @endif>
                                                                                    <label class="custom-control-label"
                                                                                           for="custom-field-checkbox-brand-{{ $brand->id }}-main">{{ $brand->name }}</label>
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if($productType->has_size)
                                                    @foreach(\App\DataClasses\ProductSizeTypesDataClass::get()->pluck('id') as $productSizeType)
                                                        @php
                                                            $productSizeType = mb_strtolower($productSizeType);
                                                        @endphp
                                                        @if($productType['has_' . $productSizeType] && $productType['filter_by_' . $productSizeType] && $productType['product_size_' . $productSizeType . '_show_on_main_filter'])
                                                            <div class="filter-item filter-item--type-custom pb-3 mb-6">
                                                                <div class="filter-title mb-4">
                                                                    {{ $productType['product_size_' . $productSizeType . '_filter_name'] }}
                                                                </div>
                                                                @if($productType['product_size_' . $productSizeType . '_filter_type_id'] === \App\DataClasses\NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_FROM_TO_INPUTS_TYPE)
                                                                    <div class="px-4 position-relative">
                                                                        <div class="price-slider slider-range mb-3">
                                                                            <div class="currency-wrap">
                                                                                <div class="input-currency mb-3">
                                                                                    <span
                                                                                        class="currency">{{ trans('base.from') }}</span>
                                                                                    <input id="currency-first-main"
                                                                                           class="product-{{$productSizeType}}-from-main sync-input"
                                                                                           type="number"
                                                                                           name="product_{{ $productSizeType }}_from"
                                                                                           @isset($filtersData['product_' .  $productSizeType . '_from']) value="{{ $filtersData['product_' .  $productSizeType . '_from'] }}"
                                                                                           @endisset placeholder="{{ trans('base.from') }}">
                                                                                </div>
                                                                                <div class="input-currency">
                                                                                    <span
                                                                                        class="currency">{{ trans('base.to') }}</span>
                                                                                    <input id="currency-last-main"
                                                                                           class="product-{{$productSizeType}}-to-main sync-input"
                                                                                           type="number"
                                                                                           name="product_{{ $productSizeType }}_to"
                                                                                           @isset($filtersData['product_' .  $productSizeType . '_to']) value="{{ $filtersData['product_' .  $productSizeType . '_to'] }}"
                                                                                           @endisset placeholder="{{ trans('base.to') }}">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <button type="button"
                                                                                class="btn btn-black-custom btn-block filter-submit-main">{{ trans('base.apply') }}</button>
                                                                    </div>
                                                                @elseif($productType['product_size_' . $productSizeType . '_filter_type_id'] === \App\DataClasses\NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_OPTIONS_TYPE)
                                                                    @foreach($productType->sizeFilterOptions->where('type', mb_strtoupper($productSizeType)) as $option)
                                                                        <div class="checkbox-preview"
                                                                             data-toggle="tooltip">
                                                                            <div
                                                                                class="custom-control custom-checkbox position-relative @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, 'product_'. $productSizeType, $option->slug)) checked @endif">
                                                                                <input type="checkbox"
                                                                                       class="custom-control-input sync-input"
                                                                                       id="custom-field-checkbox-{{$productSizeType}}-{{$option->id}}-size-main"
                                                                                       name="product_{{ $productSizeType }}"
                                                                                       value="{{ $option->slug }}"
                                                                                       @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, 'product_'. $productSizeType, $option->slug)) checked @endif>
                                                                                <label class="custom-control-label"
                                                                                       for="custom-field-checkbox-{{$productSizeType}}-{{$option->id}}-size-main">{{ $option->name }}</label>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                @endif
                                                            </div>
                                                        @endif

                                                    @endforeach
                                                @endif

                                                @if(count($filters['main']) > 1)
                                                    @foreach($filters['main'] as $index => $productField)
                                                        @if($index == 0)
                                                            @continue
                                                        @endif
                                                        <div class="filter-item filter-item--type-custom pb-3 mb-6">
                                                            <div class="filter-title mb-4">
                                                                {{ $productField->pivot->filter_name }}
                                                            </div>
                                                            <div class="position-relative checkbox-preview-wrap">
                                                                @if($productField->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION)
                                                                    @foreach($productField->options as $option)
                                                                        <div class="checkbox-preview"
                                                                             data-toggle="tooltip">
                                                                            <div
                                                                                class="custom-control custom-checkbox position-relative @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, $productField->slug, $option->slug)) checked @endif">
                                                                                <input type="checkbox"
                                                                                       class="custom-control-input sync-input"
                                                                                       id="custom-field-checkbox-{{$productField->id}}-{{$option->id}}-main"
                                                                                       name="{{ $productField->slug }}"
                                                                                       value="{{ $option->slug }}"
                                                                                       @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, $productField->slug, $option->slug)) checked @endif>
                                                                                <label class="custom-control-label"
                                                                                       for="custom-field-checkbox-{{$productField->id}}-{{$option->id}}-main">{{ $option->name }}</label>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                @elseif($productField->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_NUMBER || $productField->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_SIZE)
                                                                    @if($productField->numeric_field_filter_type_id === \App\DataClasses\NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_FROM_TO_INPUTS_TYPE)
                                                                        <div class="px-4 position-relative">
                                                                            <div class="price-slider slider-range mb-3">
                                                                                <div class="currency-wrap">
                                                                                    <div class="input-currency mb-3">
                                                                                <span
                                                                                    class="currency">{{ trans('base.from') }}</span>
                                                                                        <input
                                                                                            id="product-{{$productField->id}}-from-main"
                                                                                            class="product-{{$productField->id}}-from-main sync-input"
                                                                                            type="number"
                                                                                            name="{{$productField->slug}}_from"
                                                                                            @isset($filtersData[$productField->slug . '_from']) value="{{ $filtersData[$productField->slug . '_from'] }}"
                                                                                            @endisset placeholder="{{ trans('base.from') }}">
                                                                                    </div>
                                                                                    <div class="input-currency">
                                                                                <span
                                                                                    class="currency">{{ trans('base.to') }}</span>
                                                                                        <input
                                                                                            id="product-{{$productField->id}}-to-main"
                                                                                            class="product-{{$productField->id}}-to-main sync-input"
                                                                                            type="number"
                                                                                            name="{{$productField->slug}}_to"
                                                                                            @isset($filtersData[$productField->slug . '_to']) value="{{ $filtersData[$productField->slug . '_to'] }}"
                                                                                            @endisset placeholder="{{ trans('base.to') }}">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @elseif($productField->numeric_field_filter_type_id === \App\DataClasses\NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_OPTIONS_TYPE)
                                                                        @foreach($productField->fieldFilterOptions as $filterOption)
                                                                            <div class="checkbox-preview"
                                                                                 data-toggle="tooltip">
                                                                                <div
                                                                                    class="custom-control custom-checkbox position-relative @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, $productField->slug, $filterOption->slug)) checked @endif">
                                                                                    <input type="checkbox"
                                                                                           class="custom-control-input sync-input"
                                                                                           id="custom-field-checkbox-{{$productField->id}}-{{$filterOption->id}}-main"
                                                                                           name="{{ $productField->slug }}"
                                                                                           value="{{ $filterOption->slug }}"
                                                                                           @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, $productField->slug, $filterOption->slug)) checked @endif>
                                                                                    <label class="custom-control-label"
                                                                                           for="custom-field-checkbox-{{$productField->id}}-{{$filterOption->id}}-main">{{ $filterOption->name }}</label>
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                                <div class="filter-item filter-item--countries mb-6 pb-3">
                                                    <div class="filter-title mb-4">
                                                        {{ trans('base.filter_by_country') }}
                                                    </div>
                                                    @foreach($countries as $country)
                                                        <div
                                                            class="custom-control custom-checkbox position-relative @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, 'country', $country->code)) checked @endif">
                                                            <input type="checkbox"
                                                                   class="custom-control-input sync-input"
                                                                   id="custom-field-checkbox-country-{{$country->id}}-main"
                                                                   name="country" value="{{ $country->code }}"
                                                                   @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, 'country', $country->code)) checked @endif>
                                                            <label class="custom-control-label"
                                                                   for="custom-field-checkbox-country-{{$country->id}}-main"><img
                                                                    src="{{ $country->image_url }}"
                                                                    alt="country">{{ $country->name }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <button type="button"
                                                        class="btn btn-show-result btn-dark btn-block mb-2 filter-submit-main">{{ trans('base.filter_show') }}</button>
                                                <button type="button"
                                                        class="btn btn-clear-result btn-outline-black btn-block filter-reset">{{ trans('base.filter_reset') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-12 col-md-7 col-lg-8 col-xl-9">
                                        <!-- b-archive-catalog-list-products -->

                                        <div id="archive-catalog-list-products" class="archive-catalog-list-products">
                                            <div class="cards-products">
                                                <div class="row">
                                                    @php
                                                        if (isset($filtersData['color'])) {
                                                            $colorIds = $colors->filter(fn($color) => in_array($color->slug, (is_array($filtersData['color']) ? $filtersData['color'] : [$filtersData['color']])))->pluck('id');
                                                        }
                                                    @endphp
                                                    @foreach($productsPaginated as $product)
                                                        @php
                                                            $productToShow = $product;
                                                        @endphp
                                                        <div class="col-6 col-lg-6 col-xl-4 mb-8 mb-lg-10 content">
                                                            <div class="card card-product">
                                                                <div class="card-content">
                                                                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $productToShow->slug]) }}"
                                                                       class="card-link">
                                                                    <span class="card-link-image">
                                                                        <img src="{{ $productToShow->preview_image_url }}"
                                                                             alt="product">

                                                                        @if ($productToShow->special_offers)
                                                                            <span class="card-link-container">
                                                                                @foreach($productToShow->special_offers as $specialOffer)
                                                                                    <span class="card-link-offer">{{ \App\DataClasses\ProductSpecialOfferOptionsDataClass::get($specialOffer)['name'] ?? '' }}</span>
                                                                                @endforeach
                                                                            </span>
                                                                        @endif

                                                                        @auth
                                                                            <span class="link-heart @if($wishListProducts->contains($productToShow->id)) link-heart-active @endif" id="{{ $productToShow->slug }}">
                                                                            <span>{{ trans('base.add_to_wish_list') }}</span>
                                                                            <svg>
                                                                                <use
                                                                                    xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-heart-hover"></use>
                                                                            </svg>
                                                                        </span>
                                                                        @endauth
                                                                    </span>
                                                                        <span class="card-link-title">
								                                        {{ $productToShow->name }}
							                                        </span>
                                                                        <span class="card-link-price">
                                                                            @if($productToShow->old_price)
                                                                                <span class="card-link-price--hot">{{ $productToShow->price }} {{ $baseCurrency->name_short }} </span> <span class="card-link-price--old">{{ $productToShow->old_price }} {{ $baseCurrency->name_short }}</span>
                                                                            @else
                                                                                {{ $productToShow->price }} {{ $baseCurrency->name_short }}
                                                                            @endif
                                                                            @if($productType->product_point_name)
                                                                                <span class="card-link-price--small">/ {{ $productType->product_point_name }}</span>
                                                                            @endif
							                                        </span>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                {{ $productsPaginated->links('pagination.store') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- .entry-content -->
            </article>
        </div>
    </main><!-- #main -->
@stop
@push('dynamic_scripts')
    <script>
        const catalog = {
            product_type_slug: '{{ $productType->slug }}',
            category_slug: '{{ isset($selectedCategory) ? $selectedCategory->slug : ''}}',
            last_page: {{ $productsPaginated->lastPage() }},
            current_page: {{ $productsPaginated->currentPage() }},
            products_count_by_filter_endpoint: '{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.products.by.filters', ['productTypeSlug' => $productType->slug]) }}',
            filter_group_filters: @isset($filerGroupFilters) '{{ $filerGroupFilters }}' @else '' @endisset
        };
    </script>
@endpush
