<?php

namespace App\Services\EmailService\DTO;

use App\Services\Base\DTO\BaseDTO;

class UserChooseDoorsDTO implements BaseDTO
{
    public function __construct(
        public readonly ?string $title,
        public readonly string $name,
        public readonly string $phone,
        public readonly ?string $description,
        public readonly bool $agree,
    ) {}
}
