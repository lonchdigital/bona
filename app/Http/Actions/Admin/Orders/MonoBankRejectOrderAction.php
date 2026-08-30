<?php

namespace App\Http\Actions\Admin\Orders;

use App\Http\Actions\Admin\BaseAction;
use App\Models\Order;
use App\Services\Payment\PaymentMonoBankService;
use Illuminate\Http\Request;

class MonoBankRejectOrderAction extends BaseAction
{
    public function __invoke(Order $order, Request $request, PaymentMonoBankService $paymentMonoBankService)
    {
        //        $result = $orderService->deleteOrder($order);
        $result = $paymentMonoBankService->rejectOrderMonoBank($order);

        return $this->handleActionResult(route('admin.order.list.page'), $request, $result);
    }
}
