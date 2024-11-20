<?php

namespace App\Http\Actions\Store\Checkout\Pages;

use App\DataClasses\PaymentTypesDataClass;
use App\Models\Order;
use App\Http\Actions\Admin\BaseAction;
use App\Services\Currency\CurrencyService;
use App\Models\ProductType;
use App\Services\Cart\CartService;
use App\DataClasses\OrderPaymentStatusesDataClass;
use App\Http\Actions\Store\Cart\NeedCart;
use Illuminate\Support\Facades\Log;

class ShowCheckoutThankYouPageAction extends BaseAction
{
    use NeedCart;

    public function __invoke(
        Order $order,
        CartService $cartService,
        CurrencyService $currencyService,
    )
    {

        if ($order->payment_status_id === OrderPaymentStatusesDataClass::STATUS_UNPAID && $order->payment_type_id !== PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK) {
            return view('pages.store.payment-failure');
        }

        return view('pages.store.checkout-thank-you', [
            'order' => $order,
            'baseCurrency' => $currencyService->getBaseCurrency(),
            'productType' => ProductType::first(),
        ]);
    }
}
