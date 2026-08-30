<?php

namespace App\Http\Actions\Store\Payment\Pages;

use App\DataClasses\OrderPaymentStatusesDataClass;
use App\Http\Actions\Admin\BaseAction;
use App\Models\Order;
use App\Services\Order\OrderAccessUrlService;
use App\Services\Order\OrderService;
use App\Services\Payment\PaymentService;

class ShowLiqPayPaymentOrdinaryPageAction extends BaseAction
{
    public function __invoke(
        Order $order,
        PaymentService $paymentService,
        OrderService $orderService,
        OrderAccessUrlService $orderAccessUrlService,
    ) {
        if ($order->payment_status_id === OrderPaymentStatusesDataClass::STATUS_PAID) {
            return redirect()->to($orderAccessUrlService->thankYou($order));
        }

        $data = $paymentService->payByCardForm($orderService->getOrderSummary($order)['total'], $order->id);

        return view('pages.store.payment', [
            'data' => $data['data'],
            'signature' => $data['signature'],
        ]);
    }
}
