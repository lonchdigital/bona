<?php

namespace App\Services\UserProfile\DTO;

use App\Services\Base\DTO\BaseDTO;

class PasswordEditDTO implements BaseDTO
{
    public function __construct(
        public readonly string $currentPassword,
        public readonly string $newPassword,
        public readonly string $passwordConfirmation
    )
    { }
}
