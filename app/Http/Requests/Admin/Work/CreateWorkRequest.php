<?php

namespace App\Http\Requests\Admin\Work;

use App\Http\Requests\BaseRequest;
use App\Services\Work\DTO\EditWorkDTO;

class CreateWorkRequest extends BaseRequest
{

    public function baseRules(): array
    {
        $rules = [
            'name' => [
                'required',
                'array',
                'min:1'
            ],
            'slug' => [
                'required',
                // Checked against works; it used to be checked against
                // products, which let two projects share a slug.
                'unique:works,slug',
                'string',
            ],
            'meta_title' => [
                'nullable',
                'array',
                'min:1'
            ],
            'meta_description' => [
                'nullable',
                'array',
                'min:1'
            ],
            'meta_keywords' => [
                'nullable',
                'array',
                'min:1'
            ],
            'main_image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg',
            ],

            'intro' => ['nullable', 'array'],
            'description' => ['nullable', 'array'],
            'client_quote' => ['nullable', 'array'],
            'location' => ['nullable', 'string', 'max:191'],
            'doors_count' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'duration' => ['nullable', 'string', 'max:191'],
            'client_name' => ['nullable', 'string', 'max:191'],
            'is_published' => ['nullable'],

            'work_image' => ['nullable', 'array'],
            'work_image.*.id' => ['nullable', 'integer'],
            'work_image.*.image' => ['nullable', 'image', 'max:10240'],
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $rules['name.' . $availableLanguage] = [
                'required',
                'string',
            ];
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
            $rules['intro.' . $availableLanguage] = ['nullable', 'string', 'max:1000'];
            $rules['description.' . $availableLanguage] = ['nullable', 'string'];
            $rules['client_quote.' . $availableLanguage] = ['nullable', 'string', 'max:1000'];
            $rules['work_image.*.caption.' . $availableLanguage] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    public function rules(): array
    {
        $rules = $this->baseRules();

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [
            'product_field.*.id' => mb_strtolower(trans('admin.product_field')),
            'slug' => mb_strtolower(trans('admin.slug')),
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $attributes['name.' . $availableLanguage] = $this->prepareAttribute(trans('admin.name'), $availableLanguage);
        }

        return $attributes;
    }

    public function toDTO(): EditWorkDTO
    {
        return new EditWorkDTO(
            $this->input('name'),
            $this->input('slug'),
            $this->input('meta_title'),
            $this->input('meta_description'),
            $this->input('meta_keywords'),
            $this->file('main_image'),
            $this->input('intro'),
            $this->input('description'),
            $this->input('location'),
            $this->input('doors_count') !== null ? (int) $this->input('doors_count') : null,
            $this->input('duration'),
            $this->input('client_quote'),
            $this->input('client_name'),
            (bool) $this->input('is_published', true),
            $this->buildImages(),
        );
    }

    /**
     * Merges the uploaded files back into the plain input, so the service is
     * handed one list instead of two parallel ones.
     */
    protected function buildImages(): ?array
    {
        $images = $this->input('work_image');

        if (!is_array($images)) {
            return null;
        }

        foreach ($images as $index => $image) {
            $uploaded = $this->file('work_image.' . $index . '.image');

            if ($uploaded) {
                $images[$index]['image'] = $uploaded;
            } else {
                unset($images[$index]['image']);
            }
        }

        return $images;
    }
}
