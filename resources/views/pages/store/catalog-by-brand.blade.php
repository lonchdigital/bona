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

    <!-- ======================== Main header ======================== -->
    <section class="main-header" style="background-image:url({{ asset('storage/bg-images/catalog-header-bg.png') }})">
        <header>
            <div class="container">
                <h1 class="h2 title">{{$brand->name}}</h1>
                <ol class="breadcrumb breadcrumb-inverted">
                    <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}"><span class="icon icon-home"></span></a></li>
                    <li><a class="active" href="">{{$brand->name}}</a></li>
                </ol>
            </div>
        </header>
    </section>


    <!-- ======================== Products ======================== -->
    <section class="products">
        <div class="container">

            <header>
                <h3 class="h3 title">{{ trans('base.brands_products') . ' ' . $brand->name }}</h3>
            </header>

            <div class="row">

                <!-- === product-filters === -->


                <!--product items-->

                <div class="col-md-12 col-xs-12">

                    <div class="art-product-list art-four-column">
                        @foreach($productsPaginated as $product)
                            @include('pages.store.partials.product_item', ['product' => $product, 'baseCurrency' => $baseCurrency])
                        @endforeach
                    </div>


                    <!--Pagination-->
                {{ $productsPaginated->links('pagination.common') }}

                </div> <!--/product items-->

            </div><!--/row-->

        </div><!--/container-->
    </section>

@stop
@push('dynamic_scripts')
    <script>
        const catalog = {
            product_type_slug: '{{ $productType->slug }}',
            category_slug: '{{ isset($selectedCategory) ? $selectedCategory->slug : ''}}',
            last_page: {{ $productsPaginated->lastPage() }},
            current_page: {{ $productsPaginated->currentPage() }},
            {{--products_count_by_filter_endpoint: '{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.products.by.filters', ['productTypeSlug' => $productType->slug]) }}',--}}
            filter_group_filters: @isset($filerGroupFilters) '{{ $filerGroupFilters }}' @else '' @endisset
        };
    </script>
@endpush
