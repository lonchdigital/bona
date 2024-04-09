<?php

namespace App\Http\Actions\Store\Delivery;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Resources\BaseListResource;
use App\Services\Delivery\DeliveryService;
use Illuminate\Http\Request;

class GetSATCitiesAction extends BaseAction
{
    public function __invoke(Request $request, DeliveryService $deliveryService)
    {
        BaseListResource::withoutWrapping();
        return BaseListResource::collection($deliveryService->getSatCities($request->input('query') ?? ''));
    }
}
