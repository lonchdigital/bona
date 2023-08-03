<?php

namespace App\Http\Requests\Admin\Category;

class CategoryEditRequest extends CategoryCreateRequest
{
    public function rules(): array
    {
        $rules = $this->baseRules();

        $rules['category_image'] = [
            'nullable',
            'mimes:jpeg,png,jpg',
            'dimensions:min_width=150,min_height=150,ratio=1/1'
        ];

        return $rules;
    }
}
