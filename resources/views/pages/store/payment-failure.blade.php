@extends('layouts.store-main')

@php
    $catalogUrl = $productType
        ? App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => $productType->slug])
        : App\Helpers\MultiLangRoute::getMultiLangRoute('store.home');
@endphp

@section('body_class', 'bona-content-body')
@section('seo_title', trans('base.payment_failure_title').' — '.config('app.name'))
@section('meta_description', trans('base.payment_failure_intro'))

@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
    <div class="bona-content-page bona-payment-failure">
        <x-store.content-breadcrumbs :items="[['label' => trans('base.payment_failure_title')]]" />

        <section class="bona-payment-failure__section" aria-labelledby="payment-failure-title">
            <div class="bona-shell">
                <div class="bona-payment-failure__panel">
                    <span class="bona-payment-failure__icon" aria-hidden="true">
                        <svg viewBox="0 0 48 48"><circle cx="24" cy="24" r="21" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M17 17l14 14m0-14L17 31" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </span>

                    <div class="bona-payment-failure__copy">
                        <p class="bona-content-kicker">{{ trans('base.payment_failure_kicker') }}</p>
                        <h1 id="payment-failure-title">{{ trans('base.payment_failure_title') }}</h1>
                        <p>{{ trans('base.payment_failure_intro') }}</p>

                        @isset($order)
                            <div class="bona-payment-failure__order">
                                <span>{{ trans('base.payment_failure_order') }}</span>
                                <strong>#BD-{{ str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) }}</strong>
                            </div>
                        @endisset

                        <p class="bona-payment-failure__help">{{ trans('base.payment_failure_help') }}</p>

                        <div class="bona-payment-failure__actions">
                            <a class="bona-button bona-button--light" href="{{ $catalogUrl }}">{{ trans('base.continue_shopping') }}</a>
                            <a class="bona-button bona-button--ghost" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.contacts') }}">{{ trans('base.contacts') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
