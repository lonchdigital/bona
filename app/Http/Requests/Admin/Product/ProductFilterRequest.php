<?php

namespace App\Http\Requests\Admin\Product;

use App\Http\Requests\BaseRequest;
use App\Services\Product\DTO\FilterProductAdminDTO;

class ProductFilterRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'search' => [
                'nullable',
                'string',
            ],
            'brand_id' => [
                'nullable',
                'integer',
                'exists:brands,id',
            ],
            'color_id' => [
                'nullable',
                'integer',
                'exists:colors,id',
            ],
            'collection_id' => [
                'nullable',
                'integer',
                'exists:collections,id',
            ],
            'country_id' => [
                'nullable',
                'integer',
                'exists:countries,id',
            ],
        ];
    }

    public function toDTO(): FilterProductAdminDTO
    {
        return new FilterProductAdminDTO(
            $this->input('search'),
            $this->input('brand_id'),
            $this->input('color_id'),
            $this->input('collection_id'),
            $this->input('country_id'),
        );
    }
}
