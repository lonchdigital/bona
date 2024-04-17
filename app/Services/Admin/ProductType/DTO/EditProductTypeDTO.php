<?php

namespace App\Services\Admin\ProductType\DTO;

use App\Services\Base\DTO\BaseDTO;
use Illuminate\Http\UploadedFile;

class EditProductTypeDTO implements BaseDTO
{
    public function __construct(
        public readonly array $productTypeName,
        public readonly string $slug,
        public readonly ?array $pointName,
        public readonly ?UploadedFile $image,

        public readonly array $metaTitle,
        public readonly array $metaDescription,
        public readonly array $metaKeyWords,

        public readonly array $metaProductTitle,
        public readonly array $metaProductDescription,

        public readonly bool $productTypeHasBrand,
        public readonly bool $productTypeHasColor,
        public readonly bool $productTypeHasCollection,
        public readonly bool $productTypeHasCategory,
        public readonly bool $productTypeHasSize,

        public readonly bool $productTypeHasLength,
        public readonly bool $productTypeFilterByLength,
        public readonly bool $productTypeFilterByLengthShowOnMainFilter,
        public readonly ?int $productTypeFilterByLengthFilterFullPositionId,
        public readonly ?int $productTypeFilterByLengthFilterTypeId,
        public readonly ?array $productTypeFilterByLengthOptions,
        public readonly ?array $productTypeFilterByLengthName,

        public readonly bool $productTypeHasWidth,
        public readonly bool $productTypeFilterByWidth,
        public readonly bool $productTypeFilterByWidthShowOnMainFilter,
        public readonly ?int $productTypeFilterByWidthFilterFullPositionId,
        public readonly ?int $productTypeFilterByWidthFilterTypeId,
        public readonly ?array $productTypeFilterByWidthOptions,
        public readonly ?array $productTypeFilterByWidthName,

        public readonly bool $productTypeHasHeight,
        public readonly bool $productTypeFilterByHeight,
        public readonly bool $productTypeFilterByHeightShowOnMainFilter,
        public readonly ?int $productTypeFilterByHeightFilterFullPositionId,
        public readonly ?int $productTypeFilterByHeightFilterTypeId,
        public readonly ?array $productTypeFilterByHeightOptions,
        public readonly ?array $productTypeFilterByHeightName,

        public readonly ?array $productSizePoints,
        public readonly ?array $productTypeFields,
        public readonly ?array $productTypeAttributes,

        public readonly ?array $faqs,
        public readonly ?string $additionalProducts,

        public readonly ?array $seoTitle,
        public readonly ?array $seoText,
    )
    { }
}
