<?php

namespace App\Http\Actions\Store\Checkout\Pages;

use App\DataClasses\OrderPaymentStatusesDataClass;
use App\DataClasses\PaymentTypesDataClass;
use App\Http\Actions\Admin\BaseAction;
use App\Http\Actions\Store\Cart\NeedCart;
use App\Models\Order;
use App\Models\ProductType;
use App\Services\Cart\CartService;
use App\Services\Currency\CurrencyService;
use App\Services\Pricing\PricingService;
use App\Support\Commerce\ProductBundle;

class ShowCheckoutThankYouPageAction extends BaseAction
{
    use NeedCart;

    public function __invoke(
        Order $order,
        CartService $cartService,
        CurrencyService $currencyService,
        PricingService $pricingService,
    ) {
        // Unpaid is the expected state for an invoice or a manager-confirmed
        // order. It only represents a failed checkout here for an online card
        // payment, where payment is required before confirmation.
        $onlinePaymentTypes = [
            PaymentTypesDataClass::CARD_PAYMENT,
            PaymentTypesDataClass::CARD_PAYMENT_PAYPART,
            PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK,
        ];
        $failedPaymentStatuses = [
            OrderPaymentStatusesDataClass::STATUS_UNPAID,
            OrderPaymentStatusesDataClass::STATUS_DECLINED,
            OrderPaymentStatusesDataClass::REJECTED_BY_CLIENT,
            OrderPaymentStatusesDataClass::CLIENT_PUSH_TIMEOUT,
        ];

        if (
            in_array((int) $order->payment_type_id, $onlinePaymentTypes, true)
            && in_array((int) $order->payment_status_id, $failedPaymentStatuses, true)
        ) {
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
        $paymentPendingMessage = match (true) {
            (int) $order->payment_type_id === PaymentTypesDataClass::CARD_PAYMENT_PAYPART
                && (int) $order->payment_status_id === OrderPaymentStatusesDataClass::STATUS_PAYPART => trans('base.checkout_success_privat_intro'),
            (int) $order->payment_type_id === PaymentTypesDataClass::CARD_PAYMENT
                && (int) $order->payment_status_id === OrderPaymentStatusesDataClass::STATUS_IN_PROGRESS => trans('base.checkout_success_card_pending_intro'),
            default => null,
        };

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
