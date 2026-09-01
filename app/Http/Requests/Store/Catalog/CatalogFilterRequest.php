<?php

namespace App\Http\Requests\Store\Catalog;

use App\Http\Requests\BaseRequest;
use App\Models\Brand;
use App\Services\Product\DTO\FilterProductDTO;

class CatalogFilterRequest extends BaseRequest
{
    protected function prepareForValidation(): void
    {
        $catalogFilters = $this->route('catalogFiltersString');
        $manufacturer = $this->route('brandSlug');

        if ($manufacturer instanceof Brand) {
            $catalogFilters = 'brand='.$manufacturer->slug;
        } elseif (is_string($manufacturer) && $manufacturer !== '') {
            $catalogFilters = 'brand='.$manufacturer;
        }

        $this->merge([
            'catalog_filters' => $catalogFilters,
        ]);
    }

    public function rules(): array
    {
        return [
            'catalog_filters' => ['nullable', 'string', 'max:2048'],
            'page' => ['nullable', 'integer', 'min:1', 'max:10000'],
        ];
    }

    public function toDTO(): FilterProductDTO
    {
        $filersArray = [];
        $filterString = (string) ($this->validated('catalog_filters') ?? '');
        $filterPairs = explode(';', $filterString);

        foreach ($filterPairs as $filterPair) {
            $pair = explode('=', $filterPair, 2);

            if ($pair[0] === '' || ! preg_match('/^[a-z0-9_-]{1,64}$/i', $pair[0])) {
                continue;
            }

            if (isset($pair[1])) {
                $values = array_slice(explode(',', $pair[1]), 0, 50);
                $filersArray[$pair[0]] = count($values) > 1 ? $values : $values[0];
            } else {
                $filersArray[$pair[0]] = null;
            }

        }

        if (($page = $this->validated('page')) !== null) {
            $filersArray['page'] = (int) $page;
        }

        return new FilterProductDTO($filersArray);
    }
}
