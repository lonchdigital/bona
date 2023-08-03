<?php

namespace App\Services\Cart\DTO;

use App\Services\Base\DTO\BaseDTO;

class ChangeProductCountInCartDTO implements BaseDTO
{
    public function __construct(
        public readonly int $productCount,
    ){ }
}
