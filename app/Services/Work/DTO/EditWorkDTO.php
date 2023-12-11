<?php

namespace App\Services\Work\DTO;

use App\Services\Base\DTO\BaseDTO;
use Illuminate\Http\UploadedFile;

class EditWorkDTO implements BaseDTO
{
    public function __construct(
        public readonly array         $name,
        public readonly string        $slug,
        public readonly ?array        $metaTitle,
        public readonly ?array        $metaDescription,
        public readonly ?array        $metaKeyWords,
        public readonly ?UploadedFile $mainImage,
    )
    { }
}
