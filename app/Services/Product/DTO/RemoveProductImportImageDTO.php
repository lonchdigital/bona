<?php

namespace App\Services\Product\DTO;

use App\Services\Base\DTO\BaseDTO;

class RemoveProductImportImageDTO implements BaseDTO
{
    public function __construct(
        public readonly int $typeId,
    ) { }
}
