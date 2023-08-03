<?php

namespace App\Services\Currency\DTO;

use App\Services\Base\DTO\BaseDTO;

class EditCurrencyDTO implements BaseDTO
{
    public function __construct(
        public readonly array $name,
        public readonly array $nameShort,
        public readonly string $code,
        public readonly bool $isBase,
        public readonly ?float $rate,
    )
    { }
}
