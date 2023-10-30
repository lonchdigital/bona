<?php

namespace App\Services\HomePage\DTO;

use App\Services\Base\DTO\BaseDTO;
use Illuminate\Http\UploadedFile;

class HomePageEditDTO implements BaseDTO
{
    public function __construct(
//        public readonly ?UploadedFile $sliderLogo,

        public readonly ?array $slides,
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
