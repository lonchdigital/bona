<?php

namespace App\Http\Requests\Admin\Category;

use App\Http\Requests\BaseRequest;
use App\Services\ProductCategory\DTO\CreateCategoryDTO;

class CategoryCreateRequest extends BaseRequest
{
    public function baseRules(): array
    {
        $rules = [
            'name' => [
                'required',
                'array',
                'min:1',
            ],
            'slug' => [
                'required',
                'string',
            ]
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $rules['name.' . $availableLanguage] = [
                'required',
                'string'
            ];
        }

        return $rules;
    }

    public function rules(): array
    {
        $rules = $this->baseRules();

        $rules['category_image'] = [
            'required',
            'mimes:jpeg,png,jpg',
            'dimensions:min_width=150,min_height=150,ratio=1/1'
        ];

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [
            'category_image' => mb_strtolower(trans('admin.product_category_image')),
            'slug' => mb_strtolower(trans('admin.slug')),
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $attributes['name.' . $availableLanguage] = $this->prepareAttribute(trans('admin.name'), $availableLanguage);
        }

        return $attributes;
    }

    public function toDTO(): CreateCategoryDTO
    {
        return new CreateCategoryDTO(
            $this->input('name'),
            $this->input('slug'),
            $this->file('category_image'),
        );
    }
}
