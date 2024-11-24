<?php

namespace App\Http\Actions\Store\Payment;

use App\DataClasses\OrderPaymentStatusesDataClass;
use App\DataClasses\PartialPaymentStatusDataClass;
use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Checkout\ConfirmPartialOrderRequest;
use App\Http\Resources\BaseActionResource;
use App\Models\Order;
use App\Services\Order\OrderService;

class ConfirmPartialPaymentAction extends BaseAction
{
    public function __invoke(
        ConfirmPartialOrderRequest $request,
        OrderService $orderService
    )
    {

        $order = Order::find($request->orderId);

        if (in_array($request->paymentState, [PartialPaymentStatusDataClass::SUCCESS, PartialPaymentStatusDataClass::LOCKED])) {
            $result = $orderService->updateOrderPaymentStatusId($order, OrderPaymentStatusesDataClass::STATUS_PAID);
        } elseif (in_array($request->paymentState, [PartialPaymentStatusDataClass::CANCELED, PartialPaymentStatusDataClass::FAIL])) {
            $result = $orderService->updateOrderPaymentStatusIdWithoutEmail($order, OrderPaymentStatusesDataClass::STATUS_UNPAID);
        }

        return BaseActionResource::make([
            'success' => $result->isSuccess(),
            'message' => $result->getMessage(),
            'redirect_to' => '',
        ]);
    }
}
