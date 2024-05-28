<?php

namespace App\Services\Color\DTO;

use App\Services\Base\DTO\BaseDTO;
use Illuminate\Http\UploadedFile;

class EditColorDTO implements BaseDTO
{
    public function __construct(
        public readonly array         $name,
        public readonly string        $slug,
        public readonly bool          $displayAsImage,
        public readonly ?string       $hex,
        public readonly ?UploadedFile $mainImage,
    )
    { }
}
