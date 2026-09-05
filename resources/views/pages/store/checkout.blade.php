@extends('layouts.store-main')

@section('body_class', 'bona-commerce-body')
@section('seo_title', trans('base.checkout').' — Bona Doors')
@section('meta_description', trans('base.checkout_meta_description'))

@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
    @php
        $selectedDeliveryType = (int) old('delivery_type_id', $checkoutDeliveryType);
        $selectedPaymentType = (int) old('payment_type_id', $checkoutPaymentType);
        $selectedRecipientType = (int) old('recipient_type_id', App\DataClasses\RecipientTypesDataClass::RECIPIENT_USER);
        $selectedPaymentLabel = App\DataClasses\PaymentTypesDataClass::get($selectedPaymentType)['name'] ?? trans('base.checkout_payment_manager_confirmation');
        $selectedDeliveryLabel = App\DataClasses\DeliveryTypesDataClass::get($selectedDeliveryType)['name'] ?? trans('base.checkout_address_delivery');
        $currency = $baseCurrency->name_short;
        $formatPrice = fn ($amount) => number_format((float) $amount, 0, ',', ' ').' '.$currency;
        $productsCount = $productsInCart->sum(fn ($product) => (int) $product->pivot->count);
        $signInUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('auth.sign-in.page', ['redirect_to' => request()->getRequestUri()]);
    @endphp

    <div class="bona-commerce-page bona-checkout-page">
        <x-store.content-breadcrumbs :items="[
            ['label' => trans('base.cart'), 'url' => App\Helpers\MultiLangRoute::getMultiLangRoute('store.cart.page')],
            ['label' => trans('base.checkout_title_short')],
        ]" />

        <div class="bona-commerce-hero" role="region" aria-labelledby="checkout-page-title">
            <div class="bona-shell bona-commerce-hero__grid">
                <div>
                    <p class="bona-commerce-kicker">{{ trans('base.checkout_kicker') }}</p>
                    <h1 id="checkout-page-title">{{ trans('base.checkout_title_short') }}</h1>
                    <div class="bona-checkout-progress" aria-label="{{ trans('base.checkout_progress_label') }}">
                        <span class="is-active" data-checkout-progress="contact">{{ trans('base.checkout_progress_contact') }}</span>
                        <span data-checkout-progress="delivery">{{ trans('base.checkout_progress_delivery') }}</span>
                        <span data-checkout-progress="payment">{{ trans('base.checkout_progress_payment') }}</span>
                    </div>
                </div>
                <p>{{ trans('base.checkout_intro') }}</p>
            </div>
        </div>

        <form id="checkout-main" class="bona-shell bona-checkout-layout" action="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.checkout.confirm') }}" method="POST" novalidate>
            @csrf

            <div class="bona-checkout-form">
                @if($errors->any())
                    <div class="bona-checkout-errors" role="alert" tabindex="-1" data-checkout-errors>
                        <strong>{{ trans('base.checkout_order_error') }}</strong>
                        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <section class="bona-checkout-step" data-checkout-step="contact" aria-labelledby="checkout-contact-title">
                    <header class="bona-checkout-step__head"><span class="bona-checkout-step__num">01</span><h2 id="checkout-contact-title">{{ trans('base.checkout_contact_title') }}</h2></header>

                    @guest
                        <div class="bona-checkout-auth-prompt">
                            <div><strong>{{ trans('base.checkout_signin_title') }}</strong><p>{{ trans('base.checkout_signin_text') }}</p></div>
                            <a class="bona-button bona-button--outline" href="{{ $signInUrl }}">{{ trans('base.checkout_signin_action') }}</a>
                        </div>
                        <div class="bona-form-grid">
                            <div class="bona-field @error('full_name') has-error @enderror @error('first_name') has-error @enderror @error('last_name') has-error @enderror"><label for="name">{{ trans('base.checkout_full_name') }}</label><input id="name" name="full_name" type="text" value="{{ old('full_name', trim(old('first_name').' '.old('last_name'))) }}" autocomplete="name" maxlength="201" placeholder="{{ trans('base.checkout_full_name_placeholder') }}" required>@error('full_name')<small>{{ $message }}</small>@enderror @error('first_name')<small>{{ $message }}</small>@enderror @error('last_name')<small>{{ $message }}</small>@enderror</div>
                            <div class="bona-field @error('phone') has-error @enderror"><label for="phone">{{ trans('base.phone') }}</label><input id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" inputmode="tel" placeholder="{{ trans('base.checkout_phone_placeholder') }}" required>@error('phone')<small>{{ $message }}</small>@enderror</div>
                            <div class="bona-field bona-field--wide @error('email') has-error @enderror"><label for="email">{{ trans('base.email') }}</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" placeholder="{{ trans('base.checkout_email_placeholder') }}" required>@error('email')<small>{{ $message }}</small>@enderror</div>
                        </div>
                    @else
                        <div class="bona-checkout-customer">
                            <span aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->first_name, 0, 1).mb_substr(auth()->user()->last_name, 0, 1)) }}</span>
                            <div><p>{{ trans('base.checkout_signed_in_as') }}</p><strong>{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</strong><small>{{ auth()->user()->phone }} · {{ auth()->user()->email }}</small></div>
                        </div>
                    @endguest
                </section>

                <section class="bona-checkout-step" data-checkout-step="delivery" aria-labelledby="checkout-delivery-title">
                    <header class="bona-checkout-step__head"><span class="bona-checkout-step__num">02</span><h2 id="checkout-delivery-title">{{ trans('base.checkout_delivery_title') }}</h2></header>

                    <div class="bona-choice-list" id="checkout-delivery-accordion">
                        <label class="bona-choice-card">
                            <input class="art-accordion-delivery" type="radio" id="delivery-radio-address" name="delivery_type_id" value="{{ App\DataClasses\DeliveryTypesDataClass::ADDRESS_DELIVERY }}" data-accordion="delivery-1" @checked($selectedDeliveryType === App\DataClasses\DeliveryTypesDataClass::ADDRESS_DELIVERY)>
                            <span><b>{{ trans('base.checkout_address_delivery') }}</b><small>{{ trans('base.checkout_address_delivery_note') }}</small></span><strong>{{ $formatPrice(config('domain.delivery_price', 0)) }}</strong>
                        </label>
                        <div id="delivery-1" class="bona-choice-panel accordion-delivery-data" @hidden($selectedDeliveryType !== App\DataClasses\DeliveryTypesDataClass::ADDRESS_DELIVERY)>
                            <div class="bona-form-grid">
                                <div class="bona-field @error('region_id') has-error @enderror"><label for="region_id">{{ trans('base.region') }}</label><select id="region_id" class="region-select" name="region_id"><option value=""></option>@foreach($regions as $region)<option value="{{ $region->id }}" @selected((int) old('region_id') === (int) $region->id)>{{ $region->name }}</option>@endforeach</select>@error('region_id')<small>{{ $message }}</small>@enderror</div>
                                <div class="bona-field @error('district') has-error @enderror"><label for="district">{{ trans('base.district') }}</label><input id="district" type="text" name="district" value="{{ old('district') }}" maxlength="150">@error('district')<small>{{ $message }}</small>@enderror</div>
                                <div class="bona-field @error('city') has-error @enderror"><label for="city">{{ trans('base.city') }}</label><input id="city" type="text" name="city" value="{{ old('city') }}" autocomplete="address-level2" maxlength="150">@error('city')<small>{{ $message }}</small>@enderror</div>
                                <div class="bona-field @error('street') has-error @enderror"><label for="street">{{ trans('base.checkout_street') }}</label><input id="street" type="text" name="street" value="{{ old('street') }}" autocomplete="street-address" maxlength="180">@error('street')<small>{{ $message }}</small>@enderror</div>
                                <div class="bona-field bona-field--third @error('building_number') has-error @enderror"><label for="building_number">{{ trans('base.checkout_building_number') }}</label><input id="building_number" type="text" name="building_number" value="{{ old('building_number') }}" maxlength="30">@error('building_number')<small>{{ $message }}</small>@enderror</div>
                                <div class="bona-field bona-field--third @error('apartment_number') has-error @enderror"><label for="apartment_number">{{ trans('base.checkout_apartment_number') }}</label><input id="apartment_number" type="text" name="apartment_number" value="{{ old('apartment_number') }}" maxlength="30">@error('apartment_number')<small>{{ $message }}</small>@enderror</div>
                                <div class="bona-field bona-field--third @error('floor_number') has-error @enderror"><label for="floor_number">{{ trans('base.checkout_floor_number') }}</label><input id="floor_number" type="text" name="floor_number" value="{{ old('floor_number') }}" maxlength="20">@error('floor_number')<small>{{ $message }}</small>@enderror</div>
                            </div>
                        </div>

                        <label class="bona-choice-card">
                            <input class="art-accordion-delivery" type="radio" id="delivery-radio-np" name="delivery_type_id" value="{{ App\DataClasses\DeliveryTypesDataClass::NP_DELIVERY }}" data-accordion="delivery-2" @checked($selectedDeliveryType === App\DataClasses\DeliveryTypesDataClass::NP_DELIVERY)>
                            <span><b>{{ trans('base.checkout_np_delivery') }}</b><small>{{ trans('base.checkout_np_delivery_note') }}</small></span><strong>{{ trans('base.cart_delivery_price') }}</strong>
                        </label>
                        <div id="delivery-2" class="bona-choice-panel accordion-delivery-data" @hidden($selectedDeliveryType !== App\DataClasses\DeliveryTypesDataClass::NP_DELIVERY)>
                            <div class="bona-form-grid">
                                <div class="bona-field @error('np_city') has-error @enderror"><label for="np_city">{{ trans('base.checkout_search_city') }}</label><input id="np_city" class="np-city-select" type="text" name="np_city" value="{{ old('np_city') }}" @if($npCityInitial) data-initial-value="{{ json_encode($npCityInitial, JSON_UNESCAPED_UNICODE) }}" @endif>@error('np_city')<small>{{ $message }}</small>@enderror</div>
                                <div class="bona-field @error('np_department') has-error @enderror" id="np-department-search-wrap"><label for="np_department">{{ trans('base.checkout_select_np_department') }}</label><input id="np_department" class="np-department-select" type="text" name="np_department" value="{{ old('np_department') }}" @if($npDepartmentInitial) data-initial-value="{{ json_encode($npDepartmentInitial, JSON_UNESCAPED_UNICODE) }}" @endif>@error('np_department')<small>{{ $message }}</small>@enderror</div>
                            </div>
                        </div>

                        <label class="bona-choice-card">
                            <input class="art-accordion-delivery" type="radio" id="delivery-radio-sat" name="delivery_type_id" value="{{ App\DataClasses\DeliveryTypesDataClass::SAT_DELIVERY }}" data-accordion="delivery-3" @checked($selectedDeliveryType === App\DataClasses\DeliveryTypesDataClass::SAT_DELIVERY)>
                            <span><b>{{ trans('base.checkout_sat_delivery') }}</b><small>{{ trans('base.checkout_sat_delivery_note') }}</small></span><strong>{{ trans('base.cart_delivery_price') }}</strong>
                        </label>
                        <div id="delivery-3" class="bona-choice-panel accordion-delivery-data" @hidden($selectedDeliveryType !== App\DataClasses\DeliveryTypesDataClass::SAT_DELIVERY)>
                            <div class="bona-form-grid">
                                <div class="bona-field @error('sat_city') has-error @enderror"><label for="sat_city">{{ trans('base.checkout_search_city') }}</label><input id="sat_city" class="sat-city-select" type="text" name="sat_city" value="{{ old('sat_city') }}" @if($satCityInitial) data-initial-value="{{ json_encode($satCityInitial, JSON_UNESCAPED_UNICODE) }}" @endif>@error('sat_city')<small>{{ $message }}</small>@enderror</div>
                                <div class="bona-field @error('sat_department') has-error @enderror" id="sat-department-search-wrap"><label for="sat_department">{{ trans('base.checkout_select_np_department') }}</label><input id="sat_department" class="sat-department-select" type="text" name="sat_department" value="{{ old('sat_department') }}" @if($satDepartmentInitial) data-initial-value="{{ json_encode($satDepartmentInitial, JSON_UNESCAPED_UNICODE) }}" @endif>@error('sat_department')<small>{{ $message }}</small>@enderror</div>
                            </div>
                        </div>

                        <label class="bona-choice-card">
                            <input class="art-accordion-delivery" type="radio" id="delivery-radio-pickup" name="delivery_type_id" value="{{ App\DataClasses\DeliveryTypesDataClass::PICK_UP_DELIVERY }}" data-accordion="delivery-4" @checked($selectedDeliveryType === App\DataClasses\DeliveryTypesDataClass::PICK_UP_DELIVERY)>
                            <span><b>{{ trans('base.checkout_pickup_from_store') }}</b><small>{{ trans('base.checkout_pickup_note') }}</small></span><strong>{{ trans('base.checkout_free') }}</strong>
                        </label>
                        <div id="delivery-4" class="bona-choice-panel accordion-delivery-data" @hidden($selectedDeliveryType !== App\DataClasses\DeliveryTypesDataClass::PICK_UP_DELIVERY)><p>{{ trans('base.checkout_pickup_panel') }}</p></div>
                    </div>

                    <div class="bona-recipient-block">
                        <h3>{{ trans('base.checkout_recipient') }}</h3>
                        <div class="bona-inline-choices">
                            <label><input class="art-accordion-recipient" type="radio" id="recipient-user" name="recipient_type_id" value="{{ App\DataClasses\RecipientTypesDataClass::RECIPIENT_USER }}" data-accordion="recipient-1" @checked($selectedRecipientType === App\DataClasses\RecipientTypesDataClass::RECIPIENT_USER)><span>{{ trans('base.checkout_recipient_me') }}</span></label>
                            <label><input class="art-accordion-recipient" type="radio" id="recipient-other" name="recipient_type_id" value="{{ App\DataClasses\RecipientTypesDataClass::RECIPIENT_CUSTOM }}" data-accordion="checkout-custom-recipient-accordion" @checked($selectedRecipientType === App\DataClasses\RecipientTypesDataClass::RECIPIENT_CUSTOM)><span>{{ trans('base.checkout_recipient_another_person') }}</span></label>
                        </div>
                        <div id="checkout-custom-recipient-accordion" class="bona-choice-panel accordion-recipient-data" @hidden($selectedRecipientType !== App\DataClasses\RecipientTypesDataClass::RECIPIENT_CUSTOM)>
                            <div class="bona-form-grid">
                                <div class="bona-field @error('custom_first_name') has-error @enderror"><label for="custom_name">{{ trans('base.name') }}</label><input id="custom_name" name="custom_first_name" type="text" value="{{ old('custom_first_name') }}" maxlength="100">@error('custom_first_name')<small>{{ $message }}</small>@enderror</div>
                                <div class="bona-field @error('custom_last_name') has-error @enderror"><label for="custom_surname">{{ trans('base.last_name') }}</label><input id="custom_surname" name="custom_last_name" type="text" value="{{ old('custom_last_name') }}" maxlength="100">@error('custom_last_name')<small>{{ $message }}</small>@enderror</div>
                                <div class="bona-field @error('custom_phone') has-error @enderror"><label for="custom_phone">{{ trans('base.phone') }}</label><input id="custom_phone" name="custom_phone" type="tel" value="{{ old('custom_phone') }}" inputmode="tel">@error('custom_phone')<small>{{ $message }}</small>@enderror</div>
                                <div class="bona-field @error('custom_email') has-error @enderror"><label for="custom_email">{{ trans('base.email') }}</label><input id="custom_email" name="custom_email" type="email" value="{{ old('custom_email') }}" maxlength="255">@error('custom_email')<small>{{ $message }}</small>@enderror</div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="bona-checkout-step" data-checkout-step="payment" aria-labelledby="checkout-payment-title">
                    <header class="bona-checkout-step__head"><span class="bona-checkout-step__num">03</span><h2 id="checkout-payment-title">{{ trans('base.checkout_payment') }}</h2></header>
                    <div class="bona-choice-list bona-payment-choices">
                        <label class="bona-choice-card"><input type="radio" id="payment-manager-confirmation" name="payment_type_id" value="{{ App\DataClasses\PaymentTypesDataClass::MANAGER_CONFIRMATION_PAYMENT }}" @checked($selectedPaymentType === App\DataClasses\PaymentTypesDataClass::MANAGER_CONFIRMATION_PAYMENT)><span><b>{{ trans('base.checkout_payment_manager_confirmation') }}</b><small>{{ trans('base.checkout_payment_manager_confirmation_note') }}</small></span><strong>{{ trans('base.checkout_no_commission') }}</strong></label>
                        <label class="bona-choice-card"><input type="radio" id="payment-cash" name="payment_type_id" value="{{ App\DataClasses\PaymentTypesDataClass::CASH_PAYMENT }}" @checked($selectedPaymentType === App\DataClasses\PaymentTypesDataClass::CASH_PAYMENT)><span><b>{{ trans('base.checkout_payment_cash') }}</b><small>{{ trans('base.checkout_payment_cash_note') }}</small></span><strong>{{ trans('base.checkout_no_commission') }}</strong></label>
                        <label class="bona-choice-card"><input type="radio" id="payment-card" name="payment_type_id" value="{{ App\DataClasses\PaymentTypesDataClass::CARD_PAYMENT }}" @checked($selectedPaymentType === App\DataClasses\PaymentTypesDataClass::CARD_PAYMENT)><span><b>{{ trans('base.checkout_payment_card') }}</b><small>{{ trans('base.checkout_payment_card_note') }}</small></span><strong class="bona-payment-brand">LiqPay</strong></label>
                        <label class="bona-choice-card"><input type="radio" id="payment-invoice" name="payment_type_id" value="{{ App\DataClasses\PaymentTypesDataClass::INVOICE_PAYMENT }}" @checked($selectedPaymentType === App\DataClasses\PaymentTypesDataClass::INVOICE_PAYMENT)><span><b>{{ trans('base.checkout_payment_invoice') }}</b><small>{{ trans('base.checkout_payment_invoice_note') }}</small></span><svg class="bona-payment-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h8l4 4v14H7V3Zm8 0v5h4M10 12h6M10 16h6" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></label>
                        <label class="bona-choice-card"><input type="radio" id="payment-card_paypart-mono-bank" name="payment_type_id" value="{{ App\DataClasses\PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK }}" @checked($selectedPaymentType === App\DataClasses\PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK)><span><b>{{ trans('base.checkout_payment_paypart_mono_bank') }} monobank</b><small>{{ trans('base.checkout_payment_mono_note') }}</small></span><img class="bona-payment-logo bona-payment-logo--mono" src="{{ Vite::asset('bona-html/monobank-logo.svg') }}" alt="monobank"></label>
                        <div id="collapseMonoPartialPayment" class="bona-payment-period" @hidden($selectedPaymentType !== App\DataClasses\PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK)><label for="mono_payment_period">{{ trans('base.checkout_payment_period_label') }}</label><select id="mono_payment_period" name="mono_payment_period">@foreach(config('payment.monobank.periods', []) as $period)<option value="{{ $period }}" @selected((int) old('mono_payment_period', $checkoutMonoPeriod) === (int) $period)>{{ trans_choice('base.checkout_payment_count', $period, ['count' => $period]) }}</option>@endforeach</select></div>
                        <label class="bona-choice-card"><input type="radio" id="payment-card_paypart" name="payment_type_id" value="{{ App\DataClasses\PaymentTypesDataClass::CARD_PAYMENT_PAYPART }}" @checked($selectedPaymentType === App\DataClasses\PaymentTypesDataClass::CARD_PAYMENT_PAYPART)><span><b>{{ trans('base.checkout_payment_paypart') }} ПриватБанк</b><small>{{ trans('base.checkout_payment_privat_note') }}</small></span><img class="bona-payment-logo bona-payment-logo--privat" src="{{ Vite::asset('bona-html/privatbank-chastyny.svg') }}" alt="ПриватБанк"></label>
                        <div id="collapsePartialPayment" class="bona-payment-period" @hidden($selectedPaymentType !== App\DataClasses\PaymentTypesDataClass::CARD_PAYMENT_PAYPART)><label for="payment_period">{{ trans('base.checkout_payment_period_label') }}</label><select id="payment_period" name="payment_period">@foreach(config('payment.privatbank.periods', []) as $period)<option value="{{ $period }}" @selected((int) old('payment_period', $checkoutPrivatPeriod) === (int) $period)>{{ trans_choice('base.checkout_payment_count', $period, ['count' => $period]) }}</option>@endforeach</select></div>
                    </div>
                </section>

                <section class="bona-checkout-step bona-checkout-step--comment"><div class="bona-field"><label for="checkout-comment">{{ trans('base.checkout_order_comment') }}</label><textarea id="checkout-comment" name="comment" rows="4" maxlength="2000" placeholder="{{ trans('base.checkout_order_comment_placeholder') }}">{{ old('comment') }}</textarea></div></section>
            </div>

            <aside class="bona-order-summary bona-checkout-summary">
                <div class="bona-checkout-summary__head">
                    <div><p class="bona-commerce-kicker">{{ trans('base.checkout_summary_label') }}</p><h2>{{ trans_choice('base.checkout_summary_products', $productsCount, ['count' => $productsCount]) }}</h2></div>
                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.cart.page') }}">{{ trans('base.checkout_edit_order') }}</a>
                </div>
                <div class="bona-checkout-items">
                    @foreach($productsInCart as $product)
                        @php
                            $unitPrice = (float) $product->pivot->price + (float) ($product->pivot->attributes_price ?? 0);
                            $imageUrl = $product->pivot->current_image_path ? '/storage/'.$product->pivot->current_image_path : $product->main_image_url;
                        @endphp
                        <a class="bona-checkout-item" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $product->slug]) }}">
                            <span class="bona-checkout-item__image"><img src="{{ $imageUrl }}" alt="{{ $product->name }}"></span>
                            <span class="bona-checkout-item__body"><b>{{ $product->name }}</b><small>{{ $product->pivot->count }} × {{ $formatPrice($unitPrice) }}</small></span>
                            <strong>{{ $formatPrice($unitPrice * $product->pivot->count) }}</strong>
                        </a>
                    @endforeach
                </div>
                <div class="bona-summary-lines">
                    <div class="bona-summary-line"><span>{{ trans('base.products_price') }}</span><strong class="price-products">{{ $formatPrice($initialSummary['products']) }}</strong></div>
                    <div class="bona-summary-line"><span>{{ trans('base.delivery') }}</span><strong class="price-delivery">{{ $initialSummary['is_carrier'] ? trans('base.cart_delivery_price') : $formatPrice($initialSummary['delivery']) }}</strong></div>
                    <div class="bona-summary-line bona-summary-line--discount" data-checkout-discount-row @hidden($initialSummary['discount'] <= 0)><span>{{ trans('base.products_price_discount') }}@if($promoCode) · {{ $promoCode->code }}@endif</span><strong class="price-discount">−{{ $formatPrice($initialSummary['discount']) }}</strong></div>
                    <div class="bona-summary-line bona-summary-line--total"><span>{{ trans('base.products_price_total') }}</span><strong class="total-price-delivery">{{ $formatPrice($initialSummary['total']) }}</strong></div>
                </div>
                <div class="bona-checkout-summary__selection"><p>{{ trans('base.checkout_payment') }}: <span class="selected-payment-type">{{ $selectedPaymentLabel }}</span></p><p>{{ trans('base.delivery') }}: <span class="selected-delivery-type">{{ $selectedDeliveryLabel }}</span></p></div>
                <label class="bona-consent @error('agreement') has-error @enderror" for="checkout-order-info-form-check">
                    <input type="hidden" name="agreement" value="0"><input type="checkbox" id="checkout-order-info-form-check" name="agreement" value="1" @checked((bool) old('agreement')) required>
                    <span>{{ trans('base.checkout_by_confirm_i_agree') }} <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.static-page.page', ['staticPageSlug' => 'dogovir-publichnoyi-oferti']) }}">{{ mb_strtolower(trans('base.conditions')) }}</a></span>
                </label>
                @error('agreement')<p class="bona-consent-error">{{ $message }}</p>@enderror
                <button type="submit" class="bona-button bona-button--light bona-button--full" id="submit-button"><span>{{ trans('base.checkout_confirm_order') }}</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M14 7l5 5-5 5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                <div id="loader" class="bona-checkout-loader" role="status" hidden>{{ trans('base.checkout_processing') }}</div>
                <p class="bona-summary-note">{{ trans('base.checkout_summary_note') }}</p>
                <div class="bona-payment-marks" aria-label="{{ trans('base.payments_methods') }}"><img src="{{ Vite::asset('resources/img/payment/visa.svg') }}" alt="Visa"><img src="{{ Vite::asset('resources/img/payment/mastercard.svg') }}" alt="Mastercard"><span>LiqPay</span></div>
            </aside>
        </form>
    </div>
@endsection
