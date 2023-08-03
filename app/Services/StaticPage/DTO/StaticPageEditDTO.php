<?php

namespace App\Services\StaticPage\DTO;

use App\Services\Base\DTO\BaseDTO;

class StaticPageEditDTO implements BaseDTO
{
    public function __construct(
        public readonly array $content,
    ) { }
}
