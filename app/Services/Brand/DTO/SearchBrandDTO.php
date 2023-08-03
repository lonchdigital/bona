<?php

namespace App\Services\Brand\DTO;

use App\Services\Base\DTO\BaseDTO;

class SearchBrandDTO implements BaseDTO
{
    public function __construct(
        public readonly ?string $search
    ){ }
}
