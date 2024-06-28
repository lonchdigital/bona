<?php

namespace App\Services\HomePage\DTO;

use App\Services\Base\DTO\BaseDTO;
use Illuminate\Http\UploadedFile;

class HomePageEditDTO implements BaseDTO
{
    public function __construct(
        public readonly ?array $metaTitle,
        public readonly ?array $metaDescription,
        public readonly ?array $metaKeyWords,
        public readonly ?string $metaTags,
        public readonly ?array $slides,
        public readonly ?array $selectedProductTypes,
        public readonly ?array $selectedProductsId,
        public readonly ?array $selectedBestSalesProductsId,
        public readonly ?array $testimonials,
        public readonly ?array $faqs,
        public readonly ?array $seoTitle,
        public readonly ?array $seoText,

//        public readonly int $selectedFieldId,
//        public readonly array $selectedOptionsId,
    ){ }
}
