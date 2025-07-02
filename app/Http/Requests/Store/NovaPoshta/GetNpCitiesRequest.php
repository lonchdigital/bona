<?php

namespace App\Http\Requests\Store\NovaPoshta;

use App\Http\Requests\BaseRequest;
use App\Services\Delivery\DTO\GetNpCitiesDTO;
use App\Services\Product\DTO\SearchProductDTO;

class GetNpCitiesRequest extends BaseRequest
{
    public function rules(): array {
        return [
            'query' => [
                'nullable',
                'string'
            ],
            'locale' => [
                'nullable',
                'string'
            ]
        ];
    }

    public function toDTO(): GetNpCitiesDTO
    {
        return new GetNpCitiesDTO(
            $this->input('query'),
            $this->input('locale')
        );
    }
}
