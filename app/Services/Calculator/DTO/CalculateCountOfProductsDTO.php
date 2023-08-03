<?php

namespace App\Services\Calculator\DTO;

use App\Services\Base\DTO\BaseDTO;

class CalculateCountOfProductsDTO implements BaseDTO
{
    public function __construct(
        public readonly ?int $productId,
        public readonly float $wallpaperWidth,
        public readonly float $wallpaperLength,
        public readonly array $walls,
        public readonly ?array $windows,
        public readonly ?array $doors,
    ) { }
}
