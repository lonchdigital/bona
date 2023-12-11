<?php

namespace App\Http\Requests\Admin\Product;

use App\Rules\RequiredImageDeletedRule;

class ProductEditRequest extends ProductCreateRequest
{
    public function rules(): array
    {
        $rules = $this->baseRules();

        /*$rules['main_image_deleted_input'] = [
            'required',
            new RequiredImageDeletedRule(mb_strtolower(trans('admin.product_main_image'))),
        ];*/



        $rules['sku'] = [
            'nullable',
            'string',
            'unique:products,sku,' . $this->route('product')->id,
        ];

        $rules['slug'] = [
            'required',
            'unique:products,slug,' . $this->route('product')->id,
            'string',
        ];

        $rules['main_image' ] = [
            'nullable',
            'image',
            'mimes:jpeg,png,jpg',
        ];


        return $rules;
    }
}
