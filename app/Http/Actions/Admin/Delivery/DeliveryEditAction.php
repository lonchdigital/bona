<?php

namespace App\Http\Actions\Admin\Delivery;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\DeliveryPage\DeliveryPageEditRequest;
use App\Services\DeliveryPage\DeliveryPageService;

class DeliveryEditAction extends BaseAction
{
    public function __invoke(
        DeliveryPageEditRequest $request,
        DeliveryPageService $deliveryPageService,
    )
    {
        $result = $deliveryPageService->editDeliveryPage($request->toDTO());

        return $this->handleActionResult(route('admin.pages.list.page'), $request, $result);
    }
}

