<?php

namespace App\Http\Actions\Admin\Orders;

use App\Http\Actions\Admin\BaseAction;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\Order\OrderService;

class DeleteOrderAction extends BaseAction
{
    public function __invoke(Order $order, Request $request, OrderService $orderService)
    {
        $result = $orderService->deleteOrder($order);

        return $this->handleActionResult(route('admin.order.list.page'), $request, $result);
    }
}
