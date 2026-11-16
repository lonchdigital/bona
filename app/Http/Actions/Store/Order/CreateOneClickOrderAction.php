<?php

namespace App\Http\Actions\Store\Order;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Order\OneClickOrderRequest;
use App\Models\Product;
use App\Services\Order\OrderService;
use App\Services\SerpAgent\SerpAgentConversionService;

class CreateOneClickOrderAction extends BaseAction
{
    public function __invoke(Product $product, OneClickOrderRequest $request, OrderService $orderService, SerpAgentConversionService $conversions)
    {
        $dto = $request->toDTO();

        $order = $orderService->createOneClickOrder(
            $product,
            $dto->name,
            $dto->phone,
            $this->getAuthUser(),
            $dto->sourceUrl,
        );

        $conversions->reportAccepted($request, 'one-click');

        return response()->json(['data' => ['success' => true]]);
    }
}
