<?php

namespace App\Http\Requests\Admin\Brand;

use App\Http\Requests\BaseRequest;
use App\Services\Brand\DTO\EditBrandDTO;

class BrandCreateRequest extends BaseRequest
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
            'description' => [
                'array',
            ],
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

            $rules['description.' . $availableLanguage] = [
                'required',
                'string'
            ];

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

        $rules['logo'] = [
            'nullable',
            'mimes:jpeg,png,jpg'
        ];

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [
            'name' => mb_strtolower(trans('admin.brand_name')),
            'logo' => mb_strtolower(trans('admin.brand_logo')),
            'slug' => mb_strtolower(trans('admin.slug')),
            'meta_title' => mb_strtolower(trans('admin.meta_title')),
            'meta_description' => mb_strtolower(trans('admin.meta_description')),
            'meta_keywords' => mb_strtolower(trans('admin.meta_keywords')),
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $attributes['description.' . $availableLanguage] = $this->prepareAttribute(trans('admin.brand_description'), $availableLanguage);
            $attributes['name.' . $availableLanguage] = $this->prepareAttribute(trans('admin.brand_name'), $availableLanguage);
            $attributes['meta_title.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_title'), $availableLanguage);
            $attributes['meta_description.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_description'), $availableLanguage);
            $attributes['meta_keywords.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_keywords'), $availableLanguage);
        }

        return $attributes;
    }

    public function messages(): array
    {
        $messages = parent::messages();
//        $messages['slide.*.image.required_if'] = trans('validation.required');
        return $messages;
    }

    public function toDTO(): EditBrandDTO
    {
        return new EditBrandDTO(
            $this->input('meta_title'),
            $this->input('meta_description'),
            $this->input('meta_keywords'),
            $this->input('name'),
            $this->input('slug'),
            $this->input('description'),
            $this->file('logo'),
        );
    }
}
