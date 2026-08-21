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
        $rules['cta_button_url'] = ['nullable', 'string'];

        $rules['fact'] = ['nullable', 'array'];
        $rules['fact.*.id'] = ['nullable', 'integer'];
        $rules['fact.*.value'] = ['nullable', 'string', 'max:60'];

        $rules['step'] = ['nullable', 'array'];
        $rules['step.*.id'] = ['nullable', 'integer'];

        $rules['team'] = ['nullable', 'array'];
        $rules['team.*.id'] = ['nullable', 'integer'];
        $rules['team.*.photo'] = ['nullable', 'image', 'max:10240'];

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

            foreach ([
                'facts_title', 'history_title', 'steps_title', 'team_title',
                'cta_title', 'cta_button_text',
            ] as $sectionField) {
                $rules[$sectionField . '.' . $availableLanguage] = ['nullable', 'string', 'max:255'];
            }

            $rules['history_text.' . $availableLanguage] = ['nullable', 'string'];
            $rules['cta_text.' . $availableLanguage] = ['nullable', 'string', 'max:1000'];

            $rules['fact.*.label.' . $availableLanguage] = ['nullable', 'string', 'max:120'];
            $rules['step.*.title.' . $availableLanguage] = ['nullable', 'string', 'max:255'];
            $rules['step.*.text.' . $availableLanguage] = ['nullable', 'string', 'max:1000'];
            $rules['team.*.name.' . $availableLanguage] = ['nullable', 'string', 'max:120'];
            $rules['team.*.role.' . $availableLanguage] = ['nullable', 'string', 'max:120'];
            $rules['team.*.experience.' . $availableLanguage] = ['nullable', 'string', 'max:120'];
            $rules['team.*.quote.' . $availableLanguage] = ['nullable', 'string', 'max:500'];
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

            $this->input('facts_title'),
            $this->input('history_title'),
            $this->input('history_text'),
            $this->input('steps_title'),
            $this->input('team_title'),
            $this->input('cta_title'),
            $this->input('cta_text'),
            $this->input('cta_button_text'),
            $this->input('cta_button_url'),

            $this->input('fact'),
            $this->input('step'),
            $this->withUploads('team', 'photo'),
        );
    }

    /**
     * Merges the uploaded files back into the plain input, so the service is
     * handed one list per block instead of two parallel ones.
     */
    private function withUploads(string $key, string $fileField): ?array
    {
        $rows = $this->input($key);

        if (!is_array($rows)) {
            return null;
        }

        foreach ($rows as $index => $row) {
            $uploaded = $this->file($key . '.' . $index . '.' . $fileField);

            if ($uploaded) {
                $rows[$index][$fileField] = $uploaded;
            } else {
                unset($rows[$index][$fileField]);
            }
        }

        return $rows;
    }
}
