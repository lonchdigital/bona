<?php

namespace App\Http\Actions\Store\Delivery;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\NovaPoshta\GetNpCitiesRequest;
use App\Http\Resources\BaseListResource;
use App\Services\Delivery\DeliveryService;
use Illuminate\Http\Request;

class GetNPCitiesAction extends BaseAction
{
    public function __invoke(GetNpCitiesRequest $request, DeliveryService $deliveryService)
    {
        BaseListResource::withoutWrapping();
        $npCityDTO = $request->toDTO();
        return BaseListResource::collection(
            $deliveryService->getNpCities(
                $npCityDTO->query?? ''
            )
        );
    }
}
