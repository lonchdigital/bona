<?php

namespace App\Services\Country\DTO;

use App\Services\Base\DTO\BaseDTO;
use Illuminate\Http\UploadedFile;

class EditCountryDTO implements BaseDTO
{
    public function __construct(
        public readonly array $name,
        public readonly string $code,
        public readonly ?UploadedFile $image,
    )
    { }
}
