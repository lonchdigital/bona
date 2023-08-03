<?php

namespace App\Http\Requests\Admin\Country;

use App\Http\Requests\BaseRequest;
use App\Services\Country\DTO\EditCountryDTO;

class CountryCreateRequest extends BaseRequest
{
    public function baseRules(): array
    {
        $baseRules = [
            'name' => [
                'array',
                'required',
                'min:1',
            ],
            'code' => [
                'required',
                'string',
            ],
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $baseRules['name.' . $availableLanguage] = [
                'required',
                'string',
            ];
        }

        return $baseRules;
    }

    public function rules(): array
    {
        $rules = $this->baseRules();

        $rules['image'] = [
            'required',
            'image',
            'mimes:svg',
            'dimensions:min_width=28,min_height=20,max_width=112,max_height=80,ratio=7/5'
        ];

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [
            'image' => mb_strtolower(trans('admin.country_image')),
            'code' => mb_strtolower(trans('admin.country_code')),
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $attributes['name.' . $availableLanguage] = $this->prepareAttribute(trans('admin.country_name'), $availableLanguage);
        }

        return $attributes;
    }

    public function toDTO(): EditCountryDTO
    {
        return new EditCountryDTO(
            $this->input('name'),
            $this->input('code'),
            $this->file('image'),
        );
    }
}
