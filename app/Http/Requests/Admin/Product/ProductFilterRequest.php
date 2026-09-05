<?php

namespace App\Http\Requests\Admin\Product;

use App\Http\Requests\BaseRequest;
use App\Services\Product\DTO\FilterProductAdminDTO;
use Illuminate\Validation\Rule;

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
            'category_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
            ],
            'style_option_id' => [
                'nullable',
                'integer',
                'exists:product_field_options,id',
            ],
            'per_page' => [
                'nullable',
                'integer',
                Rule::in([30, 50, 100, 200]),
            ],
            'sort' => [
                'nullable',
                'string',
                Rule::in(['position', 'name', 'created_at']),
            ],
            'direction' => [
                'nullable',
                'string',
                Rule::in(['asc', 'desc']),
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
            $this->input('category_id'),
            $this->input('style_option_id'),
            (int) $this->input('per_page', config('domain.admin_products_items_per_page', 30)),
            (string) $this->input('sort', 'position'),
            (string) $this->input('direction', 'asc'),
        );
    }
}
