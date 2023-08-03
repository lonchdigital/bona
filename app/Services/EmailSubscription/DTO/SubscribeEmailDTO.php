<?php

namespace App\Services\EmailSubscription\DTO;

use App\Services\Base\DTO\BaseDTO;

class SubscribeEmailDTO implements BaseDTO
{
    public function __construct(
        public readonly string $email,
    ){ }
}
