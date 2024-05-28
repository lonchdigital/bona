<?php

namespace App\Services\Color\DTO;

use App\Services\Base\DTO\BaseDTO;

class FilterColorAdminDTO implements BaseDTO
{
    public function __construct(
        public readonly ?string $search,
    ) { }
}
