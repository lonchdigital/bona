<?php

namespace App\Http\Actions\Store\Checkout\Pages;

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
    )
    {
        $cart = $this->getCart($cartService);

        return view('pages.store.checkout', [
            'productsInCart' => $cartService->getProductsInCart($cart),
            'regions' => $regionService->getRegions(),
            'baseCurrency' => $currencyService->getBaseCurrency(),
        ]);
    }
}
