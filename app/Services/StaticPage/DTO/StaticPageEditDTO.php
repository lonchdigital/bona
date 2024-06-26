<?php

namespace App\Services\StaticPage\DTO;

use App\Services\Base\DTO\BaseDTO;

class StaticPageEditDTO implements BaseDTO
{
    public function __construct(
        public readonly ?array $meta_title,
        public readonly ?array $meta_description,
        public readonly ?array $meta_keywords,
        public string|array|null $meta_tags,
        public readonly array $content,
    ) { }
}
