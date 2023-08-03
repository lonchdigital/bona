<?php

namespace App\Services\Product\DTO;

use App\Services\Base\DTO\BaseDTO;

class SearchProductDTO implements BaseDTO
{
    public function __construct(
        public readonly ?string $query
    ){ }
}
