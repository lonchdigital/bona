@extends('layouts.store-main')

@section('title')
    @if (isset($selectedCategory))
        <title>{{ $selectedCategory->meta_title }}</title>
        <meta name="title" content="{{ $selectedCategory->meta_title }}">
        <meta name="description" content="{{ $selectedCategory->meta_description }}">
        <meta name="keywords" content="{{ $selectedCategory->meta_keywords }}">
    @endif
@endsection

@section('content')

    @include('pages.store.partials.page_header', ['links' => ['own' => trans('base.product_rucky_availability')]])

    <!-- ======================== Products ======================== -->
    <section class="products art-products-catalog">
        <div class="container">

            <div class="row">

                <!-- === product-filters === -->
                <div class="col-md-3 col-xs-12 art-products-catalog-sidebar">
                    <div id="art-products-filter" class="filters">

                        <div class="filter-top-wrapper">
                            <svg width="13" height="13" viewBox="0 0 13 13" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path d="M1 12L12 1M12 12L1 1" stroke="black" stroke-width="2" stroke-linecap="round"
                                      stroke-linejoin="round"/>
                            </svg>
                            <div class="art-filter-heading">
                                <h4 class="h1 title">{{ trans('base.filter_noun') }}</h4>
                            </div>
                        </div>

                        <form action="" id="filter-left-form">
                            <!--Price-->
                            <div class="filter-item filter-item--price filter-box">
                                <div class="title font-title">{{ trans('base.price') }}</div>
                                <div class="position-relative">
                                    <div id="price-slider" class="price-slider slider-range mb-3">
                                        <div class="currency-wrap">
                                            <div class="input-currency">
                                                {{--                                                <span class="currency">{{ $baseCurrency->name_short }}</span>--}}
                                                <input id="currency-first-main"
                                                       class="currency-first-main sync-input art-form-light-control"
                                                       type="number"
                                                       @isset($filtersData['price_from']) value="{{ $filtersData['price_from'] }}"
                                                       @endisset min="0" max="{{ $productsMaxPrice }}" step="1"
                                                       name="price_from" placeholder="0">
                                            </div>
                                            <div class="input-currency">
                                                {{--                                                <span class="currency">{{ $baseCurrency->name_short }}</span>--}}
                                                <input id="currency-last-main"
                                                       class="currency-last-main sync-input art-form-light-control"
                                                       type="number"
                                                       @isset($filtersData['price_to']) value="{{ $filtersData['price_to'] }}"
                                                       @endisset min="0" max="{{ $productsMaxPrice }}" step="1"
                                                       name="price_to" placeholder="{{ $productsMaxPrice }}">
                                            </div>
                                        </div>
                                        <div class="rangeBar-full"></div>
                                    </div>
                                    <button type="button"
                                            class="btn btn-empty color-dark filter-submit-main">{{ trans('base.apply') }}</button>
                                </div>
                            </div>

                            <!--Discount-->
                            @if(count($filters['main']))
                                @foreach($filters['main'] as $filter)
                                    <div class="filter-box active"> {{-- archive-catalog-filter-left--}}
                                        <div class="title font-title">
                                            {{ $filter->pivot->filter_name }}
                                        </div>

                                        <div class="filter-content filter-item"> {{-- filter-item--type-custom--}}
                                            @if($filter->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION)
                                                @foreach($filter->options as $option)
                                                    <div class="checkbox"
                                                         data-toggle="tooltip"> {{-- checkbox-preview--}}

                                                        {{--custom-checkbox--}}
                                                        <div
                                                            class="custom-control position-relative @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, $filter->slug, $option->slug)) checked @endif">
                                                            <input type="radio"
                                                                   id="custom-field-checkbox-{{$filter->id}}-{{$option->id}}-main"
                                                                   name="{{ $filter->slug }}"
                                                                   value="{{ $option->slug }}"
                                                                   @if(\App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, $filter->slug, $option->slug)) checked @endif>
                                                            <label class="custom-control-label"
                                                                   for="custom-field-checkbox-{{$filter->id}}-{{$option->id}}-main">{{ $option->name }}</label> {{--custom-control-label--}}
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
                                <div class="filter-box filter-item--colors active">
                                    <div class="title font-title">
                                        {{ trans('base.color') }}
                                    </div>
                                    <div class="filter-content">
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
                                                    @if($color->display_as_image)
                                                        <label for="color-checkbox-{{$color->id}}-main"
                                                               class="link-color @if(\App\Services\Product\ProductFiltersService::mainColorFilterOptionChecked($filtersData, 'color', $color)) active @endif"
                                                               style="background-image: url({{$color->image_url}});"
                                                               data-toggle="tooltip">
                                                            <span class="before border-silver-custom"></span>
                                                        </label>
                                                    @else
                                                        <label for="color-checkbox-{{$color->id}}-main"
                                                               class="link-color @if(\App\Services\Product\ProductFiltersService::mainColorFilterOptionChecked($filtersData, 'color', $color)) active @endif"
                                                               @if(!$color->hex)
                                                               style="background: linear-gradient(90deg, rgba(255,0,0,1) 0%, rgba(255,235,0,1) 37%, rgba(5,255,0,1) 74%, rgba(59,63,250,1) 100%, rgba(0,9,255,1) 100%);"
                                                               @else
                                                               style="background-color: {{$color->hex}};"
                                                               @endif
                                                               data-toggle="tooltip">
                                                            <span class="before border-silver-custom"></span>
                                                        </label>
                                                    @endif

                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if( count($filters['main']) || $productType->has_color )
                                <div class="toggle-filters-close filter-submit-main btn btn-empty color-dark">{{trans('base.filter')}}</div>
                            @endif
                        </form>

                    </div> <!--/filters-->
                </div>


                <!--product items-->
                <div class="col-lg-9 col-xs-12">
                    <div class="products-catalog-wrapper">
                        <h1 class="h2 title">{{ trans('base.product_rucky_availability') }}</h1>

                        <div class="art-catalog-top">

                            <div id="art-filter-display" class="art-filter-display">
                                <span>{{ trans('base.filter_noun') }}</span>
                                <svg width="9" height="9" viewBox="0 0 9 9" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M3.87479 8.426V0.973999H4.68479V8.426H3.87479ZM0.454789 5.096V4.322H8.10479V5.096H0.454789Z"
                                        fill="black"/>
                                </svg>
                            </div>

                            <div class="menu-right-dropdown">
                                <div class="right-dropdown-row">
                                    <div>
                                        <div class="dropdown dropdown-custom mb-1 mb-md-0">
                                            <button
                                                class="btn btn-dropdown dropdown-toggle d-block"
                                                type="button" id="dropdownMenuButton" data-toggle="dropdown"
                                                aria-haspopup="true" aria-expanded="false">
                                                <span class="text-left">{{ trans('base.sort_by') }}:</span>
                                                @isset($filtersData['sort_by'])
                                                    <span class="text-right">{{ App\DataClasses\ProductSortOptionsDataClass::get()->where('id', $filtersData['sort_by'])->first()['name'] }}</span>
                                                @else
                                                    <span class="text-right">{{ App\DataClasses\ProductSortOptionsDataClass::get()->where('is_active_by_default')->first()['name'] }}</span>
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
                                    <div>
                                        <div class="dropdown dropdown-custom art-sort-count-wrapper">
                                            <button
                                                class="btn btn-dropdown d-block dropdown-toggle"
                                                type="button" id="dropdownMenuButton2" data-toggle="dropdown"
                                                aria-haspopup="true" aria-expanded="false">
                                                <span class="text-left">{{ trans('base.show_items_per_page') }}:</span>
                                                @isset($filtersData['per_page'])
                                                    <span class="text-right">{{ $filtersData['per_page'] }}</span>
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



                        @if( count($productsPaginated) > 0 )
                            <div class="art-product-list art-three-column">
                                @foreach($productsPaginated as $product)
                                    @include('pages.store.partials.product_item', ['product' => $product, 'baseCurrency' => $baseCurrency])
                                @endforeach
                            </div>
                        @else
                            <section class="art-common-page-section">
                                <p class="nothing-found-text">{{ trans('base.nothing_found') }}</p>
                            </section>
                        @endif
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