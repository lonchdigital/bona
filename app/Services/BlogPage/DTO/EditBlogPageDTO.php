<?php

namespace App\Services\BlogPage\DTO;

use App\Services\Base\DTO\BaseDTO;
use Illuminate\Http\UploadedFile;

class EditBlogPageDTO implements BaseDTO
{
    public function __construct(
        public readonly ?array $metaTitle,
        public readonly ?array $metaDescription,
        public readonly ?array $metaKeywords,
        public readonly ?array $title,
    ){ }
}
