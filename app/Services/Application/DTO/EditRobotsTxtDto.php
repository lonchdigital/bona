<?php

namespace App\Services\Application\DTO;

use App\Services\Base\DTO\BaseDTO;

class EditRobotsTxtDto implements BaseDTO
{
    public function __construct(
        public readonly string $content,
    ) { }
}
