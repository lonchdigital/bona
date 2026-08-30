<?php

namespace App\Http\Requests\Admin\ProductAttribute;

class ProductAttributeEditRequest extends ProductAttributeCreateRequest
{
    public function rules(): array
    {
        $rules = $this->baseRules();

        return $rules;
    }
}
