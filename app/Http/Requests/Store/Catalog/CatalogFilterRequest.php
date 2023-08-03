<?php

namespace App\Http\Requests\Store\Catalog;

use App\Http\Requests\BaseRequest;
use App\Services\Product\DTO\FilterProductDTO;

class CatalogFilterRequest extends BaseRequest
{
    public function rules(): array
    {
        return [];
    }
    public function toDTO(): FilterProductDTO
    {
        $filersArray = [];
        $filterString = $this->route('catalogFiltersString');
        $filterPairs = explode(';', $filterString);

        foreach ($filterPairs as $filterPair) {
            $pair = explode('=', $filterPair);

            if ($pair[0] === '') {
                continue;
            }

            if (isset($pair[1])) {
                $filersArray[$pair[0]] = str_contains($pair[1], ',') ? explode(',', $pair[1]) : $pair[1];
            } else {
                $filersArray[$pair[0]] = null;
            }

        }

        return new FilterProductDTO($filersArray);
    }
}
