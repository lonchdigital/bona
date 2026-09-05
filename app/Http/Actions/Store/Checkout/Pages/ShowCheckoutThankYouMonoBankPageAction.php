<?php

namespace App\Http\Actions\Store\Checkout\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Actions\Store\Cart\NeedCart;
use App\Models\Order;
use App\Models\ProductType;
use App\Services\Cart\CartService;
use App\Services\Currency\CurrencyService;
use App\Services\Pricing\PricingService;
use App\Support\Commerce\ProductBundle;

class ShowCheckoutThankYouMonoBankPageAction extends BaseAction
{
    use NeedCart;

    public function __invoke(
        Order $order,
        CartService $cartService,
        CurrencyService $currencyService,
        PricingService $pricingService,
    ) {
        $order->loadMissing([
            'products.colors',
            'products.productType.attributes',
            'promoCode',
            'region',
            'user',
        ]);

        $orderProductGroups = ProductBundle::group($order->products);

        return view('pages.store.checkout-thank-you', [
            'order' => $order,
            'orderProductGroups' => $orderProductGroups,
            'orderProductGroupsCount' => ProductBundle::countUnits($orderProductGroups),
            'baseCurrency' => $currencyService->getBaseCurrency(),
            'productType' => ProductType::first(),
            'orderSummary' => $pricingService->forOrder($order),
            'monoBankPending' => true,
        ]);
    }
}
