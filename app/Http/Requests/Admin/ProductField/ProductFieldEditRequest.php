<?php

namespace App\Http\Requests\Admin\ProductField;

use App\Models\ProductFieldOption;
use Illuminate\Validation\Rule;

class ProductFieldEditRequest extends ProductFieldCreateRequest
{
    public function rules(): array
    {
        $rules = $this->baseRules();

        if ($this->input('as_image')) {

            $optionIds = array_column($this->input('product_field_option'), 'id');
            $options = ProductFieldOption::whereIn('id', $optionIds)->get();

            foreach ($options as $option) {
                $rules['product_field_option.' .
                $option->id . '.image'] = [
                    'mimes:jpeg,png,jpg',
                    'dimensions:min_width=150,min_height=150,max_width=350,max_height=350,ratio=1/1'
                ];

                if ($option && $option->image_url) {
                    $rules['product_field_option.' . $option->id . '.image'][] = 'nullable';
                } else {
                    $rules['product_field_option.' . $option->id . '.image'][] = 'required';
                }
            }

        }

        return $rules;
    }
}
