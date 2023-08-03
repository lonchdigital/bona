<?php

namespace App\Services\Order\DTO;

use App\Services\Base\DTO\BaseDTO;

class OrderFilterDTO implements BaseDTO
{
    public function __construct(
        public readonly ?int $statusId,
    ) { }
}
