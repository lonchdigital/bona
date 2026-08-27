@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - ' . trans('base.wish_list') }}</title>
    <meta name="robots" content="noindex, nofollow" />
@endsection

@section('content')

    @include('pages.store.partials.page_header', ['links' => ['own' => trans('base.wish_list')]])

    <section class="products art-products-catalog art-wish-list">
        <div class="container">
            <div class="products-catalog-wrapper">

            <h1 class="h2 title">{{ trans('base.wish_list') }}</h1>

            @if(!count($products))

                <div class="art-wish-list-empty">
                    <div class="art-wish-list-empty-icon">
                        <x-wish-heart/>
                    </div>

                    <p class="art-wish-list-empty-text">{{ trans('base.wish_list_is_empty') }}</p>

                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.all-products.page') }}"
                       class="btn btn-main art-wish-list-cta">{{ trans('base.wish_list_go_to_catalog') }}</a>
                </div>

            @else

                <div class="art-product-list art-four-column art-wish-list-grid"
                     id="wish-list-grid"
                     @if(!$isPublic) data-wish-list-owner="1" @endif>
                    @foreach($products as $product)
                        @include('pages.store.partials.product_item', [
                            'product' => $product,
                            'baseCurrency' => $baseCurrency,
                        ])
                    @endforeach
                </div>

                <div class="art-wish-list-actions">
                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.all-products.page') }}"
                       class="btn btn-main">{{ trans('base.wish_list_go_to_catalog') }}</a>
                </div>

            @endif

            </div>
        </div>
    </section>

@endsection
