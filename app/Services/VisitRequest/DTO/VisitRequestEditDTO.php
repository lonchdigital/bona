<?php

namespace App\Services\VisitRequest\DTO;

use App\Services\Base\DTO\BaseDTO;

class VisitRequestEditDTO implements BaseDTO
{
    public function __construct(
        public readonly int $statusId,
    ) { }
}
