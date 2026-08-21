<?php

namespace App\Services\Work\DTO;

use App\Services\Base\DTO\BaseDTO;
use Illuminate\Http\UploadedFile;

class EditWorkDTO implements BaseDTO
{
    public function __construct(
        public readonly array         $name,
        public readonly string        $slug,
        public readonly ?array        $metaTitle,
        public readonly ?array        $metaDescription,
        public readonly ?array        $metaKeyWords,
        public readonly ?UploadedFile $mainImage,
        public readonly ?array        $intro = null,
        public readonly ?array        $description = null,
        public readonly ?string       $location = null,
        public readonly ?int          $doorsCount = null,
        public readonly ?string       $duration = null,
        public readonly ?array        $clientQuote = null,
        public readonly ?string       $clientName = null,
        public readonly ?array        $serviceTitle = null,
        public readonly ?array        $serviceDescription = null,
        public readonly ?string       $priceFrom = null,
        public readonly ?string       $priceCurrency = null,
        public readonly ?array        $priceNote = null,
        public readonly bool          $isPublished = true,
        /**
         * Gallery rows as they came from the form. Each may carry an "id" for a
         * row that already exists and an "image" only when it is replaced.
         */
        public readonly ?array        $images = null,
    )
    { }
}
