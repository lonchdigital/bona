<?php

namespace App\Http\Requests\Admin\ServicesPage;

use App\Http\Requests\BaseRequest;
use App\Services\ServicesPage\DTO\ServicesPageEditDTO;

class ServicesPageEditRequest extends BaseRequest
{
    public function rules(): array
    {
        $rules = [
            'sections.*.id' => [
                'nullable',
            ],
            'sections.*.slug' => [
                'required',
                'string',
                'max:180',
                'distinct',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            ],
            'sections.*.meta_tags' => [
                'nullable',
                'string',
            ],
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

        if ($this->input('sections')) {
            foreach ($this->input('sections') as $index => $section) {
                $rules['sections.'.$index.'.image'] = [
                    (isset($section['id']) && $section['id']) ? 'nullable' : 'required',
                    'image',
                ];
                $rules['sections.'.$index.'.button_url'] = [
                    'nullable',
                    'string',
                    $this->relativeOrHttpUrlRule(),
                ];
            }
        }

        foreach ($this->availableLanguages as $availableLanguage) {
            $rules['meta_title.'.$availableLanguage] = [
                'nullable',
                'string',
            ];
            $rules['meta_description.'.$availableLanguage] = [
                'nullable',
                'string',
            ];
            $rules['meta_keywords.'.$availableLanguage] = [
                'nullable',
                'string',
            ];
            $rules['sections.*.title.'.$availableLanguage] = [
                'nullable',
                'string',
            ];
            $rules['sections.*.description.'.$availableLanguage] = [
                'nullable',
                'string',
            ];
            $rules['sections.*.intro.'.$availableLanguage] = [
                'nullable',
                'string',
            ];
            $rules['sections.*.content.'.$availableLanguage] = [
                'nullable',
                'string',
            ];
            $rules['sections.*.button_text.'.$availableLanguage] = [
                'nullable',
                'string',
            ];
            $rules['sections.*.meta_title.'.$availableLanguage] = ['nullable', 'string'];
            $rules['sections.*.meta_description.'.$availableLanguage] = ['nullable', 'string'];
            $rules['sections.*.meta_keywords.'.$availableLanguage] = ['nullable', 'string'];

        }

        return $rules;
    }

    private function relativeOrHttpUrlRule(): \Closure
    {
        return static function (string $attribute, mixed $value, \Closure $fail): void {
            if ($value === null || $value === '') {
                return;
            }

            $url = trim((string) $value);

            if (str_starts_with($url, '#')) {
                return;
            }

            if (str_starts_with($url, '/')
                && ! str_starts_with($url, '//')
                && ! str_contains($url, '\\')) {
                return;
            }

            if (filter_var($url, FILTER_VALIDATE_URL) && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true)) {
                return;
            }

            $fail(trans('validation.url'));
        };
    }

    public function attributes(): array
    {
        $attributes = [
            'meta_title' => mb_strtolower(trans('admin.meta_title')),
            'meta_description' => mb_strtolower(trans('admin.meta_description')),
            'meta_keywords' => mb_strtolower(trans('admin.meta_keywords')),
            'sections.*.image' => mb_strtolower(trans('admin.slide_image')),
        ];

        if ($this->input('sections')) {
            foreach ($this->input('sections') as $index => $slide) {
                $attributes['sections.'.$index.'.image'] = mb_strtolower(trans('admin.slide_image'));

                $attributes['sections.'.$index.'.button_url'] = mb_strtolower(trans('admin.slide_text_link'));
            }
        }

        foreach ($this->availableLanguages as $availableLanguage) {
            $attributes['meta_title.'.$availableLanguage] = $this->prepareAttribute(trans('admin.meta_title'), $availableLanguage);
            $attributes['meta_description.'.$availableLanguage] = $this->prepareAttribute(trans('admin.meta_description'), $availableLanguage);
            $attributes['meta_keywords.'.$availableLanguage] = $this->prepareAttribute(trans('admin.meta_keywords'), $availableLanguage);
            $attributes['sections.*.title.'.$availableLanguage] = $this->prepareAttribute(trans('admin.section_title'), $availableLanguage);
            $attributes['sections.*.description.'.$availableLanguage] = $this->prepareAttribute(trans('admin.section_description'), $availableLanguage);
            $attributes['sections.*.button_text.'.$availableLanguage] = $this->prepareAttribute(trans('admin.section_text_button'), $availableLanguage);
        }

        return $attributes;
    }

    public function toDTO(): ServicesPageEditDTO
    {
        return new ServicesPageEditDTO(
            $this->input('meta_title'),
            $this->input('meta_description'),
            $this->input('meta_keywords'),
            $this->input('meta_tags'),
            $this->validated('sections'),
        );
    }
}
