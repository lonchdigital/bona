<?php

namespace App\Services\BlogSlides\DTO;

use App\Services\Base\DTO\BaseDTO;

class BlogSlidesEditDTO implements BaseDTO
{
    public function __construct(
        public readonly ?array $slides,
    ) { }
}
