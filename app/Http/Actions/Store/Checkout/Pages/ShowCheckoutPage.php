<?php

namespace App\Http\Actions\Store\Checkout\Pages;

use App\DataClasses\PaymentTypesDataClass;
use App\Http\Actions\Admin\BaseAction;
use App\Http\Actions\Store\Cart\NeedCart;
use App\Services\Cart\CartService;
use App\Services\Currency\CurrencyService;
use App\Services\Delivery\DeliveryService;
use App\Services\Region\RegionService;

class ShowCheckoutPage extends BaseAction
{
    use NeedCart;

    public function __invoke(
        CartService $cartService,
        RegionService $regionService,
        CurrencyService $currencyService,
        DeliveryService $deliveryService,
    ) {
        $cart = $this->getCart($cartService);
        $paymentType = request()->integer('payment_type_id');
        $allowedPaymentTypes = [
            PaymentTypesDataClass::CARD_PAYMENT_PAYPART,
            PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK,
        ];
        $paymentType = in_array($paymentType, $allowedPaymentTypes, true) ? $paymentType : null;

        $privatPeriods = array_map('intval', config('payment.privatbank.periods', []));
        $monoPeriods = array_map('intval', config('payment.monobank.periods', []));
        $privatPeriod = request()->integer('payment_period');
        $monoPeriod = request()->integer('mono_payment_period');

        return view('pages.store.checkout', [
            'productsInCart' => $cartService->getProductsInCart($cart),
            'regions' => $regionService->getRegions(),
            'baseCurrency' => $currencyService->getBaseCurrency(),
            'checkoutPaymentType' => $paymentType,
            'checkoutPrivatPeriod' => in_array($privatPeriod, $privatPeriods, true) ? $privatPeriod : ($privatPeriods[0] ?? null),
            'checkoutMonoPeriod' => in_array($monoPeriod, $monoPeriods, true) ? $monoPeriod : ($monoPeriods[0] ?? null),
        ]);
    }
}
