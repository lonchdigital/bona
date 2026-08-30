<?php

namespace App\Services\EmailService\DTO;

use App\Services\Base\DTO\BaseDTO;

class OrderCountDoorsDTO implements BaseDTO
{
    public function __construct(
        public readonly ?string $title,
        public readonly string $name,
        public readonly string $phone,
        public readonly bool $agree,
        public readonly ?string $currentProductTitle,
        public readonly ?string $currentProductUrl,
    ) {}
}
