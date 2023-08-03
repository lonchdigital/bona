<?php

namespace App\Http\Requests\Admin\Collection;

use App\Http\Requests\BaseRequest;
use App\Services\Collection\DTO\SearchCollectionDTO;

class SearchCollectionRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'query' => [
                'nullable',
                'string',
            ],
        ];
    }

    public function toDTO(): SearchCollectionDTO
    {
        return new SearchCollectionDTO(
            $this->input('query')
        );
    }
}
