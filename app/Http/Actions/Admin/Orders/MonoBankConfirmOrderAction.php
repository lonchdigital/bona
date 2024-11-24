<?php

namespace App\Http\Actions\Admin\Orders;

use App\Http\Actions\Admin\BaseAction;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\Payment\PaymentMonoBankService;

class MonoBankConfirmOrderAction extends BaseAction
{
    public function __invoke(Order $order, Request $request, PaymentMonoBankService $paymentMonoBankService)
    {
        $result = $paymentMonoBankService->confirmOrderMonoBank($order);

        return $this->handleActionResult(route('admin.order.list.page'), $request, $result);
    }
}
