<?php

namespace App\Http\Requests\Admin\Contacts;

use App\Http\Requests\BaseRequest;
use App\Services\Contacts\DTO\ContactsPageEditDTO;

class ContactsEditRequest extends BaseRequest
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
        ];

        $rules['iframe_address_one'] = [
            'nullable',
            'string'
        ];
        $rules['iframe_address_two'] = [
            'nullable',
            'string'
        ];
        $rules['iframe_address_three'] = [
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

            $rules['city_one.' . $availableLanguage] = [
                'nullable',
                'string'
            ];
            $rules['address_one.' . $availableLanguage] = [
                'nullable',
                'string'
            ];
            $rules['phone_one.' . $availableLanguage] = [
                'nullable',
                'string'
            ];
            $rules['email_one.' . $availableLanguage] = [
                'nullable',
                'string'
            ];

            $rules['city_two.' . $availableLanguage] = [
                'nullable',
                'string'
            ];
            $rules['address_two.' . $availableLanguage] = [
                'nullable',
                'string'
            ];
            $rules['phone_two.' . $availableLanguage] = [
                'nullable',
                'string'
            ];
            $rules['email_two.' . $availableLanguage] = [
                'nullable',
                'string'
            ];

            $rules['city_three.' . $availableLanguage] = [
                'nullable',
                'string'
            ];
            $rules['address_three.' . $availableLanguage] = [
                'nullable',
                'string'
            ];
            $rules['phone_three.' . $availableLanguage] = [
                'nullable',
                'string'
            ];
            $rules['email_three.' . $availableLanguage] = [
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

    public function toDTO(): ContactsPageEditDTO
    {
        return new ContactsPageEditDTO(
            $this->input('meta_title'),
            $this->input('meta_description'),
            $this->input('meta_keywords'),

            $this->input('city_one'),
            $this->input('address_one'),
            $this->input('phone_one'),
            $this->input('email_one'),
            $this->input('iframe_address_one'),

            $this->input('city_two'),
            $this->input('address_two'),
            $this->input('phone_two'),
            $this->input('email_two'),
            $this->input('iframe_address_two'),

            $this->input('city_three'),
            $this->input('address_three'),
            $this->input('phone_three'),
            $this->input('email_three'),
            $this->input('iframe_address_three'),
        );
    }
}
