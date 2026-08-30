<?php

namespace App\Services\Author\DTO;

use App\Services\Base\DTO\BaseDTO;
use Illuminate\Http\UploadedFile;

class EditAuthorDTO implements BaseDTO
{
    public function __construct(
        public readonly array $name,
        public readonly string $slug,
        public readonly ?array $jobTitle,
        public readonly ?array $shortDescription,
        public readonly ?array $biography,
        public readonly ?UploadedFile $photo,
        public readonly ?string $instagramUrl,
        public readonly ?string $facebookUrl,
        public readonly ?string $linkedinUrl,
        public readonly ?array $metaTitle,
        public readonly ?array $metaDescription,
        public readonly ?array $metaKeywords,
        /**
         * Certificates as they came from the form. Each entry may carry an "id"
         * for a row that already exists and an "image" only when it is being
         * replaced.
         */
        public readonly ?array $certificates,
    ) {}
}
