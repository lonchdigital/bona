<?php

namespace App\Services\Auth\DTO;

use App\Services\Base\DTO\BaseDTO;

class SignUpDTO implements BaseDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $phone,
        public readonly string $password,
    )
    {
    }
}
