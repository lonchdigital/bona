<?php

namespace App\Services\EmailService\DTO;

use App\Services\Base\DTO\BaseDTO;

class UserChooseDoorsDTO implements BaseDTO
{
    public function __construct(
        public readonly string|null $title,
        public readonly string $name,
        public readonly string $phone,
        public readonly bool $agree,
    ){ }
}
