<?php

namespace App\Services\HomePage\DTO;

use App\Services\Base\DTO\BaseDTO;
use Illuminate\Http\UploadedFile;

class HomePageEditDTO implements BaseDTO
{
    public function __construct(
        public readonly array $sliderTitle,
        public readonly int $collectionId,
        public readonly ?UploadedFile $sliderLogo,
        public readonly ?array $slides,
        public readonly array $selectedBrandsId,
        public readonly int $selectedFieldId,
        public readonly array $selectedOptionsId,
    ){ }
}
