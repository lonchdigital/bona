<?php

namespace App\Services\FilterGroups\DTO;

use App\Services\Base\DTO\BaseDTO;

class FilterGroupEditDTO implements BaseDTO
{
    public function __construct(
        public readonly int $productTypeId,
        public readonly string $slug,
        public readonly ?int $priceFrom,
        public readonly ?int $priceTo,
        public readonly ?array $countryIds,
        public readonly ?array $customFields,
        public readonly array $name,
        public readonly array $titleTag,
        public readonly array $metaTitle,
        public readonly array $metaDescription,
        public readonly array $metaKeywords,
        public readonly ?array $colorIds,
        public readonly ?array $brandIds,
        public readonly ?int $lengthFrom,
        public readonly ?int $lengthTo,
        public readonly ?array $lengthOptions,
        public readonly ?int $widthFrom,
        public readonly ?int $widthTo,
        public readonly ?array $widthOptions,
        public readonly ?int $heightFrom,
        public readonly ?int $heightTo,
        public readonly ?array $heightOptions,
    ) { }
}
