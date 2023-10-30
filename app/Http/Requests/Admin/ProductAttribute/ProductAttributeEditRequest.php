<?php

namespace App\Http\Requests\Admin\ProductAttribute;

use App\Models\ProductFieldOption;
use Illuminate\Validation\Rule;

class ProductAttributeEditRequest extends ProductAttributeCreateRequest
{
    public function rules(): array
    {
        $rules = $this->baseRules();

        return $rules;
    }
}
