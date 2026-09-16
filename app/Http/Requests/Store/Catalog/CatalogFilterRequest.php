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

        // Malformed ?page= or ?query= values come from crawlers and old links.
        // A failed validation would redirect them to the homepage (a redirect
        // error in Search Console), so they fall back to the first page.
        $page = $this->input('page');
        if ($page !== null && ! (is_scalar($page) && preg_match('/^[1-9]\d{0,4}$/', (string) $page) && (int) $page <= 10000)) {
            $this->query->remove('page');
            $this->request->remove('page');
        }

        $query = is_scalar($this->input('query')) ? trim((string) $this->input('query')) : '';
        $query = mb_strlen($query) >= 3 ? mb_substr($query, 0, 120) : null;

        $this->merge([
            'catalog_filters' => is_string($catalogFilters) ? mb_substr($catalogFilters, 0, 2048) : $catalogFilters,
            'query' => $query,
        ]);
    }

    public function rules(): array
    {
        return [
            'catalog_filters' => ['nullable', 'string', 'max:2048'],
            'page' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'query' => ['nullable', 'string', 'min:3', 'max:120'],
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

            if ($pair[0] === 'per_page') {
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

        if (($query = $this->validated('query')) !== null) {
            $filersArray['search'] = trim($query);
        }

        return new FilterProductDTO($filersArray);
    }
}
