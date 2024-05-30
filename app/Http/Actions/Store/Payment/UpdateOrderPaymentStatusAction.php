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
        dd('77 77 7 999999');

        $result = $orderService->updateOrderPaymentStatusId($order, OrderPaymentStatusesDataClass::STATUS_PAID);

        return BaseActionResource::make([
            'success' => $result->isSuccess(),
            'message' => $result->getMessage(),
            'redirect_to' => '',
        ]);
    }
}
