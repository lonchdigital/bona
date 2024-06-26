<?php

namespace App\Http\Requests\Admin\Color;

use App\Http\Requests\BaseRequest;
use App\Services\Color\DTO\EditColorDTO;

class EditColorRequest extends BaseRequest
{
    public function rules(): array
    {
        $rules = [
            'name' => [
                'required',
                'array',
            ],
            'slug' => [
                'required',
                'string',
            ],
            'display_as_image' => [
                'nullable',
            ],
            'hex' => [
                'nullable',
                'string',
            ],
            'main_image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg',
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

    public function attributes(): array
    {
        $attributes = [
            'hex' => trans('admin.color'),
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $attributes['name.' . $availableLanguage] = $this->prepareAttribute(trans('admin.color_name'), $availableLanguage);
        }

        return $attributes;
    }

    public function toDTO(): EditColorDTO
    {
        return new EditColorDTO(
            $this->input('name'),
            $this->input('slug'),
            $this->input('display_as_image'),
            $this->input('hex'),
            $this->file('main_image'),
        );
    }
}
