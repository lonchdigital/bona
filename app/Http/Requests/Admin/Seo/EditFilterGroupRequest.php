<?php

namespace App\Http\Requests\Admin\Seo;

use App\DataClasses\NumericFieldFilerTypesDataClass;
use App\Http\Requests\BaseRequest;
use App\Models\ProductType;
use App\Services\FilterGroups\DTO\FilterGroupEditDTO;

class EditFilterGroupRequest extends BaseRequest
{
    public function rules(): array
    {
        $rules = [
            'product_type_id' => [
                'required',
                'exists:product_types,id'
            ],
            'slug' => [
                'required',
                'string',
                $this->route('filterGroup') ? 'unique:filter_groups,slug,' . $this->route('filterGroup')->id : 'unique:filter_groups,slug',
            ],
            'price_from' => [
                'nullable',
                'numeric',
            ],
            'price_to' => [
                'nullable',
                'numeric',
            ],
            'country_ids' => [
                'nullable',
            ],
            'custom_field' => [
                'array',
            ],
            'custom_field.*.id' => [
                'required',
                'integer',
                'exists:product_fields,id',
            ],
            'custom_field.*.value' => [
                'nullable',
            ]
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $rules['name.' . $availableLanguage] = [
                'required',
                'string',
            ];

            $rules['title_tag.' . $availableLanguage] = [
                'required',
                'string',
            ];

            $rules['meta_title.' . $availableLanguage] = [
                'required',
                'string',
            ];

            $rules['meta_description.' . $availableLanguage] = [
                'required',
                'string',
            ];

            $rules['meta_keywords.' . $availableLanguage] = [
                'required',
                'string',
            ];
        }

        $selectedProductType = ProductType::find($this->input('product_type_id'));

        if ($selectedProductType) {
            if ($selectedProductType->has_color) {
                $rules['color_ids'] = [
                    'nullable',
                ];
            }

            if ($selectedProductType->has_brand) {
                $rules['brand_ids'] = [
                    'nullable',
                ];
            }

            if ($selectedProductType->has_size && $selectedProductType->has_length && $selectedProductType->filter_by_length) {
                if ($selectedProductType->product_size_length_filter_type_id === NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_FROM_TO_INPUTS_TYPE) {
                    $rules['length_from'] = [
                        'nullable',
                        'numeric'
                    ];
                    $rules['length_to'] = [
                        'nullable',
                        'numeric'
                    ];
                } elseif ($selectedProductType->product_size_length_filter_type_id === NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_OPTIONS_TYPE) {
                    $rules['length_options'] = [
                        'nullable',
                    ];
                }
            }

            if ($selectedProductType->has_size && $selectedProductType->has_width && $selectedProductType->filter_by_width) {
                if ($selectedProductType->product_size_width_filter_type_id === NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_FROM_TO_INPUTS_TYPE) {
                    $rules['width_from'] = [
                        'nullable',
                        'numeric'
                    ];
                    $rules['width_to'] = [
                        'nullable',
                        'numeric'
                    ];
                } elseif ($selectedProductType->product_size_width_filter_type_id === NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_OPTIONS_TYPE) {
                    $rules['width_options'] = [
                        'nullable',
                    ];
                }
            }

            if ($selectedProductType->has_size && $selectedProductType->has_height && $selectedProductType->filter_by_height) {
                if ($selectedProductType->product_size_height_filter_type_id === NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_FROM_TO_INPUTS_TYPE) {
                    $rules['height_from'] = [
                        'nullable',
                        'numeric'
                    ];
                    $rules['height_to'] = [
                        'nullable',
                        'numeric'
                    ];
                } elseif ($selectedProductType->product_size_height_filter_type_id === NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_OPTIONS_TYPE) {
                    $rules['height_options'] = [
                        'nullable',
                    ];
                }
            }
        }

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [
            'slug' => mb_strtolower(trans('admin.slug')),
            'product_type_id' => mb_strtolower(trans('admin.product_type')),
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $attributes['name.' . $availableLanguage] = $this->prepareAttribute(trans('admin.name'), $availableLanguage);
            $attributes['title_tag.' . $availableLanguage] = $this->prepareAttribute(trans('admin.title_tag'), $availableLanguage);
            $attributes['meta_title.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_title'), $availableLanguage);
            $attributes['meta_description.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_description'), $availableLanguage);
            $attributes['meta_keywords.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_keywords'), $availableLanguage);
        }

        return $attributes;
    }

    public function toDto(): FilterGroupEditDTO
    {
        $transformedCustomFields = [];

        foreach ($this->input('custom_field') as $customField) {
            $transformedCustomFields[] = [
                'id' => (int) $customField['id'],
                'value' => $customField['value'] ? explode(',', $customField['value']) : null,
            ];
        }

        return new FilterGroupEditDTO(
            $this->input('product_type_id'),
            $this->input('slug'),
            $this->input('price_from'),
            $this->input('price_to'),
            $this->input('country_ids') ? explode(',', $this->input('country_ids')) : null,
            $transformedCustomFields,
            $this->input('name'),
            $this->input('title_tag'),
            $this->input('meta_title'),
            $this->input('meta_description'),
            $this->input('meta_keywords'),
            $this->input('color_ids') ? explode(',', $this->input('color_ids')) : null,
            $this->input('brand_ids') ? explode(',', $this->input('brand_ids')) : null,
            $this->input('length_from'),
            $this->input('length_to'),
            $this->input('length_options') ? explode(',', $this->input('length_options')) : null,
            $this->input('width_from'),
            $this->input('width_to'),
            $this->input('width_options') ? explode(',', $this->input('width_options')) : null,
            $this->input('height_from'),
            $this->input('height_to'),
            $this->input('height_options') ? explode(',', $this->input('height_options')) : null,
        );
    }
}
