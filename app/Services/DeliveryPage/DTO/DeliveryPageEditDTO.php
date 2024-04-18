<?php

namespace App\Services\DeliveryPage\DTO;

use App\Services\Base\DTO\BaseDTO;
use Illuminate\Http\UploadedFile;

class DeliveryPageEditDTO implements BaseDTO
{
    public function __construct(
        public readonly ?array $metaTitle,
        public readonly ?array $metaDescription,
        public readonly ?array $metaKeyWords,
        public readonly ?string $metaTags,
        public readonly ?array $title,
        public readonly ?array $description,
        public readonly ?array $buttonText,
        public readonly ?string $buttonUrl,
        public readonly ?UploadedFile $image,
        public readonly bool $imageDeleted,
        public readonly ?string $iframe,
    ){ }
}
