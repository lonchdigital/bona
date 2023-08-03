<?php

namespace App\Http\Actions\Store\Checkout\Pages;

use App\Models\Order;
use App\Http\Actions\Admin\BaseAction;
use App\Services\Currency\CurrencyService;

class ShowCheckoutThankYouPageAction extends BaseAction
{
    public function __invoke(
        Order $order,
        CurrencyService $currencyService,
    )
    {
        return view('pages.store.checkout-thank-you', [
            'order' => $order,
            'baseCurrency' => $currencyService->getBaseCurrency(),
        ]);
    }
}
