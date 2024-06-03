<?php

namespace App\Services\UserProfile\DTO;

use App\Services\Base\DTO\BaseDTO;

class UserProfileUpdateDTO implements BaseDTO
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
//        public readonly string $email,
        public readonly string $phone,
    )
    { }
}
