<?php

namespace App\Services\Auth\DTO;

use App\Services\Base\DTO\BaseDTO;

class SignInDTO implements BaseDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly bool $rememberMe,
    )
    { }
}
