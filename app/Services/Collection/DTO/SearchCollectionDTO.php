<?php

namespace App\Services\Collection\DTO;

use App\Services\Base\DTO\BaseDTO;

class SearchCollectionDTO implements BaseDTO
{
    public function __construct(
        public readonly ?string $query
    ) { }
}
