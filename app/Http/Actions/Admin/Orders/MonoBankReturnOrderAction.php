<?php

namespace App\Http\Actions\Admin\Orders;

use App\Http\Actions\Admin\BaseAction;
use App\Models\Order;
use App\Services\Payment\PaymentMonoBankService;
use Illuminate\Http\Request;

class MonoBankReturnOrderAction extends BaseAction
{
    public function __invoke(Order $order, Request $request, PaymentMonoBankService $paymentMonoBankService)
    {
        $result = $paymentMonoBankService->returnOrderMonoBank($order);

        return $this->handleActionResult(route('admin.order.list.page'), $request, $result);
    }
}
