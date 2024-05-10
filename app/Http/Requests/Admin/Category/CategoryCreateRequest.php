<?php

namespace App\Http\Requests\Admin\Category;

use App\Http\Requests\BaseRequest;
use App\Services\ProductCategory\DTO\CreateCategoryDTO;

class CategoryCreateRequest extends BaseRequest
{
    public function baseRules(): array
    {
        $rules = [
            'meta_title' => [
                'nullable',
                'array',
            ],
            'meta_description' => [
                'nullable',
                'array',
            ],
            'meta_keywords' => [
                'nullable',
                'array',
            ],
            'name' => [
                'required',
                'array',
                'min:1',
            ],
            'slug' => [
                'required',
                'string',
            ],
            'seo_text' => [
                'array',
            ]
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $rules['meta_title.' . $availableLanguage] = [
                'nullable',
                'string',
            ];
            $rules['meta_description.' . $availableLanguage] = [
                'nullable',
                'string',
            ];
            $rules['meta_keywords.' . $availableLanguage] = [
                'nullable',
                'string',
            ];
            $rules['name.' . $availableLanguage] = [
                'required',
                'string'
            ];
            $rules['seo_title.' . $availableLanguage] = [
                'nullable',
                'string',
            ];
            $rules['seo_text.' . $availableLanguage] = [
                'nullable',
                'string',
            ];
        }

        return $rules;
    }

    public function rules(): array
    {
        $rules = $this->baseRules();

        $rules['category_image'] = [
            'nullable',
            'mimes:jpeg,png,jpg',
            'dimensions:min_width=150,min_height=150'
        ];

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [
            'meta_title' => mb_strtolower(trans('admin.meta_title')),
            'meta_description' => mb_strtolower(trans('admin.meta_description')),
            'meta_keywords' => mb_strtolower(trans('admin.meta_keywords')),
            'category_image' => mb_strtolower(trans('admin.product_category_image')),
            'slug' => mb_strtolower(trans('admin.slug')),
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $attributes['meta_title.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_title'), $availableLanguage);
            $attributes['meta_description.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_description'), $availableLanguage);
            $attributes['meta_keywords.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_keywords'), $availableLanguage);
            $attributes['name.' . $availableLanguage] = $this->prepareAttribute(trans('admin.name'), $availableLanguage);
        }

        return $attributes;
    }

    public function toDTO(): CreateCategoryDTO
    {
        return new CreateCategoryDTO(
            $this->input('meta_title'),
            $this->input('meta_description'),
            $this->input('meta_keywords'),
            $this->input('name'),
            $this->input('slug'),
            $this->file('category_image'),

            $this->input('seo_title'),
            $this->input('seo_text'),
        );
    }
}
