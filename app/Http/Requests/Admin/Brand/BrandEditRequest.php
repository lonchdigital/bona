<?php

namespace App\Http\Requests\Admin\Brand;

class BrandEditRequest extends BrandCreateRequest
{
    public function rules(): array
    {
        $rules = $this->baseRules();

        $rules['logo'] = [
            'nullable',
            'mimes:jpeg,png,jpg'
        ];

        return $rules;
    }
}
