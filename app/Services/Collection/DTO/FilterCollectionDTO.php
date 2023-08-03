<?php

namespace App\Services\Collection\DTO;

use App\Services\Base\DTO\BaseDTO;

class FilterCollectionDTO implements BaseDTO
{
    public function __construct(
        public readonly ?string $search,
        public readonly ?array $searchBrandIds,
    ) { }
}
