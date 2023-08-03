<?php

namespace App\Http\Requests\Store\Cart;

use App\DataClasses\DeliveryTypesDataClass;
use App\Http\Requests\BaseRequest;
use App\Services\Cart\DTO\GetProductsSummaryWithDeliveryDTO;

class GetProductsSummaryWithDeliveryRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'delivery_type_id' => [
                'required',
                'int',
                'in:' . DeliveryTypesDataClass::get()->pluck('id')->implode(','),
            ],
        ];
    }

    public function toDTO(): GetProductsSummaryWithDeliveryDTO
    {
        return new GetProductsSummaryWithDeliveryDTO(
            $this->input('delivery_type_id')
        );
    }
}
