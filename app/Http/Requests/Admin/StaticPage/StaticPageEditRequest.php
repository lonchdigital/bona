<?php

namespace App\Http\Requests\Admin\StaticPage;

use App\Http\Requests\BaseRequest;
use App\Services\StaticPage\DTO\SeoTextsEditDTO;

class StaticPageEditRequest extends BaseRequest
{
    public function rules(): array
    {
        $rules = [
            'content' => [
                'array',
            ]
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $rules['content.' . $availableLanguage] = [
                'required',
                'string'
            ];
        }

        return $rules;
    }


    public function toDTO(): SeoTextsEditDTO
    {
        return new SeoTextsEditDTO(
            $this->input('content')
        );
    }
}
