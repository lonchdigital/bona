<?php

namespace App\Services\EmailService\DTO;

use App\Services\Base\DTO\BaseDTO;

class OrderCountDoorsDTO implements BaseDTO
{
    public function __construct(
        public readonly string|null $title,
        public readonly string $name,
        public readonly string $phone,
        public readonly bool $agree,
        public readonly string|null $currentProductTitle,
        public readonly string|null $currentProductUrl,
    ){ }
}
