<?php

namespace App\Services\Order\DTO;

use App\Services\Base\DTO\BaseDTO;

class UpdateOrderDTO implements BaseDTO
{
    public function __construct(
        public readonly int $statusId,
        public readonly int $orderPaymentStatusId,
    ){ }
}
