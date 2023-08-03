<?php

namespace App\Http\Requests\Admin\Brand;

use App\Http\Requests\BaseRequest;
use App\Services\Brand\DTO\EditBrandDTO;

class BrandCreateRequest extends BaseRequest
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
            ],
            'description' => [
                'array',
            ],
            'slider_main_text' => [
                'array',
            ],
            'slider_description_text' => [
                'array',
            ],
            'slide' => [
                'array',
                'required',
                'min:1',
            ],
            'slide.*.id' => [
                'nullable',
                'exists:collection_slides,id',
            ],
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $rules['description.' . $availableLanguage] = [
                'required',
                'string'
            ];

            $rules['name.' . $availableLanguage] = [
                'required',
                'string'
            ];

            $rules['slider_main_text.' . $availableLanguage] = [
                'required',
                'string',
            ];

            $rules['slider_description_text.' . $availableLanguage] = [
                'required',
                'string',
            ];
        }

        return $rules;
    }

    public function rules(): array
    {
        $rules = $this->baseRules();

        $rules['logo'] = [
            'required',
            'mimes:jpeg,png,jpg',
            'dimensions:min_width=300,min_height=300,max_width=600,max_height=600,ratio=1/1'
        ];

        $rules['head'] = [
            'required',
            'mimes:jpeg,png,jpg',
            'dimensions:min_width=1000,min_height=1000,max_width=2500,max_height=2500,ratio=1/1'
        ];

        $rules['slide.*.image'] = [
            'required',
            'image',
            'dimensions:min_width=1000,min_height=1000,max_width=2500,max_height=2500,ratio=1/1'
        ];

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [
            'name' => mb_strtolower(trans('admin.brand_name')),
            'logo' => mb_strtolower(trans('admin.brand_logo')),
            'head' => mb_strtolower(trans('admin.brand_head')),
            'slug' => mb_strtolower(trans('admin.slug')),
            'slide.*.image' => mb_strtolower(trans('admin.brand_slide_image')),
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $attributes['description.' . $availableLanguage] = $this->prepareAttribute(trans('admin.brand_description'), $availableLanguage);
            $attributes['name.' . $availableLanguage] = $this->prepareAttribute(trans('admin.brand_name'), $availableLanguage);
            $attributes['slider_main_text.' . $availableLanguage] = $this->prepareAttribute(trans('admin.brand_slider_main_text'), $availableLanguage);
            $attributes['slider_description_text.' . $availableLanguage] = $this->prepareAttribute(trans('admin.brand_slider_description_text'), $availableLanguage);
        }

        return $attributes;
    }

    public function messages(): array
    {   $messages = parent::messages();

        $messages['slide.*.image.required_if'] = trans('validation.required');

        return $messages;
    }

    public function toDTO(): EditBrandDTO
    {
        return new EditBrandDTO(
            $this->input('name'),
            $this->input('slug'),
            $this->input('description'),
            $this->file('logo'),
            $this->file('head'),
            $this->input('slider_main_text'),
            $this->input('slider_description_text'),
            $this->validated('slide'),
        );
    }
}
