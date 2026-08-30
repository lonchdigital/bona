<?php

namespace App\Http\Actions\Store\Checkout\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Actions\Store\Cart\NeedCart;
use App\Models\Order;
use App\Models\ProductType;
use App\Services\Cart\CartService;
use App\Services\Currency\CurrencyService;

class ShowCheckoutThankYouMonoBankPageAction extends BaseAction
{
    use NeedCart;

    public function __invoke(
        Order $order,
        CartService $cartService,
        CurrencyService $currencyService,
    ) {
        return view('pages.store.checkout-thank-you-mono-bank', [
            'order' => $order,
            'productType' => ProductType::first(),
        ]);
    }
}
