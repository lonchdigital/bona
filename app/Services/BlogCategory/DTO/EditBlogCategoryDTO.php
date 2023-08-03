<?php

namespace App\Services\BlogCategory\DTO;

use App\Services\Base\DTO\BaseDTO;

class EditBlogCategoryDTO implements BaseDTO
{
    public function __construct(
        public readonly array $name,
        public readonly string $slug,
    ) { }
}
