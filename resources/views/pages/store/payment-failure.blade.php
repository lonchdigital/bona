@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - ' . trans('base.payment_failure') }}</title>
@endsection

@section('content')

    @include('pages.store.partials.page_header', ['links' => ['#' => 'payment_failure']])

    <main id="thanks" class="thanks mb-20">
        <div class="content">
            <div class="entry-content">
                <div class="container">
                    <div class="row">
                        <div class="col-12 col-sm-10 col-xl-8 col-xxl-7 mx-auto">

                            <div class="d-flex align-items-center justify-content-center mb-4 mt-14 mx-auto position-relative">
                                <div class="h5 thanks-title text-center"><span>{{ trans('base.payment_failure') }}</div>
                            </div>

                            <div class="d-flex align-items-center justify-content-center mb-4 mt-14 mx-auto position-relative">
                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => $productType->slug]) }}" class="btn btn-empty color-dark">{{ trans('base.continue_shopping') }}</a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
