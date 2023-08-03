<?php

namespace App\Http\Requests\Admin\Brand;

class BrandEditRequest extends BrandCreateRequest
{
    public function rules(): array
    {
        $rules = $this->baseRules();

        $rules['logo'] = [
            'nullable',
            'mimes:jpeg,png,jpg',
            'dimensions:min_width=300,min_height=300,max_width=600,max_height=600,ratio=1/1'
        ];

        $rules['head'] = [
            'nullable',
            'mimes:jpeg,png,jpg',
            'dimensions:min_width=1000,min_height=1000,max_width=2500,max_height=2500,ratio=1/1'
        ];

        $rules['slide.*.image'] = [
            'required_if:slide.*.id,null',
            'image',
            'dimensions:min_width=1000,min_height=1000,max_width=2500,max_height=2500,ratio=1/1'
        ];

        return $rules;
    }
}
