<?php

namespace App\Http\Requests\Admin\Work;

use App\Http\Requests\BaseRequest;
use App\Services\Work\DTO\EditWorkDTO;

class CreateWorkRequest extends BaseRequest
{

    public function baseRules(): array
    {
        $rules = [
            'name' => [
                'required',
                'array',
                'min:1'
            ],
            'slug' => [
                'required',
                'unique:products,slug',
                'string',
            ],
            'meta_title' => [
                'nullable',
                'array',
                'min:1'
            ],
            'meta_description' => [
                'nullable',
                'array',
                'min:1'
            ],
            'meta_keywords' => [
                'nullable',
                'array',
                'min:1'
            ],
            'main_image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg',
            ],
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $rules['name.' . $availableLanguage] = [
                'required',
                'string',
            ];
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
        }

        return $rules;
    }

    public function rules(): array
    {
        $rules = $this->baseRules();

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [
            'product_field.*.id' => mb_strtolower(trans('admin.product_field')),
            'slug' => mb_strtolower(trans('admin.slug')),
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $attributes['name.' . $availableLanguage] = $this->prepareAttribute(trans('admin.name'), $availableLanguage);
        }

        return $attributes;
    }

    public function toDTO(): EditWorkDTO
    {
        return new EditWorkDTO(
            $this->input('name'),
            $this->input('slug'),
            $this->input('meta_title'),
            $this->input('meta_description'),
            $this->input('meta_keywords'),
            $this->file('main_image'),
        );
    }
}
