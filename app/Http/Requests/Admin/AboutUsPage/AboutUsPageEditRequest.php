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
        $attributes = [];
        /*$attributes = [
            'sections.*.image' => mb_strtolower(trans('admin.slide_image')),
        ];

        if ($this->input('sections')) {
            foreach ($this->input('sections') as $index => $slide) {
                $attributes['sections.' . $index . '.image'] = mb_strtolower(trans('admin.slide_image'));

                $attributes['sections.' . $index . '.button_url'] = mb_strtolower(trans('admin.slide_text_link'));
            }
        }

        foreach ($this->availableLanguages as $availableLanguage) {
            $attributes['sections.*.title.' . $availableLanguage] = $this->prepareAttribute(trans('admin.section_title'), $availableLanguage);
            $attributes['sections.*.description.' . $availableLanguage] = $this->prepareAttribute(trans('admin.section_description'), $availableLanguage);
            $attributes['sections.*.button_text.' . $availableLanguage] = $this->prepareAttribute(trans('admin.section_text_button'), $availableLanguage);
        }*/

        return $attributes;
    }

    public function toDTO(): AboutUsPageEditDTO
    {
        return new AboutUsPageEditDTO(
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
