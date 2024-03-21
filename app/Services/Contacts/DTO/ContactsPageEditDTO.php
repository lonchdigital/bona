<?php

namespace App\Services\Contacts\DTO;

use App\Services\Base\DTO\BaseDTO;
use Illuminate\Http\UploadedFile;

class ContactsPageEditDTO implements BaseDTO
{
    public function __construct(
        public readonly ?array $metaTitle,
        public readonly ?array $metaDescription,
        public readonly ?array $metaKeyWords,

        public readonly ?array $cityOne,
        public readonly ?array $addressOne,
        public readonly ?array $phoneOne,
        public readonly ?array $emailOne,
        public readonly ?string $iframeAddressOne,

        public readonly ?array $cityTwo,
        public readonly ?array $addressTwo,
        public readonly ?array $phoneTwo,
        public readonly ?array $emailTwo,
        public readonly ?string $iframeAddressTwo,

        public readonly ?array $cityThree,
        public readonly ?array $addressThree,
        public readonly ?array $phoneThree,
        public readonly ?array $emailThree,
        public readonly ?string $iframeAddressThree,
    ){ }
}
