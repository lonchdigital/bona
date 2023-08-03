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
        public readonly ?int          $parentProductId,
        public readonly int           $availabilityStatusId,
        public readonly ?array        $specialOfferIds,
        public readonly string        $sku,
        public readonly ?float        $oldPriceInCurrency,
        public readonly float         $priceInCurrency,
        public readonly float         $purchasePriceInCurrency,
        public readonly int           $currencyId,
        public readonly ?UploadedFile $mainImage,
        public readonly bool          $mainImageDeleted,
        public readonly ?UploadedFile $patternImage,
        public readonly bool          $patternImageDeleted,
        public readonly ?UploadedFile $galleryImage1,
        public readonly bool          $galleryImage1Deleted,
        public readonly ?UploadedFile $galleryImage2,
        public readonly bool          $galleryImage2Deleted,
        public readonly ?UploadedFile $galleryImage3,
        public readonly bool          $galleryImage3Deleted,
        public readonly ?UploadedFile $galleryImage4,
        public readonly bool          $galleryImage4Deleted,
        public readonly ?UploadedFile $galleryImage5,
        public readonly bool          $galleryImage5Deleted,
        public readonly int           $countryId,
        public readonly ?int          $brandId,
        public readonly ?int          $collectionId,
        public readonly ?array        $categoryIds,
        public readonly ?int          $colorId,
        public readonly ?array        $allColorIds,
        public readonly ?array        $customFields,
        public readonly ?float        $length,
        public readonly ?float        $width,
        public readonly ?float        $height,
    ) { }
}
