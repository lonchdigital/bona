<?php

namespace App\Http\Requests\Store\Brand;

use App\Http\Requests\BaseRequest;
use App\Services\Brand\DTO\SearchBrandDTO;

class BrandSearchRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'search' => [
                'nullable',
                'string',
            ]
        ];
    }

    public function toDTO(): SearchBrandDTO
    {
        return new SearchBrandDTO(
            $this->input('search')
        );
    }
}
