<?php

namespace App\Http\Requests\Admin\AboutUsPage;

use App\Http\Requests\BaseRequest;
use App\Models\HomePageConfig;
use App\Rules\RequiredImageDeletedRule;
use App\Services\AboutUsPage\DTO\AboutUsPageEditDTO;

class AboutUsPageEditRequest extends BaseRequest
{
    public function rules(): array
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
            'meta_tags' => [
                'nullable',
                'string',
            ],
        ];

        $rules['image'] = [
            'nullable',
            'image',
        ];
        $rules['image_deleted'] = [
            'nullable',
        ];
        $rules['button_url'] = [
            'nullable',
            'string'
        ];
        $rules['iframe'] = [
            'nullable',
            'string'
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

            $rules['title.' . $availableLanguage] = [
                'nullable',
                'string'
            ];
            $rules['description.' . $availableLanguage] = [
                'nullable',
                'string'
            ];
            $rules['button_text.' . $availableLanguage] = [
                'nullable',
                'string'
            ];
        }

        return  $rules;
    }

    public function attributes(): array
    {
        $attributes = [
            'meta_title' => mb_strtolower(trans('admin.meta_title')),
            'meta_description' => mb_strtolower(trans('admin.meta_description')),
            'meta_keywords' => mb_strtolower(trans('admin.meta_keywords')),
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $attributes['meta_title.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_title'), $availableLanguage);
            $attributes['meta_description.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_description'), $availableLanguage);
            $attributes['meta_keywords.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_keywords'), $availableLanguage);
        }

        return $attributes;
    }

    public function toDTO(): AboutUsPageEditDTO
    {
        return new AboutUsPageEditDTO(
            $this->input('meta_title'),
            $this->input('meta_description'),
            $this->input('meta_keywords'),
            $this->input('meta_tags'),
            $this->input('title'),
            $this->input('description'),
            $this->input('button_text'),
            $this->input('button_url'),
            $this->file('image'),
            (bool) $this->input('image_deleted'),
            $this->input('iframe'),
        );
    }
}
