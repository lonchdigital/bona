<?php

namespace App\Services\Product\DTO;

class ProductImportFilterDTO extends FilterProductAdminDTO
{
    public function __construct(
        public readonly ?string $search,
        public readonly ?int $brandId,
        public readonly ?int $colorId,
        public readonly ?int $collectionId,
        public readonly ?int $countryId,
        public readonly ?int $mainImageStatusId,
        public readonly ?int $patternImageStatusId,
        public readonly ?int $galleryImage1StatusId,
        public readonly ?int $galleryImage2StatusId,
        public readonly ?int $galleryImage3StatusId,
        public readonly ?int $galleryImage4StatusId,
        public readonly ?int $galleryImage5StatusId,
        public readonly bool $showNew,
        public readonly bool $showExisting,
    ) { }
}
