<?php

namespace App\Http\Requests\Store\Product;

use App\Http\Requests\BaseRequest;
use App\Services\Product\DTO\SearchProductDTO;

class SearchProductRequest extends BaseRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['query' => trim((string) $this->input('query'))]);
    }

    public function rules(): array
    {
        return [
            'query' => [
                'required',
                'string',
                'min:3',
                'max:120',
            ],
        ];
    }

    public function toDTO(): SearchProductDTO
    {
        return new SearchProductDTO(
            $this->input('query')
        );
    }
}
