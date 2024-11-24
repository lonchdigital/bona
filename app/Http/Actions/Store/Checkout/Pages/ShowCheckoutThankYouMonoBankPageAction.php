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

class ShowCheckoutThankYouMonoBankPageAction extends BaseAction
{
    use NeedCart;

    public function __invoke(
        Order $order,
        CartService $cartService,
        CurrencyService $currencyService,
    )
    {
        return view('pages.store.checkout-thank-you-mono-bank', [
            'order' => $order,
            'productType' => ProductType::first(),
        ]);
    }
}
