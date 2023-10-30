<?php

namespace App\Services\Product\DTO;

use App\Services\Base\DTO\BaseDTO;

class FilterProductAdminDTO implements BaseDTO
{
    public function __construct(
        public readonly ?string $search,
        public readonly ?int $brandId,
        public readonly ?int $colorId,
        public readonly ?int $collectionId,
        public readonly ?int $countryId,
        public readonly ?int $categoryId,
    ) { }
}
