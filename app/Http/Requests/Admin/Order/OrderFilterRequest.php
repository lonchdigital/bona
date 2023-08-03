<?php

namespace App\Http\Requests\Admin\Order;

use App\Http\Requests\BaseRequest;
use App\Services\Order\DTO\OrderFilterDTO;
use App\DataClasses\OrderStatusesDataClass;

class OrderFilterRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'status_id' => [
                'nullable',
                'in:'. OrderStatusesDataClass::get()->pluck('id')->implode(',')
            ],
        ];
    }

    public function toDTO(): OrderFilterDTO
    {
        return new OrderFilterDTO(
            $this->input('status_id')
        );
    }
}
