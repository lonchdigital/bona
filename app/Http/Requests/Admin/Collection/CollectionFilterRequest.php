<?php

namespace App\Http\Requests\Admin\Collection;

use App\Http\Requests\BaseRequest;
use App\Services\Collection\DTO\FilterCollectionDTO;

class CollectionFilterRequest extends BaseRequest
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
                'array',
                'exists:brands,id',
            ],
        ];
    }

    public function toDTO(): FilterCollectionDTO
    {
        return new FilterCollectionDTO(
            $this->input('search'),
            $this->input('brand_id'),
        );
    }
}
