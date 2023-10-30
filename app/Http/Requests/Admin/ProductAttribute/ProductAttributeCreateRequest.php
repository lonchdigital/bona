<?php

namespace App\Http\Requests\Admin\ProductAttribute;

use Illuminate\Validation\Rule;
use App\Http\Requests\BaseRequest;
use App\DataClasses\ProductFieldTypeOptionsDataClass;
use App\DataClasses\NumericFieldFilerTypesDataClass;
use App\Services\Admin\ProductAttribute\DTO\EditProductAttributeDTO;

class ProductAttributeCreateRequest extends BaseRequest
{
    public function baseRules(): array
    {
        $rules = [
            'product_attribute_name' => [
                'required',
                'array',
                'min:1',
            ],
            'slug' => [
                'required',
                'string',
                'unique:product_attributes,slug' . ($this->route('productAttribute') ? ',' . $this->route('productAttribute')->id : ''),
            ],
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $rules['product_attribute_name.' . $availableLanguage] = [
                'required',
                'string',
            ];
        }


        return $rules;
    }

    public function rules(): array
    {
        $rules = $this->baseRules();

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [
            'product_field_option' => mb_strtolower(trans('admin.product_field_option')),
            'product_field_option.*.slug' => mb_strtolower(trans('admin.slug')),
        ];

        foreach ($this->availableLanguages as $language) {
            $attributes['product_attribute_name.' . $language] = $this->prepareAttribute(trans('admin.name'), $language);
        }

        return $attributes;
    }

    public function toDTO(): EditProductAttributeDTO
    {
        return new EditProductAttributeDTO(
            $this->input('product_attribute_name'),
            $this->input('slug'),
        );
    }

}
