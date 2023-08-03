@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - ' . trans('base.thank_you_for_order') }}</title>
@endsection

@section('content')
    <main id="thanks" class="thanks mb-10">
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
                                <div class="h3 thanks-title text-center"><span>{{ $order->user->first_name }}</span>, {{ trans('base.thank_you_for_order') }}</div>
                            </div>
                            <div class="thanks-subtitle d-flex flex-column flex-lg-row justify-content-center text-center text-lg-left mr-lg-2 mb-11">{{ trans('base.order_phone') }}:
                                <a href="tel:+38 (098) 123 45 67" class="d-flex align-items-center link-phone justify-content-center justify-content-sm-start mt-4 mt-lg-0 mx-auto ml-lg-0 mr-lg-0">
                                    <svg>
                                        <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-phone"></use>
                                    </svg>
                                    {{ $order->user->phone }}
                                </a>
                            </div>
                            <div id="thancks-info" class="thancks-info mb-26">
                                <div class="checkout-order-info">
                                    <form class="checkout-order-thanks">
                                        <div class="total-info-top pt-4 pt-xl-6 px-lg-2 px-xl-5 pb-4 pb-xl-6  w-100">
                                            <div class="checkout-order-list-product-descr d-flex flex-wrap p-lg-5 mb-0 mb-lg-6">
                                                <div class="order-number mb-5 mb-md-3 w-100">{{ trans('base.order_id') }}: <span>{{ $order->id }}</span></div>
                                                @php
                                                    $totalSum = 0;
                                                @endphp
                                                @foreach($order->products as $product)
                                                    @php
                                                        $totalSum += round($product->pivot->count * $product->pivot->price, 2);
                                                    @endphp
                                                    <div class="list-product-item mb-5 mb-lg-2">
                                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $product->slug]) }}" class="table-product d-flex align-items-center">
                                                            <div class="table-product-image mr-3 d-block">
                                                                <img src="{{ $product->main_image_url }}" alt="img">
                                                            </div>
                                                            <div class="table-product-info d-block">
                                                                <div class="table-product-name mb-0 d-block">
                                                                    {{ $product->name }}
                                                                </div>
                                                                <div class="table-total-price position-relative">
                                                                    <div class="price">
                                                                        {{ round($product->pivot->count * $product->pivot->price, 2) }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="row flex-column flex-md-row p-lg-5">
                                                <div class="col-12 col-md-6 d-flex align-items-end">
                                                    <div class="thanks-info-checkout-data">
                                                        <div class="checkout-payment checkout-order-info-delivery-title mb-1">{{trans('base.checkout_payment')}}:&nbsp;<span>{{ \App\DataClasses\PaymentTypesDataClass::get($order->payment_type_id)['name'] }}</span></div>
                                                        <div class="checkout-delivery checkout-order-info-delivery-title mb-1">{{trans('base.delivery')}}:&nbsp;<span>{{ \App\DataClasses\DeliveryTypesDataClass::get($order->delivery_type_id)['name'] }}</span></div>
                                                        <div class="checkout-delivery checkout-order-info-date mb-2 mb-md-8">{{ trans('base.delivery_date') }}:&nbsp;<span>23/09/2021</span></div>
                                                        <div class="checkout-delivery checkout-order-user-name mb-5 mb-md-0">{{ trans('base.delivery_to_person') }}:&nbsp;<span>{{ $order->user->first_name }}</span></div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <div class="info-top-prices mb-3">
                                                        <div class="info-top-item d-flex flex-column align-items-start">
                                                            <span class="mr-6 total-title-delivery mb-1">{{ trans('base.summary') }}</span>
                                                            <span class="text-nowrap total-price-delivery">{{ $totalSum }} {{ $baseCurrency->name_short }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="info-top-pay text-center d-flex mb-6">
                                                        <div class="pay-title mr-2">{{ trans('base.payments_methods') }}:</div>
                                                        <div class="pay-list d-flex align-items-center justify-content-center">
                                                            <div class="pay-list-item overflow-hidden d-flex align-items-center justify-content-center">
                                                                <img src="{{ Vite::asset('resources/img/payment/visa.svg') }}" alt="img">
                                                            </div>
                                                            <div class="pay-list-item overflow-hidden d-flex align-items-center justify-content-center">
                                                                <img src="{{ Vite::asset('resources/img/payment/mastercard.svg') }}" alt="img">
                                                            </div>
                                                            <div class="pay-list-item overflow-hidden d-flex align-items-center justify-content-center">
                                                                <img src="{{ Vite::asset('resources/img/payment/cash.svg') }}" alt="img">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => config('domain.wallpaper_product_type_slug')]) }}" class="btn btn-black-custom w-100">{{ trans('base.continue_shopping') }}</a>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
