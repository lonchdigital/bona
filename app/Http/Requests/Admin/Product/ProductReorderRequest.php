<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductReorderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_ids' => ['required', 'array', 'min:2', 'max:200'],
            'product_ids.*' => ['required', 'integer', 'distinct', 'exists:products,id'],
        ];
    }
}
