<?php

namespace App\Http\Requests\Admin\Seo;

use App\Http\Requests\BaseRequest;
use App\Services\Seogen\DTO\EditSeogenDTO;

class EditSeogenRequest extends BaseRequest
{
    public function rules(): array
    {
        $rules = [
            'product_category.*.product_type_id' => [
                'required',
                'integer',
                'exists:product_types,id',
            ],
            'product.*.product_type_id' => [
                'required',
                'integer',
                'exists:product_types,id',
            ],
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $rules['product_category.*.title_tag.' . $availableLanguage] = [
                'required',
                'string',
            ];

            $rules['product_category.*.h1_tag.' . $availableLanguage] = [
                'required',
                'string',
            ];

            $rules['product_category.*.meta_title_tag.' . $availableLanguage] = [
                'required',
                'string',
            ];

            $rules['product_category.*.meta_description_tag.' . $availableLanguage] = [
                'required',
                'string',
            ];

            $rules['product_category.*.meta_keywords_tag.' . $availableLanguage] = [
                'required',
                'string',
            ];

            $rules['product.*.title_tag.' . $availableLanguage] = [
                'required',
                'string',
            ];

            $rules['product.*.h1_tag.' . $availableLanguage] = [
                'required',
                'string',
            ];

            $rules['product.*.meta_title_tag.' . $availableLanguage] = [
                'required',
                'string',
            ];

            $rules['product.*.meta_description_tag.' . $availableLanguage] = [
                'required',
                'string',
            ];

            $rules['product.*.meta_keywords_tag.' . $availableLanguage] = [
                'required',
                'string',
            ];

            $rules['brand_title_tag.' . $availableLanguage] = [
                'required',
                'string',
            ];

            $rules['brand_h1_tag.' . $availableLanguage] = [
                'required',
                'string',
            ];

            $rules['brand_meta_title_tag.' . $availableLanguage] = [
                'required',
                'string',
            ];

            $rules['brand_meta_description_tag.' . $availableLanguage] = [
                'required',
                'string',
            ];

            $rules['brand_meta_keywords_tag.' . $availableLanguage] = [
                'required',
                'string',
            ];
        }

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [];

        foreach ($this->availableLanguages as $availableLanguage) {
            $attributes['product_category.*.title_tag.' . $availableLanguage] = $this->prepareAttribute(trans('admin.title_tag'), $availableLanguage);
            $attributes['product_category.*.h1_tag.' . $availableLanguage] = $this->prepareAttribute(trans('admin.h1_tag'), $availableLanguage);
            $attributes['product_category.*.meta_title_tag.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_title_tag'), $availableLanguage);
            $attributes['product_category.*.meta_description_tag.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_description_tag'), $availableLanguage);
            $attributes['product_category.*.meta_keywords_tag.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_keywords_tag'), $availableLanguage);

            $attributes['product.*.title_tag.' . $availableLanguage] = $this->prepareAttribute(trans('admin.title_tag'), $availableLanguage);
            $attributes['product.*.h1_tag.' . $availableLanguage] = $this->prepareAttribute(trans('admin.h1_tag'), $availableLanguage);
            $attributes['product.*.meta_title_tag.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_title_tag'), $availableLanguage);
            $attributes['product.*.meta_description_tag.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_description_tag'), $availableLanguage);
            $attributes['product.*.meta_keywords_tag.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_keywords_tag'), $availableLanguage);

            $attributes['brand_title_tag.' . $availableLanguage] = $this->prepareAttribute(trans('admin.title_tag'), $availableLanguage);
            $attributes['brand_h1_tag.' . $availableLanguage] = $this->prepareAttribute(trans('admin.h1_tag'), $availableLanguage);
            $attributes['brand_meta_title_tag.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_title_tag'), $availableLanguage);
            $attributes['brand_meta_description_tag.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_description_tag'), $availableLanguage);
            $attributes['brand_meta_keywords_tag.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_keywords_tag'), $availableLanguage);
        }

        return $attributes;
    }

    public function toDTO(): EditSeogenDTO {
        return new EditSeogenDTO(
            $this->input('product_category'),
            $this->input('product'),
            $this->input('brand_title_tag'),
            $this->input('brand_h1_tag'),
            $this->input('brand_meta_title_tag'),
            $this->input('brand_meta_description_tag'),
            $this->input('brand_meta_keywords_tag'),
        );
    }
}
