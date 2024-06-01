<?php

namespace App\Http\Requests\Admin\Order;

use App\DataClasses\OrderPaymentStatusesDataClass;
use App\Http\Requests\BaseRequest;
use App\Services\Order\DTO\UpdateOrderDTO;
use App\DataClasses\OrderStatusesDataClass;

class UpdateOrderRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'status_id' => [
                'required',
                'int',
                'in:'.OrderStatusesDataClass::get()->pluck('id')->implode(','),
            ],
            'order_payment_status_id' => [
                'required',
                'int',
                'in:'.OrderPaymentStatusesDataClass::get()->pluck('id')->implode(','),
            ],
        ];
    }


    public function toDTO(): UpdateOrderDTO
    {
        return new UpdateOrderDTO(
            $this->input('status_id'),
            $this->input('order_payment_status_id')
        );
    }
}
