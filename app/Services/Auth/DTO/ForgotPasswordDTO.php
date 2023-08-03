<?php

namespace App\Services\Auth\DTO;

use App\Services\Base\DTO\BaseDTO;

class ForgotPasswordDTO implements BaseDTO
{
    public function __construct(
        public readonly string $email,
    )
    { }
}
