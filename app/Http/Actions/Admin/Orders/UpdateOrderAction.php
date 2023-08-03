<?php

namespace App\Http\Actions\Admin\Orders;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\Order\UpdateOrderRequest;
use App\Models\Order;
use App\Services\Order\OrderService;

class UpdateOrderAction extends BaseAction
{
    public function __invoke(Order $order, UpdateOrderRequest $request, OrderService $orderService)
    {
        $result = $orderService->updateOrder($order, $request->toDTO());

        return $this->handleActionResult(route('admin.order.list.page'), $request, $result);
    }
}
