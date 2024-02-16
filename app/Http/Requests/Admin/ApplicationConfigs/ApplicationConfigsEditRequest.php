<?php

namespace App\Http\Requests\Admin\ApplicationConfigs;

use App\Http\Requests\BaseRequest;
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

        $rules['form_image'] = [
            'nullable',
            'image',
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $rules['footer_text.' . $availableLanguage] = [
                'nullable',
                'string'
            ];

            $rules['form_title.' . $availableLanguage] = [
                'nullable',
                'string'
            ];
            $rules['form_text.' . $availableLanguage] = [
                'nullable',
                'string'
            ];
        }

        return  $rules;
    }

    public function attributes(): array
    {
        $attributes = [];

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

            $this->input('footer_text'),

            $this->input('form_title'),
            $this->input('form_text'),
            $this->file('form_image'),
            (bool) $this->input('form_image_deleted'),
        );
    }
}
