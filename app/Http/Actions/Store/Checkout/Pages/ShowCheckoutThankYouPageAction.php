<?php

namespace App\Http\Actions\Store\Checkout\Pages;

use App\DataClasses\OrderPaymentStatusesDataClass;
use App\Http\Actions\Admin\BaseAction;
use App\Http\Actions\Store\Cart\NeedCart;
use App\Models\Order;
use App\Models\ProductType;
use App\Services\Cart\CartService;
use App\Services\Currency\CurrencyService;

class ShowCheckoutThankYouPageAction extends BaseAction
{
    use NeedCart;

    public function __invoke(
        Order $order,
        CartService $cartService,
        CurrencyService $currencyService,
    ) {

        if ($order->payment_status_id === OrderPaymentStatusesDataClass::STATUS_UNPAID) {
            return view('pages.store.payment-failure');
        }

        return view('pages.store.checkout-thank-you', [
            'order' => $order,
            'baseCurrency' => $currencyService->getBaseCurrency(),
            'productType' => ProductType::first(),
        ]);
    }
}
