<?php

namespace App\Services\Product\DTO;

use App\Services\Base\DTO\BaseDTO;
use Illuminate\Http\UploadedFile;

class UploadProductsImportFileDTO implements BaseDTO
{
    public function __construct(
        public readonly UploadedFile $file,
    ){ }
}
