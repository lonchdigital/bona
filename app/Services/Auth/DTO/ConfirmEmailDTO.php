<?php

namespace App\Services\Auth\DTO;

use App\Services\Base\DTO\BaseDTO;

class ConfirmEmailDTO implements BaseDTO
{
    public function __construct(
        public readonly ?string $code,
    )
    { }
}
