<?php

namespace App\Services\Delivery\DTO;

use App\Services\Base\DTO\BaseDTO;

class GetNpCitiesDTO implements BaseDTO
{
    public function __construct(
        public readonly ?string $query,
        public readonly ?string $locale
    )
    { }
}
