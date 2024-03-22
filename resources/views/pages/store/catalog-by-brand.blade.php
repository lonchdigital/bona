@extends('layouts.store-main')

@section('title')
    @if (isset($brand))
        <title>{{ $brand->name }}</title>
        <meta name="title" content="{{ $brand->meta_title }}">
        <meta name="description" content="{{ $brand->meta_description }}">
        <meta name="keywords" content="{{ $brand->meta_keywords }}">
    @endif
@endsection

@section('content')

    @include('pages.store.partials.page_header', ['links' => ['own' => $brand->name]])


    <!-- ======================== Products ======================== -->
    <section class="products art-section-pd">
        <div class="container">


            <div class="row">
                <header class=" col-12 art-header-left">
                    <div>
                        <h2 class="title">{{ trans('base.brands_products') . ' ' . $brand->name }}</h2>
                    </div>
                </header>
            </div>

            <div class="row">
                <div class="col-md-12 col-xs-12">

                    <div class="art-product-list art-product-filtered-by-brand art-four-column">
                        @if( count($productsPaginated) > 0 )
                            @foreach($productsPaginated as $product)
                                @include('pages.store.partials.product_item', ['product' => $product, 'baseCurrency' => $baseCurrency])
                            @endforeach
                        @else
                            <p class="nothing-found-text">{{ trans('base.nothing_found') }}</p>
                        @endif
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
