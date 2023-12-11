<?php

namespace App\Services\Product\DTO;

use App\Services\Base\DTO\BaseDTO;
use Illuminate\Http\UploadedFile;

class EditProductDTO implements BaseDTO
{
    public function __construct(
        public readonly bool          $isActive,
        public readonly array         $name,
        public readonly string        $slug,
        public readonly ?array         $metaTitle,
        public readonly ?array         $metaDescription,
        public readonly ?array         $metaKeyWords,
        public readonly ?array        $selectedSubProductsId,
        public readonly int           $availabilityStatusId,
        public readonly ?array        $specialOfferIds,
        public readonly ?string       $sku,
        public readonly float         $priceInCurrency,
        public readonly int           $currencyId,
        public readonly ?array        $productText,
        public readonly ?UploadedFile $mainImage,
        public readonly bool          $mainImageDeleted,
        public readonly ?array        $gallery,
        public readonly ?array        $characteristics,
        public readonly ?array        $videos,
        public readonly ?array        $attributes,
        public readonly ?int          $countryId, // added ?
        public readonly ?int          $brandId,
        public readonly ?int          $collectionId,
        public readonly ?array        $categoryIds,
        public readonly ?int          $colorId,
        public readonly ?array        $allColorIds,
        public readonly ?array        $customFields,
        public readonly ?float        $length,
        public readonly ?float        $width,
        public readonly ?float        $height,
        public readonly ?array        $faqs,
        public readonly ?array        $seoTitle,
        public readonly ?array        $seoText,
    ) { }
}
