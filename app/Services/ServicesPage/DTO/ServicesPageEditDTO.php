<?php

namespace App\Services\ServicesPage\DTO;

use App\Services\Base\DTO\BaseDTO;

class ServicesPageEditDTO implements BaseDTO
{
    public function __construct(
        public readonly ?array $title,
        public readonly ?array $intro,
        public readonly ?array $content,
        public readonly ?array $metaTitle,
        public readonly ?array $metaDescription,
        public readonly ?array $metaKeyWords,
        public readonly ?string $metaTags,
        public readonly ?array $faqs,
        public readonly bool $faqsManaged,
        public readonly ?array $sections,

    ) {}
}
