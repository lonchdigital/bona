<?php

namespace App\Services\Admin\ProductAttribute\DTO;

use App\Services\Base\DTO\BaseDTO;

class EditProductAttributeDTO implements BaseDTO
{
    public function __construct(
        public readonly array $productAttributeName,
        public readonly string $slug,
    )
    { }
}
