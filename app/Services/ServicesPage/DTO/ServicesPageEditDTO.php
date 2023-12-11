<?php

namespace App\Services\ServicesPage\DTO;

use App\Services\Base\DTO\BaseDTO;
use Illuminate\Http\UploadedFile;

class ServicesPageEditDTO implements BaseDTO
{
    public function __construct(

        public readonly ?array $sections,

    ){ }
}
