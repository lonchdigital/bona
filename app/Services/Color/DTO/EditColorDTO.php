<?php

namespace App\Services\Color\DTO;

use App\Services\Base\DTO\BaseDTO;

class EditColorDTO implements BaseDTO
{
    public function __construct(
        public readonly array   $name,
        public readonly string  $slug,
        public readonly ?string $hex,
        public readonly ?int    $parentColorId,
    )
    { }
}
