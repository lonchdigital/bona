<?php

namespace App\Services\Cart\DTO;

use App\Services\Base\DTO\BaseDTO;

class DeleteProductFromCartDTO implements BaseDTO
{
    public function __construct(
        public readonly ?array $productAttributes,
    ){ }
}
