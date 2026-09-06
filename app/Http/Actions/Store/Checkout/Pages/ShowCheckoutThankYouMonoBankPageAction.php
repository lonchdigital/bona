<?php

namespace App\Http\Actions\Store\Checkout\Pages;

use App\DataClasses\OrderPaymentStatusesDataClass;
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
        if (in_array((int) $order->payment_status_id, [
            OrderPaymentStatusesDataClass::STATUS_UNPAID,
            OrderPaymentStatusesDataClass::STATUS_DECLINED,
            OrderPaymentStatusesDataClass::REJECTED_BY_CLIENT,
            OrderPaymentStatusesDataClass::CLIENT_PUSH_TIMEOUT,
        ], true)) {
            return view('pages.store.payment-failure', [
                'productType' => ProductType::first(),
                'order' => $order,
            ]);
        }

        $order->loadMissing([
            'products.colors',
            'products.productType.attributes',
            'promoCode',
            'region',
            'user',
        ]);

        $orderProductGroups = ProductBundle::group($order->products);
        $paymentPendingMessage = (int) $order->payment_status_id === OrderPaymentStatusesDataClass::STATUS_IN_PROGRESS
            ? trans('base.checkout_success_mono_intro')
            : null;

        return view('pages.store.checkout-thank-you', [
            'order' => $order,
            'orderProductGroups' => $orderProductGroups,
            'orderProductGroupsCount' => ProductBundle::countUnits($orderProductGroups),
            'baseCurrency' => $currencyService->getBaseCurrency(),
            'productType' => ProductType::first(),
            'orderSummary' => $pricingService->forOrder($order),
            'paymentPendingMessage' => $paymentPendingMessage,
        ]);
    }
}
