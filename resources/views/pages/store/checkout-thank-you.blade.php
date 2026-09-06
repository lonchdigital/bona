@extends('layouts.store-main')

@section('body_class', 'bona-commerce-body')
@section('seo_title', trans('base.checkout_success_title').' — Bona Doors')
@section('meta_description', trans('base.checkout_success_intro'))

@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
    @php
        $currency = $baseCurrency->name_short;
        $formatPrice = fn ($amount) => number_format(
            (float) $amount,
            ((int) round((float) $amount * 100)) % 100 === 0 ? 0 : 2,
            ',',
            ' ',
        ).' '.$currency;
        $deliveryLabel = App\DataClasses\DeliveryTypesDataClass::get((int) $order->delivery_type_id)['name'] ?? trans('base.checkout_success_address_pending');
        $paymentLabel = App\DataClasses\PaymentTypesDataClass::get((int) $order->payment_type_id)['name'] ?? trans('base.checkout_payment_manager_confirmation');
        $recipientName = (int) $order->recipient_type_id === App\DataClasses\RecipientTypesDataClass::RECIPIENT_CUSTOM
            ? trim($order->custom_recipient_first_name.' '.$order->custom_recipient_last_name)
            : trim(($order->user?->first_name ?? '').' '.($order->user?->last_name ?? ''));
        $recipientPhone = (int) $order->recipient_type_id === App\DataClasses\RecipientTypesDataClass::RECIPIENT_CUSTOM
            ? $order->custom_recipient_phone
            : $order->user?->phone;
        $deliveryAddress = match ((int) $order->delivery_type_id) {
            App\DataClasses\DeliveryTypesDataClass::ADDRESS_DELIVERY => collect([
                $order->region?->name,
                $order->district,
                $order->city,
                $order->street,
                $order->building_number,
                $order->apartment_number ? trans('base.checkout_apartment_number').' '.$order->apartment_number : null,
            ])->filter()->implode(', '),
            App\DataClasses\DeliveryTypesDataClass::NP_DELIVERY => collect([$order->np_city, $order->np_department])->filter()->implode(', '),
            App\DataClasses\DeliveryTypesDataClass::SAT_DELIVERY => collect([$order->sat_city, $order->sat_department])->filter()->implode(', '),
            default => trans('base.checkout_success_address_pending'),
        };
        $catalogUrl = $productType
            ? App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => $productType->slug])
            : App\Helpers\MultiLangRoute::getMultiLangRoute('store.home');
    @endphp

    <main class="bona-commerce-page bona-checkout-success">
        <x-store.content-breadcrumbs :items="[
            ['label' => trans('base.checkout_title_short')],
            ['label' => trans('base.checkout_success_title')],
        ]" />

        <section class="bona-checkout-success__hero">
            <div class="bona-shell bona-checkout-success__hero-inner">
                <span class="bona-checkout-success__mark" aria-hidden="true">
                    <svg viewBox="0 0 48 48"><circle cx="24" cy="24" r="22" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="m14.5 24.5 6.2 6.2 13-14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div>
                    <p class="bona-commerce-kicker">{{ trans('base.checkout_success_kicker') }}</p>
                    <h1>{{ trans('base.checkout_success_title') }}</h1>
                    <p>{{ $paymentPendingMessage ?: trans('base.checkout_success_intro') }}</p>
                </div>
            </div>
        </section>

        <section class="bona-shell bona-checkout-success__layout" aria-label="{{ trans('base.checkout_success_number') }}">
            <div class="bona-checkout-success__main">
                <div class="bona-checkout-success__reference">
                    <div><span>{{ trans('base.checkout_success_number') }}</span><strong>#BD-{{ str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) }}</strong></div>
                    <div><span>{{ trans('base.checkout_success_date') }}</span><strong>{{ $order->created_at?->format('d.m.Y · H:i') }}</strong></div>
                </div>

                <div class="bona-checkout-success__details">
                    <article>
                        <span>{{ trans('base.checkout_success_recipient') }}</span>
                        <strong>{{ $recipientName ?: trans('base.checkout_success_address_pending') }}</strong>
                        @if($recipientPhone)<a href="tel:{{ preg_replace('/[^\d+]/', '', $recipientPhone) }}">{{ $recipientPhone }}</a>@endif
                    </article>
                    <article><span>{{ trans('base.checkout_success_delivery') }}</span><strong>{{ $deliveryLabel }}</strong></article>
                    <article><span>{{ trans('base.checkout_success_address') }}</span><strong>{{ $deliveryAddress ?: trans('base.checkout_success_address_pending') }}</strong></article>
                    <article>
                        <span>{{ trans('base.checkout_success_payment') }}</span>
                        <strong>{{ $paymentLabel }}</strong>
                        @if($orderSummary['installment_fee'] > 0)
                            <small>{{ trans('base.checkout_payment_period_label') }}: {{ $orderSummary['installment_period'] }}</small>
                        @endif
                    </article>
                </div>

                <div class="bona-checkout-success__items">
                    <div class="bona-checkout-success__section-head">
                        <h2>{{ trans('base.checkout_success_items') }}</h2>
                        <span>{{ $orderProductGroupsCount }}</span>
                    </div>
                    @foreach($orderProductGroups as $group)
                        @if($group['is_bundle'])
                            <section class="bona-checkout-success__bundle">
                                <header><span>{{ trans('base.cart_bundle_label') }}</span><small>{{ $group['parent']->name }}</small></header>
                                @include('pages.store.partials.checkout-success-product-row', ['product' => $group['parent'], 'isBundleItem' => false])
                                @if($group['items']->isNotEmpty())
                                    <div class="bona-checkout-success__bundle-label">{{ trans('base.cart_bundle_contents') }}</div>
                                    @foreach($group['items'] as $product)
                                        @include('pages.store.partials.checkout-success-product-row', ['product' => $product, 'isBundleItem' => true])
                                    @endforeach
                                @endif
                            </section>
                        @else
                            @include('pages.store.partials.checkout-success-product-row', ['product' => $group['parent'], 'isBundleItem' => false])
                        @endif
                    @endforeach
                </div>
            </div>

            <aside class="bona-checkout-success__summary">
                <p class="bona-commerce-kicker">{{ trans('base.checkout_success_total') }}</p>
                <strong>{{ $formatPrice($orderSummary['total']) }}</strong>
                <div><span>{{ trans('base.products_price') }}</span><b>{{ $formatPrice($orderSummary['products']) }}</b></div>
                @if($orderSummary['discount'] > 0)
                    <div><span>{{ trans('base.products_price_discount') }}</span><b>−{{ $formatPrice($orderSummary['discount']) }}</b></div>
                @endif
                <div><span>{{ trans('base.delivery') }}</span><b>{{ $orderSummary['is_carrier'] ? trans('base.cart_delivery_price') : $formatPrice($orderSummary['delivery']) }}</b></div>
                <div class="bona-checkout-success__next">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h14v12H9l-4 4V4Zm4 5h6M9 12h4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <div><h2>{{ trans('base.checkout_success_next_title') }}</h2><p>{{ trans('base.checkout_success_next_text') }}</p></div>
                </div>

                <a class="bona-button bona-button--light bona-button--full" href="{{ $catalogUrl }}">
                    <span>{{ trans('base.continue_shopping') }}</span>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M14 7l5 5-5 5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            </aside>
        </section>
    </main>
@endsection
