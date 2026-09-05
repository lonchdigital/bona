@extends('layouts.store-main')

@php
    $servicesUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.services');
    $measurementUrl = $measurementService
        ? App\Helpers\MultiLangRoute::getMultiLangRoute('store.service.page', ['serviceSlug' => $measurementService->slug])
        : $servicesUrl;
@endphp

@section('body_class', 'bona-commerce-body')
@section('seo_title', trans('base.cart').' — Bona Doors')
@section('meta_description', trans('base.cart_meta_description'))

@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
    <div class="bona-commerce-page bona-cart-page">
        <x-store.content-breadcrumbs :items="[['label' => trans('base.cart')]]" />

        <div class="bona-commerce-hero" role="region" aria-labelledby="cart-page-title">
            <div class="bona-shell bona-commerce-hero__grid">
                <div>
                    <p class="bona-commerce-kicker">{{ trans('base.cart_kicker') }}</p>
                    <h1 id="cart-page-title">{{ trans('base.cart') }}</h1>
                </div>
                <p>{{ trans('base.cart_intro') }}</p>
            </div>
        </div>

        <div class="bona-shell bona-commerce-layout" data-cart-page>
            <div class="bona-cart-content" role="region" aria-label="{{ trans('base.products_in_cart_label') }}">
                <div class="bona-cart-list cart-page-products-list" data-cart-list aria-live="polite">
                    <div class="bona-cart-loading" data-cart-loading><span></span><span></span></div>
                </div>

                <div class="bona-cart-error" data-cart-error hidden>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8v5m0 3h.01M10.4 4.2 3.1 17a2 2 0 0 0 1.74 3h14.32a2 2 0 0 0 1.74-3L13.6 4.2a1.84 1.84 0 0 0-3.2 0Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <div>
                        <h2>{{ trans('base.cart_load_error_title') }}</h2>
                        <p>{{ trans('base.cart_load_error_text') }}</p>
                    </div>
                    <button class="bona-button bona-button--outline" type="button" data-cart-retry>{{ trans('base.cart_retry') }}</button>
                </div>

                <div class="bona-cart-empty" data-cart-empty hidden>
                    <svg viewBox="0 0 64 64" aria-hidden="true">
                        <path d="M18 23h31l-4 21H22L18 23Z" fill="none" stroke="currentColor" stroke-width="2"/>
                        <path d="M13 16h4l5 28M26 51a3 3 0 1 0 0 .1M42 51a3 3 0 1 0 0 .1" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <h2>{{ trans('base.cart_empty_title') }}</h2>
                    <p>{{ trans('base.cart_empty_text') }}</p>
                    <a class="bona-button bona-button--dark" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.all-products.page') }}">{{ trans('base.wish_list_go_to_catalog') }}</a>
                </div>

                <article class="bona-service-offer" data-cart-service-offer>
                    <div>
                        <h2>{{ trans('base.cart_measure_title') }}</h2>
                        <p>{{ trans('base.cart_measure_text') }}</p>
                    </div>
                    <a class="bona-button bona-button--outline" href="{{ $measurementUrl }}">{{ trans('base.cart_measure_cta') }}</a>
                </article>
            </div>

            <aside class="bona-order-summary" data-cart-summary>
                <p class="bona-commerce-kicker">{{ trans('base.cart_summary') }}</p>
                <h2>{{ trans('base.cart_to_pay') }}</h2>

                <div class="bona-summary-lines">
                    <div class="bona-summary-line"><span>{{ trans('base.products_price') }}</span><strong class="price-products" data-summary-subtotal>—</strong></div>
                    <div class="bona-summary-line"><span>{{ trans('base.delivery') }}</span><strong>{{ trans('base.cart_delivery_next') }}</strong></div>
                    <div class="bona-summary-line bona-summary-line--discount" data-summary-discount-row hidden><span>{{ trans('base.products_price_discount') }}</span><strong class="price-discount" data-summary-discount>—</strong></div>
                    <div class="bona-summary-line bona-summary-line--total"><span>{{ trans('base.products_price_total') }}</span><strong class="total-price-delivery" data-summary-total>—</strong></div>
                </div>

                <form class="bona-promo-form" id="promo-code-form" data-promo-form novalidate>
                    <label class="sr-only" for="cart-promo-code">{{ trans('base.your_promo_code') }}</label>
                    <div class="bona-promo-form__control">
                        <input id="cart-promo-code" type="text" name="code" maxlength="64" autocomplete="off" placeholder="{{ trans('base.your_promo_code') }}">
                        <button type="submit" class="add-promo-code-button" aria-label="{{ trans('base.cart_apply_promo') }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M14 7l5 5-5 5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                    <div class="bona-promo-form__applied" data-promo-applied hidden><span data-promo-applied-label></span><button type="button" data-promo-remove>{{ trans('base.cart_remove_promo') }}</button></div>
                    <p class="success-text" data-promo-success hidden>{{ trans('base.promo_code_add_success') }}</p>
                    <p class="error-text" data-promo-error role="alert"></p>
                </form>

                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.checkout.page') }}" class="bona-button bona-button--light bona-button--full art-cart-checkout-button" data-checkout-link>{{ trans('base.make_order') }}</a>
                <p class="bona-summary-note">{{ trans('base.cart_summary_note') }}</p>

                <div class="bona-payment-marks" aria-label="{{ trans('base.payments_methods') }}">
                    <img src="{{ Vite::asset('resources/img/payment/visa.svg') }}" alt="Visa">
                    <img src="{{ Vite::asset('resources/img/payment/mastercard.svg') }}" alt="Mastercard">
                    <span>LiqPay</span>
                </div>
            </aside>
        </div>

        @if($cartServices->isNotEmpty())
            <section class="bona-cart-services" aria-labelledby="cart-services-title">
                <div class="bona-shell">
                    <header class="bona-cart-services__head">
                        <div>
                            <p class="bona-commerce-kicker">{{ trans('base.cart_services_kicker') }}</p>
                            <h2 id="cart-services-title">{{ trans('base.cart_services_title') }}</h2>
                        </div>
                        <a class="bona-arrow-link" href="{{ $servicesUrl }}">
                            <span>{{ trans('base.cart_services_all') }}</span>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-5-5 5 5-5 5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    </header>

                    <div class="bona-cart-services__grid">
                        @foreach($cartServices as $service)
                            @php
                                $serviceDescription = Illuminate\Support\Str::limit(
                                    trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) $service->description))),
                                    175
                                );
                                $serviceUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.service.page', ['serviceSlug' => $service->slug]);
                            @endphp
                            <a class="bona-cart-service-card" href="{{ $serviceUrl }}">
                                <div class="bona-cart-service-card__meta">
                                    <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span>{{ $service->button_text ?: trans('base.cart_service_help_label') }}</span>
                                </div>
                                <h3>{{ $service->title }}</h3>
                                @if($serviceDescription)
                                    <p>{{ $serviceDescription }}</p>
                                @endif
                                <span class="bona-arrow-link bona-cart-service-card__link">
                                    <span>{{ $service->button_text ?: trans('base.cart_service_details') }}</span>
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-5-5 5 5-5 5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </div>
@endsection
