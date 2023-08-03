<?php

namespace App\Http\Requests\Store\Product;

use App\Http\Requests\BaseRequest;
use App\Services\Product\DTO\SearchProductDTO;

class SearchProductRequest extends BaseRequest
{
    public function rules(): array {
        return [
            'query' => [
                'nullable',
                'string'
            ]
        ];
    }

    public function toDTO(): SearchProductDTO
    {
        return new SearchProductDTO(
            $this->input('query')
        );
    }
}
