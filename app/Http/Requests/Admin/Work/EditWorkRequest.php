<?php

namespace App\Http\Requests\Admin\Work;

class EditWorkRequest extends CreateWorkRequest
{

    public function rules(): array
    {
        $rules = $this->baseRules();

        $rules['slug'] = [
            'required',
            'unique:works,slug,' . $this->route('work')->id,
            'string',
        ];

        $rules['main_image' ] = [
            'nullable',
            'image',
            'mimes:jpeg,png,jpg',
        ];

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
}
