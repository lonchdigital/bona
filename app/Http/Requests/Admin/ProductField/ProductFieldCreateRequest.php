<?php

namespace App\Http\Requests\Admin\ProductField;

use Illuminate\Validation\Rule;
use App\Http\Requests\BaseRequest;
use App\DataClasses\ProductFieldTypeOptionsDataClass;
use App\DataClasses\NumericFieldFilerTypesDataClass;
use App\Services\Admin\ProductField\DTO\EditProductFieldDTO;

class ProductFieldCreateRequest extends BaseRequest
{
    public function baseRules(): array
    {
        $rules = [
            'product_field_name' => [
                'required',
                'array',
                'min:1',
            ],
            'slug' => [
                'required',
                'string',
                'unique:product_fields,slug' . ($this->route('productField') ? ',' . $this->route('productField')->id : ''),
            ],
            'product_field_type' => [
                'required',
                'integer',
                Rule::in([
                    ProductFieldTypeOptionsDataClass::FIELD_TYPE_STRING,
                    ProductFieldTypeOptionsDataClass::FIELD_TYPE_NUMBER,
                    ProductFieldTypeOptionsDataClass::FIELD_TYPE_SIZE,
                    ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION
                ]),
            ]
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $rules['product_field_name.' . $availableLanguage] = [
                'required',
                'string',
            ];
        }

        //validations for field type SIZE
        if ($this->input('product_field_type') == ProductFieldTypeOptionsDataClass::FIELD_TYPE_SIZE) {

            foreach ($this->availableLanguages as $availableLanguage) {
                $rules['product_field_size_name.' . $availableLanguage] = [
                    'required',
                    'string',
                ];
            }
        }

        if ($this->input('product_field_type') == ProductFieldTypeOptionsDataClass::FIELD_TYPE_SIZE ||
            $this->input('product_field_type') == ProductFieldTypeOptionsDataClass::FIELD_TYPE_NUMBER
        ) {
            $rules['numeric_field_filter_type_id'] = [
                'required',
                'in:' . NumericFieldFilerTypesDataClass::get()->pluck('id')->implode(','),
            ];

            if ($this->input('numeric_field_filter_type_id') == NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_OPTIONS_TYPE) {
                $rules['numeric_filter_option.*.name'] = [
                    'required',
                    'array',
                ];

                foreach ($this->availableLanguages as $availableLanguage) {
                    $rules['numeric_filter_option.*.name.' . $availableLanguage] = [
                        'required',
                        'string',
                    ];
                }

                $rules['numeric_filter_option.*.slug'] = [
                    'required',
                    'string',
                ];

                $rules['numeric_filter_option.*.from'] = [
                    'required',
                    'numeric',
                ];

                $rules['numeric_filter_option.*.to'] = [
                    'required',
                    'numeric',
                ];
            }
        }


        //validations for field type OPTION
        if ($this->input('product_field_type') == ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION) {

            $rules['product_field_option'] = [
                'required',
                'array',
                'min:1'
            ];

            foreach ($this->availableLanguages as $availableLanguage) {
                $rules['product_field_option.*.name.' . $availableLanguage] = [
                    'required',
                    'string',
                ];
            }

            $rules['product_field_option.*.slug'] = [
                'required',
                'string',
            ];

            $rules['is_multiselectable'] = [
                'nullable',
            ];
        }

        return $rules;
    }

    public function rules(): array
    {
        $rules = $this->baseRules();

        if ($this->input('as_image')) {
            $rules['product_field_option.*.image'] = [
                'required',
                'mimes:jpeg,png,jpg',
                'dimensions:min_width=150,min_height=150,max_width=350,max_height=350,ratio=1/1'
            ];
        }

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [
            'product_field_type' => mb_strtolower(trans('admin.product_field_type')),
            'product_field_option' => mb_strtolower(trans('admin.product_field_option')),
            'product_field_option.*.image' => mb_strtolower(trans('admin.product_field_option_image')),
            'product_field_option.*.slug' => mb_strtolower(trans('admin.slug')),
            'numeric_filter_option.*.from' => mb_strtolower(trans('admin.numeric_filter_option_from')),
            'numeric_filter_option.*.to' => mb_strtolower(trans('admin.numeric_filter_option_to')),
            'numeric_filter_option.*.slug' => mb_strtolower(trans('admin.numeric_filter_option_to')),
        ];

        foreach ($this->input('product_field_option') as $fieldOption) {
            $attributes['product_field_option.' . $fieldOption['id'] . '.image'] = mb_strtolower(trans('admin.product_field_option_image'));
        }

        foreach ($this->availableLanguages as $language) {
            $attributes['product_field_name.' . $language] = $this->prepareAttribute(trans('admin.name'), $language);
            $attributes['product_field_size_name.' . $language] = $this->prepareAttribute(trans('admin.point_name'), $language);
            $attributes['product_field_option.*.name.' . $language] = $this->prepareAttribute(trans('admin.option_name'), $language);
            $attributes['numeric_filter_option.*.name.' . $language] = $this->prepareAttribute(trans('admin.numeric_filter_option_name'), $language);
        }

        return $attributes;
    }

    public function toDTO(): EditProductFieldDTO
    {
        return new EditProductFieldDTO(
            $this->input('product_field_name'),
            $this->input('slug'),
            $this->input('product_field_type'),
            $this->input('product_field_size_name'),
            $this->validated('product_field_option'),
            (bool) $this->input('is_multiselectable'),
            (bool) $this->input('as_image'),
            (bool) $this->input('display_on_single'),
            $this->input('numeric_field_filter_type_id'),
            $this->input('numeric_filter_option'),
        );
    }

}
