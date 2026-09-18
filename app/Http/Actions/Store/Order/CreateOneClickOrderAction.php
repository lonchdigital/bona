<?php

namespace App\Http\Actions\Store\Order;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Order\OneClickOrderRequest;
use App\Jobs\ReportSerpAgentConversion;
use App\Models\Product;
use App\Services\Order\OrderService;

class CreateOneClickOrderAction extends BaseAction
{
    public function __invoke(Product $product, OneClickOrderRequest $request, OrderService $orderService)
    {
        $dto = $request->toDTO();

        $order = $orderService->createOneClickOrder(
            $product,
            $dto->name,
            $dto->phone,
            $this->getAuthUser(),
            $dto->sourceUrl,
        );

        // Queued: the buyer waits for the order, not for Serp Agent.
        dispatch(ReportSerpAgentConversion::forOrder(
            $order->id,
            $dto->sourceUrl ?: $request->fullUrl(),
            $request->landingUrl(),
            $order->created_at,
        ));

        return response()->json(['data' => ['success' => true]]);
    }
}
