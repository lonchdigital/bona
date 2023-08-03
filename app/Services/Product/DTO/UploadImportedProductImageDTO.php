<?php

namespace App\Services\Product\DTO;

use App\Services\Base\DTO\BaseDTO;
use Illuminate\Http\UploadedFile;

class UploadImportedProductImageDTO implements BaseDTO
{
    public function __construct(
        public readonly UploadedFile $image,
        public readonly int $typeId,
    ) { }
}
