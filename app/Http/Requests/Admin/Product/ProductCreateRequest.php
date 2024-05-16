<?php

namespace App\Http\Requests\Admin\Product;

use App\DataClasses\ProductFieldTypeOptionsDataClass;
use App\DataClasses\ProductSpecialOfferOptionsDataClass;
use App\DataClasses\ProductStatusDataClass;
use App\Http\Requests\BaseRequest;
use App\Models\ProductType;
use App\Services\Product\DTO\EditProductDTO;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Validation\Rule;

class ProductCreateRequest extends BaseRequest
{
    protected ProductType $productType;

    public function createDefaultValidator(ValidationFactory $factory): \Illuminate\Contracts\Validation\Validator
    {
        $this->productType = $this->route('productType');
        return parent::createDefaultValidator($factory);
    }

    public function baseRules(): array
    {
        $rules = [
            'is_active' => [
                'nullable',
            ],
            'slug' => [
                'required',
                'unique:products,slug',
                'string',
            ],
            /*'selected_sub_products_id' => [
                'nullable',
                'array',
            ],*/
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
            'availability_status_id' => [
                'required',
                'in:' . ProductStatusDataClass::get()->pluck('id')->implode(','),
            ],
            'special_offer_id' => [
                'nullable',
                'array',
                'in:' . ProductSpecialOfferOptionsDataClass::get()->pluck('id')->implode(','),
            ],
            'special_offer_id.*' => [
                'integer'
            ],
            'sku' => [
                'nullable',
                'string',
                'unique:products,sku'
            ],
/*            'old_price_in_currency' => [
                'nullable',
                'numeric',
            ],*/
            'price' => [
                'required',
                'numeric',
            ],
            /*'purchase_price_in_currency' => [
                'nullable', // required
                'numeric',
            ],*/
            'currency_id' => [
                'required',
                'exists:currencies,id',
            ],
            'main_image_deleted_input' => [
                'nullable',
            ],
            'gallery.*.id' => [
                'nullable'
            ],
            'gallery_color_ids.*.color_id' => [
                'nullable'
            ],
            'videos.*.iframe' => [
                'nullable'
            ],
            'attributes.*.*.price' => [
                'nullable'
            ],
            'all_color_ids.*.color_id' => [
                'nullable',
                'exists:colors,id',
            ],
            'length' => [
                $this->productType->has_length ? 'required' : 'nullable',
                'numeric',
            ],
            'width' => [
                $this->productType->has_width ? 'required' : 'nullable',
                'numeric',
            ],
            'height' => [
                $this->productType->has_height ? 'required' : 'nullable',
                'numeric',
            ],
        ];

        if ($this->input('gallery')) {
            foreach ($this->input('gallery') as $index => $gallery_item) {
                $rules['gallery.' . $index . '.image'] = [
                    (isset($gallery_item['id']) && $gallery_item['id']) ? 'nullable' : 'required',
                    'image',
                ];
            }
        }

        if ($this->productType->has_brand) {
            $rules['brand_id'] = [
                'required',
                'exists:brands,id',
            ];
        }

        /*if ($this->productType->has_brand || $this->productType->has_collection) {
            $rules['collection_id'] = [
                'required',
                Rule::exists('collections', 'id')->where('brand_id', $this->input('brand_id')),
            ];
        }*/

        if ($this->productType->has_category) {
            $rules['category_ids.*'] = [
                'required',
//                'array',
                'exists:categories,id',
            ];
        }

        if ($this->productType->has_color) {
            $rules['color_id'] = [
                'nullable',
                'exists:colors,id',
            ];
        }

        /*if ($this->productType->has_color) {
            $rules['all_color_ids'] = [
                'nullable',
                'array',
                'exists:colors,id',
            ];
        }*/

        if (count($this->productType->fields)) {
            $rules['custom_field'] = [
                'required',
                'array',
            ];
        }

        foreach ($this->productType->fields as $customField) {
            $rules['custom_field.*.field_id'] = [
                'required',
                'integer',
                'exists:product_fields,id',
            ];

            switch ($customField->field_type_id) {
                case ProductFieldTypeOptionsDataClass::FIELD_TYPE_STRING:
                    $rules['custom_field.' . $customField->id . '.value'] = [
                        'required',
                        'string',
                    ];
                    break;
                case ProductFieldTypeOptionsDataClass::FIELD_TYPE_SIZE:
                case ProductFieldTypeOptionsDataClass::FIELD_TYPE_NUMBER:
                    $rules['custom_field.' . $customField->id . '.value'] = [
                        'required',
                        'numeric',
                    ];
                    break;
                case ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION:
                    if ($customField->is_multiselectable) {
                        $rules['custom_field.' . $customField->id . '.value'] = [
                            'required',
                            'array',
                        ];
                    } else {
                        $rules['custom_field.' . $customField->id . '.value'] = [
                            'required',
                            'exists:product_field_options,id',
                        ];
                    }
                    break;
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

            $rules['product_text.' . $availableLanguage] = [
                'nullable',
                'string',
            ];

            $rules['characteristics.*.name.' . $availableLanguage] = [
                'nullable',
                'string'
            ];
            $rules['characteristics.*.value.' . $availableLanguage] = [
                'nullable',
                'string'
            ];
            $rules['videos.*.tab.' . $availableLanguage] = [
                'nullable',
                'string'
            ];
            $rules['attributes.*.*.name.' . $availableLanguage] = [
                'nullable',
                'string'
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

        return $rules;
    }

    public function rules(): array
    {
        $rules = $this->baseRules();

        /*$rules['main_image' ] = [
            'required',
            'image',
            'mimes:jpeg,png,jpg',
        ];*/

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [
//            'is_active' => mb_strtolower(trans('admin.product_is_active')),
            'slug' => mb_strtolower(trans('admin.slug')),
            'meta_title' => mb_strtolower(trans('admin.meta_title')),
            'meta_description' => mb_strtolower(trans('admin.meta_description')),
            'meta_keywords' => mb_strtolower(trans('admin.meta_keywords')),
            'product_text' => mb_strtolower(trans('admin.meta_keywords')),
            'availability_status_id' => mb_strtolower(trans('admin.availability_status')),
            'special_offer_id' => mb_strtolower(trans('admin.special_offer')),
            'sku' => mb_strtolower(trans('admin.sku')),
            'price' => mb_strtolower(trans('admin.price')),
            'old_price' => mb_strtolower(trans('admin.old_price')),
            'currency_id' => mb_strtolower(trans('admin.price_currency')),
            'country_id' => mb_strtolower(trans('admin.country')),
            'main_image' => mb_strtolower(trans('admin.product_main_image')),
            'brand_id' => mb_strtolower(trans('admin.brand')),
            'collection_id' => mb_strtolower(trans('admin.collection')),
            'category_id' => mb_strtolower(trans('admin.category')),
            'category_ids.*' => mb_strtolower(trans('admin.category')),
            'color_id' => mb_strtolower(trans('admin.color')),
            'all_color_ids' => mb_strtolower(trans('admin.all_colors')),
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $attributes['name.' . $availableLanguage] = $this->prepareAttribute(trans('admin.name'), $availableLanguage);
            $attributes['meta_title.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_title'), $availableLanguage);
            $attributes['meta_description.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_description'), $availableLanguage);
            $attributes['meta_keywords.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_keywords'), $availableLanguage);
            $attributes['product_text.' . $availableLanguage] = $this->prepareAttribute(trans('admin.product_text'), $availableLanguage);
        }

        foreach ($this->productType->fields as $customField) {
            $attributes['custom_field.' . $customField->id . '.value'] = mb_strtolower($customField->field_name);
        }

        return $attributes;
    }


    public function toDTO(): EditProductDTO
    {
        return new EditProductDTO(
//            (bool) $this->input('is_active'),
            $this->input('name'),
            $this->input('slug'),
            $this->input('meta_title'),
            $this->input('meta_description'),
            $this->input('meta_keywords'),
            $this->input('meta_tags'),
            explode(',', $this->input('selected_sub_products_id')),
            $this->input('availability_status_id'),
            $this->input('special_offer_id') ? array_map('intval', $this->input('special_offer_id')) : null,
            $this->input('sku'),
            $this->input('old_price'),
            $this->input('price'),
            $this->input('currency_id'),
            $this->input('product_text'),
            $this->file('main_image'),
            (bool) $this->input('main_image_deleted_input'),
            $this->validated('gallery'),
            $this->validated('gallery_color_ids'),
            $this->validated('characteristics'),
            $this->validated('videos'),
            $this->validated('attributes'),
            $this->input('country_id'),
            $this->input('brand_id'),
            $this->input('collection_id'),
            $this->input('category_ids'),
            $this->input('color_id'),
            $this->input('all_color_ids'),
    //            explode(',', $this->input('all_color_ids')),
            $this->input('custom_field'),
            $this->input('length'),
            $this->input('width'),
            $this->input('height'),
            $this->validated('faqs'),
            $this->input('seo_title'),
            $this->input('seo_text'),
        );
    }
}
