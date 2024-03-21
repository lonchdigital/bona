<?php

namespace App\Http\Requests\Admin\StaticPage;

use App\Http\Requests\BaseRequest;
use App\Services\StaticPage\DTO\StaticPageEditDTO;

class StaticPageEditRequest extends BaseRequest
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
            'content' => [
                'array',
            ]
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
            $rules['content.' . $availableLanguage] = [
                'required',
                'string'
            ];
        }

        return $rules;
    }


    public function toDTO(): StaticPageEditDTO
    {
        return new StaticPageEditDTO(
            $this->input('meta_title'),
            $this->input('meta_description'),
            $this->input('meta_keywords'),
            $this->input('content')
        );
    }
}
