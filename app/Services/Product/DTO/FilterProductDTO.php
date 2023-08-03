<?php

namespace App\Services\Product\DTO;

use App\Services\Base\DTO\BaseDTO;

class FilterProductDTO implements BaseDTO
{
    public function __construct(
        public readonly ?array $filters,
    ) { }
}
