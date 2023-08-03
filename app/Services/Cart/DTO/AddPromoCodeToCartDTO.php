<?php

namespace App\Services\Cart\DTO;

use App\Services\Base\DTO\BaseDTO;

class AddPromoCodeToCartDTO implements BaseDTO
{
    public function __construct(
        public readonly string $code,
    ) { }
}
