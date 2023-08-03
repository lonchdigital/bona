<?php

namespace App\Http\Requests\Admin\Collection;

class CollectionEditRequest extends CollectionCreateRequest
{
    public function rules(): array
    {
        $rules = $this->baseRules();

        $rules['slide.*.image_1'] = [
            'required_if:slide.*.id,null',
            'image',
            'dimensions:min_width=2000,min_height=2000,max_width=5000,max_height=5000,ratio=1/1'
        ];

        $rules['slide.*.image_2'] = [
            'required_if:slide.*.id,null',
            'image',
            'dimensions:min_width=2000,min_height=2000,max_width=5000,max_height=5000,ratio=1/1'
        ];

        $rules['collection_preview_image_1'] = [
            'nullable',
            'image',
            'dimensions:min_width=2000,min_height=2000,max_width=5000,max_height=5000,ratio=1/1'
        ];

        $rules['collection_preview_image_2'] = [
            'nullable',
            'image',
            'dimensions:min_width=2000,min_height=2000,max_width=5000,max_height=5000,ratio=1/1'
        ];

        $rules['collection_preview_image_3'] = [
            'nullable',
            'image',
            'dimensions:min_width=2000,min_height=2000,max_width=5000,max_height=5000,ratio=1/1'
        ];

        $rules['collection_preview_image_4'] = [
            'nullable',
            'image',
            'dimensions:min_width=2000,min_height=2000,max_width=5000,max_height=5000,ratio=1/1'
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'slide.*.image_1.required_if' => trans('validation.required'),
            'slide.*.image_2.required_if' => trans('validation.required'),
        ];
    }
}
