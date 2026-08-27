<?php

namespace App\Http\Actions\Store\Order;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Order\OneClickOrderRequest;
use App\Models\Product;
use App\Services\Order\OrderService;

class CreateOneClickOrderAction extends BaseAction
{
    public function __invoke(Product $product, OneClickOrderRequest $request, OrderService $orderService)
    {
        $dto = $request->toDTO();

        $orderService->createOneClickOrder(
            $product,
            $dto->name,
            $dto->phone,
            $this->getAuthUser()
        );

        return response()->json(['data' => ['success' => true]]);
    }
}
