<?php

namespace App\Services\BlogArticle\DTO;

use App\Services\Base\DTO\BaseDTO;
use Illuminate\Http\UploadedFile;

class EditBlogArticleDTO implements BaseDTO
{
    public function __construct(
        public readonly int $categoryId,
        public readonly array $name,
        public readonly string $slug,
        public readonly ?array $metaTitle,
        public readonly ?array $metaDescription,
        public readonly ?array $metaKeywords,
        public readonly array $subTitle,
        public readonly ?UploadedFile $heroImage,
        public readonly ?array $blocks,
    ){ }
}
