<?php

namespace App\Services\Seogen\DTO;

use App\Services\Base\DTO\BaseDTO;

class EditSeogenDTO implements BaseDTO
{
    public function __construct(
        public readonly array $productCategory,
        public readonly array $product,
        public readonly array $brandTitleTag,
        public readonly array $brandH1Tag,
        public readonly array $brandMetaTitleTag,
        public readonly array $brandMetaDescriptionTag,
        public readonly array $brandMetaKeywordsTag,
    ) { }
}
