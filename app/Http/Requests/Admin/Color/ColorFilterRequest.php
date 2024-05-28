<?php

namespace App\Http\Requests\Admin\Color;

use App\Http\Requests\BaseRequest;
use App\Services\Color\DTO\FilterColorAdminDTO;

class ColorFilterRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'search' => [
                'nullable',
                'string',
            ],
        ];
    }

    public function toDTO(): FilterColorAdminDTO
    {
        return new FilterColorAdminDTO(
            $this->input('search'),
        );
    }
}
