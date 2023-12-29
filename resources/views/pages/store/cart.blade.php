@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - ' . trans('base.cart') }}</title>
    <meta name="robots" content="noindex, nofollow" />
@endsection

@section('content')

    <x-header-component :data="[
        '#' => 'cart'
    ]" />

    <main id="page-cart" class="page-cart">
        <div class="content">
            <div class="entry-content">
                <div class="container">

                    <div class="row">
                        <div class="art-cart-products-wrapper col-md-12">
                            <div id="basket-list-product" class="basket-list-product">
                                <div class="list-product-table">

                                    <div class="table-head mb-8 d-none d-xl-block">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="table-title">{{ trans('base.name_of_product') }}</div>
                                            </div>
                                            <div class="col-6">
                                                <div class="row">
                                                    <div class="col">
                                                        <div class="table-title">{{ trans('base.price_per_product') }}</div>
                                                    </div>
                                                    <div class="col">
                                                        <div class="table-title">{{ trans('base.count_of_products') }}</div>
                                                    </div>
                                                    <div class="col">
                                                        <div class="table-title text-right">{{ trans('base.price') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col cart-page-products-list">

                                        </div>
                                    </div>

                                </div>


                            </div>
                        </div>
                    </div>

                    <div class="art-cart-bottom">
                        <div id="basket-total-info" class="row">

                            <div class="col-md-8">
                                <div class="total-info-left">
                                    <div class="info-bottom-title mb-3 text-center">
                                        {{ trans('base.enter_promo_code') }}
                                    </div>
                                    <div class="info-bottom-form mt-3">
                                        <form id="promo-code-form">
                                            <div class="d-flex">
                                                <input type="text" name="code" class="form-control" placeholder="{{ trans('base.your_promo_code') }}">
                                                <button type="button" class="btn btn-dark ml-1 add-promo-code-button">{{ trans('base.enter') }}</button>
                                            </div>
                                            <div class="success-text text-success d-none">{{ trans('base.promo_code_add_success') }}</div>
                                            <div class="error-text text-danger"></div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="total-info-right">

                                    <div class="info-top-count d-none d-lg-block mb-2 text-center">
                                        {!! trans('base.products_in_cart') !!}
                                    </div>
                                    <div class="info-top-delivery text-center d-none d-lg-flex mb-3">
                                        <div class="btn-free-shiping font-weight-bold d-none">
                                            <img src="{{ Vite::asset('resources/img/gift-box-delivery.png') }}" alt="{{ trans('base.free_shipment') }}">
                                            <span class="ml-3">
                                                {{ trans('base.free_shipment') }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="info-top-prices mb-6">
                                        <div class="info-top-item py-3 px-5">
                                            <span class="mr-6">{{ trans('base.products_price') }}</span>
                                            <span class="text-nowrap price-products"></span>
                                        </div>
                                        <div class="info-top-item py-3 px-5">
                                            <span class="mr-6">{{ trans('base.delivery') }}</span>
                                            <div class="d-flex">
                                                <span class="text-nowrap price-delivery">{{ trans('base.cart_delivery_price') }}</span>
                                            </div>
                                        </div>
                                        <div class="info-top-item py-3 px-5">
                                            <span class="mr-6 total-title-delivery">{{ trans('base.products_price_discount') }}</span>
                                            <span class="text-nowrap price-discount"></span>
                                        </div>
                                        <div class="info-top-item py-3 px-5">
                                            <span class="mr-6 total-title-delivery">{{ trans('base.products_price_total') }}</span>
                                            <span class="text-nowrap total-price-delivery"></span>
                                        </div>
                                    </div>
                                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.checkout.page') }}" class="btn btn-black-custom w-100 mb-5 mb-lg-9">{{ trans('base.make_order') }}</a>
                                    <div class="info-top-pay text-center">
                                        <div class="pay-title">{{ trans('base.payments_methods') }}:</div>
                                        <div class="pay-list d-flex align-items-center justify-content-center">
                                            <div class="pay-list-item bg-white overflow-hidden d-flex align-items-center justify-content-center">
                                                <img src="{{ Vite::asset('resources/img/payment/visa.svg') }}" alt="img">
                                            </div>
                                            <div class="pay-list-item bg-white overflow-hidden d-flex align-items-center justify-content-center">
                                                <img src="{{ Vite::asset('resources/img/payment/mastercard.svg') }}" alt="img">
                                            </div>
                                            <div class="pay-list-item bg-white overflow-hidden d-flex align-items-center justify-content-center">
                                                <img src="{{ Vite::asset('resources/img/payment/cash.svg') }}" alt="img">
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- upsale here -->
                </div>
                @guest
                    @if(!\Illuminate\Support\Facades\Session::exists('email_subscription_sent'))
                        <x-email-subscription-form/>
                    @endif
                @endguest
            </div>
        </div>
    </main>
@endsection
