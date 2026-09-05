<?php

namespace App\Http\Actions\Store\Checkout\Pages;

use App\DataClasses\DeliveryTypesDataClass;
use App\DataClasses\PaymentTypesDataClass;
use App\Helpers\MultiLangRoute;
use App\Http\Actions\Admin\BaseAction;
use App\Http\Actions\Store\Cart\NeedCart;
use App\Services\Cart\CartService;
use App\Services\Currency\CurrencyService;
use App\Services\Delivery\DeliveryService;
use App\Services\Pricing\PricingService;
use App\Services\Region\RegionService;

class ShowCheckoutPage extends BaseAction
{
    use NeedCart;

    public function __invoke(
        CartService $cartService,
        RegionService $regionService,
        CurrencyService $currencyService,
        PricingService $pricingService,
        DeliveryService $deliveryService,
    ) {
        $cart = $this->getExistingCart($cartService);
        if (! $cart || ! $cart->products()->exists()) {
            return redirect()->to(MultiLangRoute::getMultiLangRoute('store.cart.page'));
        }

        $cart->loadMissing(['products', 'promoCode']);

        $paymentType = request()->integer('payment_type_id');
        $allowedPaymentTypes = PaymentTypesDataClass::get()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $paymentType = in_array($paymentType, $allowedPaymentTypes, true)
            ? $paymentType
            : PaymentTypesDataClass::CASH_PAYMENT;

        $deliveryType = (int) old('delivery_type_id', DeliveryTypesDataClass::ADDRESS_DELIVERY);
        $allowedDeliveryTypes = DeliveryTypesDataClass::get()->pluck('id')->map(fn ($id) => (int) $id)->all();
        if (! in_array($deliveryType, $allowedDeliveryTypes, true)) {
            $deliveryType = DeliveryTypesDataClass::ADDRESS_DELIVERY;
        }

        $privatPeriods = array_map('intval', config('payment.privatbank.periods', []));
        $monoPeriods = array_map('intval', config('payment.monobank.periods', []));
        $privatPeriod = request()->integer('payment_period');
        $monoPeriod = request()->integer('mono_payment_period');

        // FastSelect stores the carrier reference in the submitted input. If
        // validation sends the customer back, also restore the human-readable
        // label so a valid city or branch does not look as if it disappeared.
        $npCityInitial = old('np_city')
            ? $deliveryService->getNpCityByRef((string) old('np_city'))
            : null;
        $npDepartmentInitial = old('np_city') && old('np_department')
            ? $deliveryService->getNpDepartmentByRef((string) old('np_city'), (string) old('np_department'))
            : null;
        $satCityInitial = old('sat_city')
            ? $deliveryService->getSatCityByRef((string) old('sat_city'))->first()
            : null;
        $satDepartmentInitial = old('sat_department')
            ? $deliveryService->getSATDepartmentByRef((string) old('sat_department'))->first()
            : null;

        return view('pages.store.checkout', [
            'productsInCart' => $cartService->getProductsInCart($cart),
            'regions' => $regionService->getRegions(),
            'baseCurrency' => $currencyService->getBaseCurrency(),
            'checkoutPaymentType' => $paymentType,
            'checkoutDeliveryType' => $deliveryType,
            'checkoutPrivatPeriod' => in_array($privatPeriod, $privatPeriods, true) ? $privatPeriod : ($privatPeriods[0] ?? null),
            'checkoutMonoPeriod' => in_array($monoPeriod, $monoPeriods, true) ? $monoPeriod : ($monoPeriods[0] ?? null),
            'initialSummary' => $pricingService->forCart($cart, $deliveryType),
            'promoCode' => $cart->promoCode,
            'npCityInitial' => $npCityInitial ?: null,
            'npDepartmentInitial' => $npDepartmentInitial ?: null,
            'satCityInitial' => $satCityInitial ?: null,
            'satDepartmentInitial' => $satDepartmentInitial ?: null,
        ]);
    }
}
