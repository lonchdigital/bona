<?php

namespace App\Services\Admin\ProductField\DTO;

use App\Services\Base\DTO\BaseDTO;

class EditProductFieldDTO implements BaseDTO
{
    public function __construct(
        public readonly array $productFieldName,
        public readonly string $slug,
        public readonly int $productFieldType,
        public readonly ?array $productFieldSizeName,
        public readonly ?array $productFieldOptions,
        public readonly bool $isMultiselectable,
        public readonly bool $asImage,
        public readonly ?int $numericFieldFilterType,
        public readonly ?array $numericFiledFilterOptions,
    )
    { }
}
