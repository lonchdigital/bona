<?php

namespace App\Http\Actions\Store\Payment;

use App\DataClasses\OrderPaymentStatusesDataClass;
use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Checkout\ConfirmMonoBankPartialOrderRequest;
use App\Models\Order;
use App\Services\Order\OrderService;
use Illuminate\Support\Facades\Log;

class ConfirmMonoBankPartialPaymentAction extends BaseAction
{
    public function __invoke(
        ConfirmMonoBankPartialOrderRequest $request,
        OrderService $orderService
    )
    {

        $order = Order::where('mono_order_id', $request->order_id)->first();

        // CLIENT_APPROVED_PUSH
        if ( $request->order_sub_state === 'WAITING_FOR_STORE_CONFIRM' ) {
            $orderService->updateOrderPaymentStatusId($order, OrderPaymentStatusesDataClass::STATUS_PAID);
        } elseif ( $request->state === 'REJECTED_BY_CLIENT' ) {
            $orderService->updateOrderPaymentStatusIdWithoutEmail($order, OrderPaymentStatusesDataClass::REJECTED_BY_CLIENT);
        } elseif ( $request->state === 'CLIENT_PUSH_TIMEOUT' ) {
            $orderService->updateOrderPaymentStatusIdWithoutEmail($order, OrderPaymentStatusesDataClass::CLIENT_PUSH_TIMEOUT);
        }

        return '';
    }
}
