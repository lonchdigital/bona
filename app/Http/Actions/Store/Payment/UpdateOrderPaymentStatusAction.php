<?php

namespace App\Http\Actions\Store\Payment;

use App\Http\Resources\BaseActionResource;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\Order\OrderService;
use App\Http\Actions\Admin\BaseAction;
use App\DataClasses\OrderPaymentStatusesDataClass;

class UpdateOrderPaymentStatusAction extends BaseAction
{
    public function __invoke(
        Order $order,
        Request $request,
        OrderService $orderService,
    )
    {

//        $result = $orderService->updateOrderPaymentStatusId($order, OrderPaymentStatusesDataClass::STATUS_PAID);
        $result = $orderService->updateOrderPaymentStatusId($order, $request->statusId);

        return BaseActionResource::make([
            'success' => $result->isSuccess(),
            'message' => $result->getMessage(),
            'redirect_to' => '',
        ]);
    }
}
