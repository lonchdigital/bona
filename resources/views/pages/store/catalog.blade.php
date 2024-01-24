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

    <!-- ======================== Page header ======================== -->
    <section class="main-header" style="background-image:url({{ asset('storage/bg-images/header-bg.png') }})"></section>
    <header class="art-page-header">
        <div class="container">
            <ol class="breadcrumb breadcrumb-inverted font-two">
                <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}"><span class="icon icon-home"></span></a></li>
                <li><a class="active" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => $productType->slug]) }}">{{ $productType->name }}</a></li>
            </ol>
        </div>
    </header>

    <!-- ======================== Products ======================== -->

    <section class="products art-products-catalog">
        <div class="container">

            <div class="row">

                <!-- === product-filters === -->

                <div class="col-md-3 col-xs-12">
                    <div class="filters">

                        <form action="" id="filter-left-form">

                            <!--Price-->
                            <div class="filter-box active">
                                <div class="title font-title">{{ trans('base.price') }}</div>
                                <div class="filter-content">
                                    <div class="price-filter">
                                        <input type="hidden" name="price_from" class="art-irs-from"
                                               @isset($filtersData['price_from']) value="{{ $filtersData['price_from'] }}"@endisset
                                        >
                                        <input type="hidden" name="price_to" class="art-irs-to"
                                               @isset($filtersData['price_to']) value="{{ $filtersData['price_to'] }}"@endisset
                                        >
                                        <input type="text" id="range-price-slider" value="">
                                    </div>
                                </div>
                            </div>


                            <!--Discount-->

{{--                            @dd($filters['main'])--}}
                            @if(count($filters['main']))

                                @foreach($filters['main'] as $filter)

                                    <div class="filter-box active"> {{-- archive-catalog-filter-left--}}
                                        <div class="title font-title">
                                            {{ $filter->pivot->filter_name }}
                                        </div>

                                        <div class="filter-content filter-item"> {{-- filter-item--type-custom--}}
                                            @if($filter->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION)
                                                @foreach($filter->options as $option)
                                                    <div class="checkbox" data-toggle="tooltip"> {{-- checkbox-preview--}}

                                                        {{--custom-checkbox--}}
                                                        <div class="custom-control position-relative @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, $filter->slug, $option->slug)) checked @endif">
                                                            <input type="radio"
                                                                   id="custom-field-checkbox-{{$filter->id}}-{{$option->id}}-main"
                                                                   name="{{ $filter->slug }}"
                                                                   value="{{ $option->slug }}"
                                                                   @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, $filter->slug, $option->slug)) checked @endif>
                                                            <label class="custom-control-label" for="custom-field-checkbox-{{$filter->id}}-{{$option->id}}-main">{{ $option->name }}</label> {{--custom-control-label--}}
                                                        </div>
                                                    </div>
                                                @endforeach

                                            @elseif($filter->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_NUMBER || $filter->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_SIZE)

{{--                                                @dd('oh!')--}}

                                            @endif
                                        </div>

                                    </div> <!--/filter-box-->

                                @endforeach

                            @endif


                            @if($productType->has_color)

                                <div class="filter-box filter-item--colors active"> {{-- archive-catalog-filter-left--}}
                                    <div class="title font-title">
                                        {{ trans('base.color') }}
                                    </div>

                                    <div class="filter-content"> {{-- filter-item--type-custom--}}
                                        <div class="art-filter-color-content">
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
                                                        {{--                                                       title="{{ $color->name }}"--}}
                                                    >
                                                        <span class="before border-silver-custom"></span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                </div> <!--/filter-box-->

                            @endif


                            <div class="toggle-filters-close filter-submit-main btn btn-empty color-dark">{{trans('base.filter')}}</div>

                        </form>

                    </div> <!--/filters-->
                </div>

                <!--product items-->

                <div class="col-md-9 col-xs-12">

                    <div class="products-catalog-wrapper">

                        <h1 class="h2 title">{{ $productType->name }}</h1>

                        <div class="art-product-list art-three-column">
                            @foreach($productsPaginated as $product)
                                @include('pages.store.partials.product_item', ['product' => $product, 'baseCurrency' => $baseCurrency])
                            @endforeach
                        </div>

                    </div>

                    <!--Pagination-->
                {{ $productsPaginated->links('pagination.store') }}


                </div> <!--/product items-->

            </div><!--/row-->

        </div><!--/container-->
    </section>




    @if( count($faqs) )
        <!-- ======================== FAQs ======================== -->
        <section class="faqs-section">
            <div class="container">

                <header>
                    <div class="row">
                        <div class="col-md-offset-2 col-md-8 text-center">
                            <h2 class="title">{{ trans('base.faqs') }}</h2>
                            <div class="subtitle font-two">
                                <p>{{trans('base.faqs_subtitle')}}</p>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="accordion-faqs">

                    <div class="faq-col">
                        @foreach($faqs as $index => $faq)
                            @if($index % 2 == 0)
                                <div class="accordion-item-wrapper">
                                    <button class="accordion">
                                        <span class="question">{{ $faq->question }}</span>
                                    </button>
                                    <div class="art-panel">
                                        <div class="panel-data">{{ $faq->answer }}</div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="faq-col">
                        @foreach($faqs as $index => $faq)
                            @if($index % 2 != 0)
                                <div class="accordion-item-wrapper">
                                    <button class="accordion">
                                        <span class="question">{{ $faq->question }}</span>
                                    </button>
                                    <div class="art-panel">
                                        <div class="panel-data">{{ $faq->answer }}</div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                </div>

            </div>
        </section>
    @endif

    @if( !is_null($seoText) && (!is_null($seoText['title']) || !is_null($seoText['content'])) )
        <!-- ======================== SEO ======================== -->
        <section class="seo-section">
            <div class="container">

                <header>
                    <div class="row">
                        <div class="col-md-offset-2 col-md-8 text-center">
                            <h2 class="title">{{$seoText['title']}}</h2>
                        </div>
                    </div>
                </header>

                <div class="seo-content">
                    {!! $seoText['content'] !!}
                </div>

            </div>
        </section>
    @endif

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
