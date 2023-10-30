<?php

namespace App\Services\Admin\ProductSubtype\DTO;

use App\Services\Base\DTO\BaseDTO;
use Illuminate\Http\UploadedFile;

class EditProductSubtypeDTO implements BaseDTO
{
    public function __construct(
        public readonly array $productSubtypeName,
        public readonly string $slug,
    )
    { }
}
