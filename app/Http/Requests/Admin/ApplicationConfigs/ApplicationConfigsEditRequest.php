<?php

namespace App\Http\Requests\Admin\ApplicationConfigs;

use App\Http\Requests\BaseRequest;
use App\Models\HomePageConfig;
use App\Rules\RequiredImageDeletedRule;
use App\Services\Application\DTO\ApplicationConfigEditDTO;

class ApplicationConfigsEditRequest extends BaseRequest
{
    public function rules(): array
    {
        $rules['logo_light'] = [
            'nullable',
            'image',
        ];
        $rules['logo_light_deleted'] = [
            'nullable',
        ];

        $rules['logo_dark'] = [
            'nullable',
            'image',
        ];
        $rules['logo_dark_deleted'] = [
            'nullable',
        ];

        $rules['instagram'] = [
            'nullable',
            'string'
        ];
        $rules['telegram'] = [
            'nullable',
            'string'
        ];
        $rules['viber'] = [
            'nullable',
            'string'
        ];
        $rules['facebook'] = [
            'nullable',
            'string'
        ];
        $rules['phone_one'] = [
            'nullable',
            'string'
        ];

        /*foreach ($this->availableLanguages as $availableLanguage) {
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
        }*/

        return  $rules;
    }

    public function attributes(): array
    {
        $attributes = [];
        // TODO: clean this up
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

    public function toDTO(): ApplicationConfigEditDTO
    {
        return new ApplicationConfigEditDTO(
            $this->file('logo_light'),
            (bool) $this->input('logo_light_deleted'),
            $this->file('logo_dark'),
            (bool) $this->input('logo_dark_deleted'),
            $this->input('instagram'),
            $this->input('telegram'),
            $this->input('viber'),
            $this->input('facebook'),
            $this->input('phone_one'),
        );
    }
}
