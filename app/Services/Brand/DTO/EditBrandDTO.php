<?php

namespace App\Services\Brand\DTO;

use App\Services\Base\DTO\BaseDTO;
use Illuminate\Http\UploadedFile;

class EditBrandDTO implements BaseDTO
{
    public function __construct(
        public readonly array $name,
        public readonly string $slug,
        public readonly array $description,
        public readonly ?UploadedFile $logo,
        public readonly ?UploadedFile $head,
        public readonly array $sliderMainText,
        public readonly array $sliderDescriptionText,
        public readonly array $slides,
    )
    { }
}
