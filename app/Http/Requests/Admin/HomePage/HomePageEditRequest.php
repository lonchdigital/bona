<?php

namespace App\Http\Requests\Admin\HomePage;

use App\Http\Requests\BaseRequest;
use App\Models\HomePageConfig;
use App\Rules\RequiredImageDeletedRule;
use App\Services\HomePage\DTO\HomePageEditDTO;

class HomePageEditRequest extends BaseRequest
{
    public function rules(): array
    {
        $rules = [
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
            'slides.*.id' => [
                'nullable'
            ],
            'testimonials.*.id' => [
                'nullable'
            ],
            'testimonials.*.rating' => [
                'integer',
                'required'
            ],
            'testimonials.*.date' => [
                'string',
                'nullable'
            ],
            'testimonials.*.url' => [
                'string',
                'nullable'
            ],
            'selected_product_types' => [
                'nullable',
                'exists:product_types,id',
            ],
            'selected_products_id' => [
                'nullable',
                'exists:products,id',
            ],
            'selected_best_sales_products_id' => [
                'nullable',
                'exists:products,id',
            ],
            /*'selected_field_id' => [
                'required',
                'exists:product_fields,id',
            ],
            'selected_field_options_id' => [
                'required',
                'exists:product_field_options,id',
            ]*/
        ];

        if ($this->input('slides')) {
            foreach ($this->input('slides') as $index => $slide) {
                $rules['slides.' . $index . '.image'] = [
                    (isset($slide['id']) && $slide['id']) ? 'nullable' : 'required',
                    'image',
                ];
                $rules['slides.' . $index . '.button_url'] = [
                    'required',
                    'string'
                ];
            }
        }

        if ($this->input('testimonials')) {
            foreach ($this->input('testimonials') as $index => $testimonial) {
                $rules['testimonials.' . $index . '.image'] = [
                    (isset($testimonial['id']) && $testimonial['id']) ? 'nullable' : 'required',
                    'image',
                ];
            }
        }



        foreach ($this->availableLanguages as $availableLanguage) {
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
            $rules['slides.*.title.' . $availableLanguage] = [
                'nullable',
                'string'
            ];
            $rules['slides.*.description.' . $availableLanguage] = [
                'nullable',
                'string'
            ];
            $rules['slides.*.button_text.' . $availableLanguage] = [
                'required',
                'string'
            ];

            $rules['testimonials.*.name.' . $availableLanguage] = [
                'required',
                'string'
            ];
            $rules['testimonials.*.review.' . $availableLanguage] = [
                'required',
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
                'string'
            ];
            $rules['seo_text.' . $availableLanguage] = [
                'nullable',
                'string'
            ];
        }

        return  $rules;
    }

    public function attributes(): array
    {
        $attributes = [
            'meta_title' => mb_strtolower(trans('admin.meta_title')),
            'meta_description' => mb_strtolower(trans('admin.meta_description')),
            'meta_keywords' => mb_strtolower(trans('admin.meta_keywords')),
            'slider_logo' => mb_strtolower(trans('admin.slider_logo')),
            'slides.*.image' => mb_strtolower(trans('admin.slide_image')),
            'selected_field_id' => mb_strtolower(trans('admin.field')),
//            'selected_field_options_id' => mb_strtolower(trans('admin.field_options')),
        ];

        if ($this->input('slides')) {
            foreach ($this->input('slides') as $index => $slide) {
                $attributes['slides.' . $index . '.image'] = mb_strtolower(trans('admin.slide_image'));
                $attributes['slides.' . $index . '.image'] = mb_strtolower(trans('admin.slide_image'));

                $attributes['slides.' . $index . '.button_url'] = mb_strtolower(trans('admin.slide_text_link'));
            }
        }

        foreach ($this->availableLanguages as $availableLanguage) {
            $attributes['meta_title.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_title'), $availableLanguage);
            $attributes['meta_description.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_description'), $availableLanguage);
            $attributes['meta_keywords.' . $availableLanguage] = $this->prepareAttribute(trans('admin.meta_keywords'), $availableLanguage);
            $attributes['slides.*.title.' . $availableLanguage] = $this->prepareAttribute(trans('admin.slide_title'), $availableLanguage);
            $attributes['slides.*.description.' . $availableLanguage] = $this->prepareAttribute(trans('admin.slide_description'), $availableLanguage);
            $attributes['slides.*.button_text.' . $availableLanguage] = $this->prepareAttribute(trans('admin.slide_text_button'), $availableLanguage);
        }

        return $attributes;
    }

    public function toDTO(): HomePageEditDTO
    {
        return new HomePageEditDTO(
            $this->input('meta_title'),
            $this->input('meta_description'),
            $this->input('meta_keywords'),
            $this->input('meta_tags'),
            $this->validated('slides'),
            explode(',', $this->input('selected_product_types')),
            explode(',', $this->input('selected_products_id')),
            explode(',', $this->input('selected_best_sales_products_id')),
            $this->validated('testimonials'),
            $this->validated('faqs'),
            $this->input('seo_title'),
            $this->input('seo_text'),

//            $this->input('selected_field_id'),
//            explode(',', $this->input('selected_field_options_id')),
        );
    }
}
