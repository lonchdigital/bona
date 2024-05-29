<?php

namespace App\Services\VisitRequest\DTO;

use App\Services\Base\DTO\BaseDTO;

class VisitRequestCreateDTO implements BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $phone,
    ) { }
}
