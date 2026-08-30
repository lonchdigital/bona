<?php

namespace App\Services\Order\DTO;

use App\Services\Base\DTO\BaseDTO;

class OneClickOrderDTO implements BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $phone,
    ) {}
}
