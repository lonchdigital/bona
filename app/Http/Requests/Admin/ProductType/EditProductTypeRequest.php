<?php

namespace App\Http\Requests\Admin\ProductType;

use App\Http\Requests\BaseRequest;
use App\DataClasses\NumericFieldFilerTypesDataClass;
use App\DataClasses\ProductFilterFullPositionOptionsDataClass;
use App\Services\Admin\ProductType\DTO\EditProductTypeDTO;

class EditProductTypeRequest extends BaseRequest
{
    protected array $sizeTypes = ['length', 'width', 'height'];

    public function rules(): array
    {
        $rules = [
            'name' => [
                'required',
                'array',
                'min:1',
            ],
            'slug' => [
                'required',
                'unique:product_types,slug' . (!$this->route('productType') ? '' : ',' . $this->route('productType')->id),
                'string',
            ],
            'product_point_name' => [
                'nullable',
                'array',
            ],
            'product_type_image' => [
                'nullable',
                'mimes:jpeg,png,jpg',
            ],
            'meta_title' => [
                'nullable',
                'array',
            ],
            'meta_description' => [
                'nullable',
                'array',
            ],
            'meta_keywords' => [
                'nullable',
                'array',
            ],
            'meta_tags' => [
                'nullable',
                'string',
            ],
            'meta_product_title' => [
                'nullable',
                'array',
            ],
            'meta_product_description' => [
                'nullable',
                'array',
            ],
            'product_has_brand' => [
                'nullable',
            ],
            'product_has_color' => [
                'nullable',
            ],
            'product_has_collection' => [
                'nullable',
            ],
            'product_has_size' => [
                'nullable',
            ],
            'size_points' => [
                $this->input('has_size') ? 'required' : 'nullable',
                'array',
                'min:1',
            ],
            'product_field' => [
                'array',
            ],
            'product_field.*.id' => [
                'required',
                'exists:product_fields,id',
                'distinct',
            ],
            'product_field.*.show_as_filter' => [
                'nullable',
            ],
            'product_field.*.show_on_main_filters_list' => [
                'nullable',
            ],
            'product_field.*.filter_full_position_id' => [
                'nullable',
                'in:' . ProductFilterFullPositionOptionsDataClass::get()->pluck('id')->implode(','),
            ],
            'product_field.*.filter_name' => [
                'array',
                'min:1',
            ],
            'product_attribute' => [
                'array',
            ],
            'additional_products' => [
                'nullable',
                'string',
            ],
        ];

        foreach ($this->sizeTypes as $sizeType) {
            $rules['product_has_' . $sizeType] = [
                'nullable',
            ];
            $rules['product_filter_by_' . $sizeType] = [
                'nullable',
            ];
            $rules['product_size_' . $sizeType . '_filter_name'] = [
                $this->input('product_filter_by_' . $sizeType) ? 'required' : 'nullable',
                'array',
            ];
            $rules['product_size_' . $sizeType . '_show_on_main_filter'] = [
                'nullable',
            ];
            $rules['product_size_' . $sizeType . '_filter_full_position_id'] = [
                'nullable',
                'in:' . ProductFilterFullPositionOptionsDataClass::get()->pluck('id')->implode(','),
            ];
            $rules['product_size_' . $sizeType .'_filter_type_id'] = [
                $this->input('product_filter_by_' . $sizeType) ? 'required' : 'nullable',
                'in:'.NumericFieldFilerTypesDataClass::get()->pluck('id')->implode(','),
            ];
            $rules['product_size_' . $sizeType . '_option'] = [
                'array',
                $this->input('product_size_' . $sizeType . '_filter_type_id') ==
                NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_OPTIONS_TYPE ? 'required' : 'nullable',
            ];

            if ($this->input('product_size_' . $sizeType .'_filter_type_id') ==
                NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_OPTIONS_TYPE) {

                $rules['product_size_' . $sizeType . '_option.*.id'] = [
                    'required',
                    'numeric',
                ];
                $rules['product_size_' . $sizeType . '_option.*.name'] = [
                    'required',
                    'array',
                ];
                $rules['product_size_' . $sizeType . '_option.*.slug'] = [
                    'required',
                    'string',
                ];
                $rules['product_size_' . $sizeType . '_option.*.from'] = [
                    'required',
                    'numeric',
                ];
                $rules['product_size_' . $sizeType . '_option.*.to'] = [
                    'required',
                    'numeric',
                ];

                foreach ($this->availableLanguages as $availableLanguage) {
                    $rules['product_size_' . $sizeType . '_option.*.name.' . $availableLanguage] = [
                        'required',
                        'string',
                    ];
                }
            }

            foreach ($this->availableLanguages as $availableLanguage) {
                $rules['product_size_' . $sizeType . '_filter_name.' . $availableLanguage] = [
                    $this->input('product_filter_by_' . $sizeType) ? 'required' : 'nullable',
                    'string',
                ];
            }

        }

        foreach ($this->availableLanguages as $availableLanguage) {
            $rules['name.' . $availableLanguage] = [
                'required',
                'string',
            ];
            $rules['meta_title.' . $availableLanguage] = [
                'nullable',
                'string',
            ];
            $rules['meta_description.' . $availableLanguage] = [
                'nullable',
                'string',
            ];
            $rules['meta_keywords.' . $availableLanguage] = [
                'nullable',
                'string',
            ];
            $rules['meta_product_title.' . $availableLanguage] = [
                'nullable',
                'string',
            ];
            $rules['meta_product_description.' . $availableLanguage] = [
                'nullable',
                'string',
            ];
            $rules['size_points.' . $availableLanguage] = [
                $this->input('has_size') ? 'required' : 'nullable',
                'string',
            ];

            $rules['faqs.*.question.' . $availableLanguage] = [
                'required',
                'string'
            ];
            $rules['faqs.*.answer.' . $availableLanguage] = [
                'required',
                'string'
            ];

            $rules['seo_title.' . $availableLanguage] = [
                'nullable',
                'string',
            ];
            $rules['seo_text.' . $availableLanguage] = [
                'nullable',
                'string',
            ];
        }


        if ($this->input('product_field')) {
            foreach ($this->input('product_field') as $index => $productField) {
                if ($productField['show_as_filter']) {
                    foreach ($this->availableLanguages as $availableLanguage) {
                        $rules['product_field.' . $index . '.filter_name.' . $availableLanguage] = [
                            'required',
                            'string',
                        ];
                    }
                }
            }
        }

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [
            'product_field.*.id' => mb_strtolower(trans('admin.product_field')),
            'slug' => mb_strtolower(trans('admin.slug')),
        ];

        foreach ($this->sizeTypes as $sizeType) {
            foreach ($this->availableLanguages as $availableLanguage) {
                $attributes['product_size_' . $sizeType . '_option.*.name.' . $availableLanguage] =
                    $this->prepareAttribute(trans('admin.numeric_filter_option_name'), $availableLanguage);
                $attributes['product_size_' . $sizeType . '_filter_name.' . $availableLanguage] =
                    $this->prepareAttribute(trans('admin.filter_name'), $availableLanguage);
            }
            $attributes['product_size_' . $sizeType . '_option.*.slug'] = mb_strtolower(trans('admin.slug'));
            $attributes['product_size_' . $sizeType . '_option.*.from'] = mb_strtolower(trans('admin.numeric_filter_option_from'));
            $attributes['product_size_' . $sizeType . '_option.*.to'] = mb_strtolower(trans('admin.numeric_filter_option_to'));
        }

        foreach ($this->availableLanguages as $availableLanguage) {
            $attributes['name.' . $availableLanguage] = $this->prepareAttribute(trans('admin.name'), $availableLanguage);
            $attributes['meta_title.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_title'), $availableLanguage);
            $attributes['meta_description.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_description'), $availableLanguage);
            $attributes['meta_keywords.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_keywords'), $availableLanguage);
            $attributes['meta_product_title.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_product_title'), $availableLanguage);
            $attributes['meta_product_description.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_product_description'), $availableLanguage);
            $attributes['size_points.' . $availableLanguage] = $this->prepareAttribute(trans('admin.size_points'), $availableLanguage);
        }

        if ($this->input('product_field')) {
            foreach ($this->input('product_field') as $index => $productField) {
                if ($productField['show_as_filter']) {
                    foreach ($this->availableLanguages as $availableLanguage) {
                        $attributes['product_field.' . $index . '.filter_name.' . $availableLanguage] =
                            $this->prepareAttribute(trans('admin.filter_name'), $availableLanguage);
                    }
                }
            }
        }

        return $attributes;
    }

    public function toDTO(): EditProductTypeDTO
    {
        return new EditProductTypeDTO(
            $this->input('name'),
            $this->input('slug'),
            $this->input('product_point_name'),
            $this->file('product_type_image'),

            $this->input('meta_title'),
            $this->input('meta_description'),
            $this->input('meta_keywords'),
            $this->input('meta_tags'),

            $this->input('meta_product_title'),
            $this->input('meta_product_description'),

            $this->input('product_has_brand'),
            $this->input('product_has_color'),
            $this->input('product_has_collection'),
            $this->input('product_has_category'),
            $this->input('product_has_size'),

            $this->input('product_has_length'),
            $this->input('product_filter_by_length'),
            $this->input('product_size_length_show_on_main_filter'),
            $this->input('product_size_length_filter_full_position_id'),
            $this->input('product_size_length_filter_type_id'),
            $this->input('product_size_length_option'),
            $this->input('product_size_length_filter_name'),

            $this->input('product_has_width'),
            $this->input('product_filter_by_width'),
            $this->input('product_size_width_show_on_main_filter'),
            $this->input('product_size_width_filter_full_position_id'),
            $this->input('product_size_width_filter_type_id'),
            $this->input('product_size_width_option'),
            $this->input('product_size_width_filter_name'),

            $this->input('product_has_height'),
            $this->input('product_filter_by_height'),
            $this->input('product_size_height_show_on_main_filter'),
            $this->input('product_size_height_filter_full_position_id'),
            $this->input('product_size_height_filter_type_id'),
            $this->input('product_size_height_option'),
            $this->input('product_size_height_filter_name'),

            $this->input('size_points'),
            $this->input('product_field'),
            $this->input('product_attribute'),

            $this->validated('faqs'),
            $this->validated('additional_products'),

            $this->input('seo_title'),
            $this->input('seo_text'),
        );
    }
}
