<?php

namespace App\Services\Collection\DTO;

use Illuminate\Http\UploadedFile;
use App\Services\Base\DTO\BaseDTO;

class EditCollectionDTO implements BaseDTO
{
    public function __construct(
        public readonly array $name,
        public readonly string $slug,
        public readonly int $brandId,
        public readonly array $slides,
        public readonly ?UploadedFile $preview1,
        public readonly ?UploadedFile $preview2,
        public readonly ?UploadedFile $preview3,
        public readonly ?UploadedFile $preview4,
    )
    { }
}
