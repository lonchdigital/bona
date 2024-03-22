<?php

namespace App\Services\ProductCategory\DTO;

use App\Services\Base\DTO\BaseDTO;
use Illuminate\Http\UploadedFile;

class CreateCategoryDTO implements BaseDTO
{
    public function __construct(
        public readonly ?array $metaTitle,
        public readonly ?array $metaDescription,
        public readonly ?array $metaKeyWords,
        public readonly array $name,
        public readonly string $slug,
        public readonly ?UploadedFile $image,
    )
    { }
}
