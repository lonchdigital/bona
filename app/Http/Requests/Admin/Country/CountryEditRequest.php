<?php

namespace App\Http\Requests\Admin\Country;

class CountryEditRequest extends CountryCreateRequest
{
    public function rules(): array
    {
        $rules = $this->baseRules();

        $rules['image'] = [
            'nullable',
            'image',
            'mimes:svg',
            'dimensions:min_width=28,min_height=20,max_width=112,max_height=80,ratio=7/5'
        ];

        return $rules;
    }
}
