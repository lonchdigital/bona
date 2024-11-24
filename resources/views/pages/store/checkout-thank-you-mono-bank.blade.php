@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - ' . trans('base.thank_you_for_order') }}</title>
@endsection

@section('content')

    @include('pages.store.partials.page_header', ['links' => ['#' => 'thank_you_for_order']])

    <main id="thanks" class="thanks mb-20">
        <div class="content">
            <div class="entry-content">
                <div class="container">
                    <div class="row">
                        <div class="col-12 col-sm-10 col-xl-8 col-xxl-7 mx-auto">

                            <div class="d-flex align-items-center justify-content-center mb-4 mt-14 mx-auto position-relative">
                                <div class="i-congratulations mr-lg-4">
                                    <svg>
                                        <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-congratulations"></use>
                                    </svg>
                                </div>
                                @if(!is_null($order->user))
                                    <div class="h5 thanks-title text-center"><span>{{ $order->user->first_name }}</span>, {{ trans('base.thank_you_for_order') }}</div>
                                @else
                                    <div class="h5 thanks-title text-center"><span>{{ trans('base.thank_you_for_order') }}</div>
                                @endif
                            </div>
                            <div class="thanks-subtitle d-flex flex-column flex-lg-row justify-content-center text-center mr-lg-2 mb-11">{{ trans('base.order_success_mono_bank') }}</div>

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
