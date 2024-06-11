<?php

namespace App\Http\Actions\Store\Payment\Pages;

use App\DataClasses\OrderPaymentStatusesDataClass;
use App\Models\Order;
use App\Services\Order\OrderService;
use App\Http\Actions\Admin\BaseAction;
use App\Services\Payment\PaymentService;

class ShowLiqPayPaymentPaypartPageAction extends BaseAction
{
    public function __invoke(
        Order $order,
        PaymentService $paymentService,
        OrderService $orderService
    )
    {
        if ($order->payment_status_id === OrderPaymentStatusesDataClass::STATUS_PAID) {
            return response()->redirectToRoute('store.checkout.thank-you', ['order' => $order->id]);
        }

        $data = $paymentService->paypartByCardForm($order->summary, $order->id);

        return view('pages.store.payment', [
            'data' => $data['data'],
            'signature' => $data['signature'],
        ]);
    }
}
