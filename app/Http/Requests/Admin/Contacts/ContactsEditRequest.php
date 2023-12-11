<?php

namespace App\Http\Requests\Admin\Contacts;

use App\Http\Requests\BaseRequest;
use App\Services\Contacts\DTO\ContactsPageEditDTO;

class ContactsEditRequest extends BaseRequest
{
    public function rules(): array
    {
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

    public function toDTO(): ContactsPageEditDTO
    {
        return new ContactsPageEditDTO(
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
