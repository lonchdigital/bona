<?php

namespace App\Http\Requests\Admin\BlogCategory;

use App\Http\Requests\BaseRequest;
use App\Services\BlogCategory\DTO\EditBlogCategoryDTO;

class EditBlogCategoryRequest extends BaseRequest
{
    public function rules(): array
    {
        $rules = [
            'name' => [
                'array'
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

    public function attributes(): array
    {
        $attributes = [
            'slug' => mb_strtolower(trans('admin.slug')),
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $attributes['name.' . $availableLanguage] = $this->prepareAttribute(trans('admin.name'), $availableLanguage);
        }

        return $attributes;
    }

    public function toDTO(): EditBlogCategoryDTO
    {
        return new EditBlogCategoryDTO(
            $this->input('name'),
            $this->input('slug'),
        );
    }
}
