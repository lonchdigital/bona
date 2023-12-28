<?php

namespace App\Services\Application\DTO;

use App\Services\Base\DTO\BaseDTO;
use Illuminate\Http\UploadedFile;

class ApplicationConfigEditDTO implements BaseDTO
{
    public function __construct(
        public readonly ?UploadedFile $logoLight,
        public readonly bool $logoLightDeleted,
        public readonly ?UploadedFile $logoDark,
        public readonly bool $logoDarkDeleted,
        public readonly ?string $instagram,
        public readonly ?string $telegram,
        public readonly ?string $viber,
        public readonly ?string $phoneOne,
    ){ }
}