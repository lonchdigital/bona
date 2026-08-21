<?php

namespace App\Services\AboutUsPage\DTO;

use App\Services\Base\DTO\BaseDTO;
use Illuminate\Http\UploadedFile;

class AboutUsPageEditDTO implements BaseDTO
{
    public function __construct(
        public readonly ?array $metaTitle,
        public readonly ?array $metaDescription,
        public readonly ?array $metaKeyWords,
        public readonly ?string $metaTags,
        public readonly ?array $title,
        public readonly ?array $description,
        public readonly ?array $buttonText,
        public readonly ?string $buttonUrl,
        public readonly ?UploadedFile $image,
        public readonly bool $imageDeleted,
        public readonly ?string $iframe,

        public readonly ?array $factsTitle = null,
        public readonly ?array $historyTitle = null,
        public readonly ?array $historyText = null,
        public readonly ?array $stepsTitle = null,
        public readonly ?array $teamTitle = null,
        public readonly ?array $ctaTitle = null,
        public readonly ?array $ctaText = null,
        public readonly ?array $ctaButtonText = null,
        public readonly ?string $ctaButtonUrl = null,

        /** Repeatable blocks as they arrived from the form. */
        public readonly ?array $facts = null,
        public readonly ?array $steps = null,
        public readonly ?array $teamMembers = null,
    ){ }
}
