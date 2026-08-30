<?php

namespace App\Http\Requests\Admin\HomePage;

use App\Http\Requests\BaseRequest;
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
                'nullable',
            ],
            'testimonials.*.id' => [
                'nullable',
            ],
            'testimonials.*.rating' => [
                'integer',
                'required',
            ],
            'testimonials.*.date' => [
                'string',
                'nullable',
            ],
            'testimonials.*.url' => [
                'string',
                'nullable',
            ],
            'selected_product_types' => [
                'nullable',
                'exists:product_types,id',
            ],
            'style_section' => [
                'nullable',
                'array',
            ],
            'style_section.enabled' => [
                'nullable',
                'boolean',
            ],
            'style_section.cta_url' => [
                'nullable',
                'string',
                'max:500',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($value === '' || str_starts_with($value, '/') || str_starts_with($value, '#')) {
                        return;
                    }

                    if (filter_var($value, FILTER_VALIDATE_URL) && in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true)) {
                        return;
                    }

                    $fail(trans('validation.url', ['attribute' => $attribute]));
                },
            ],
            'style_section.items' => [
                'nullable',
                'array',
                'max:10',
            ],
            'style_section.items.*.existing_image_path' => [
                'nullable',
                'string',
                'max:500',
            ],
            'style_section.items.*.image_deleted' => [
                'nullable',
                'boolean',
            ],
            'style_section.items.*.image' => [
                'nullable',
                'image',
                'max:10240',
            ],
            'selected_products_id' => [
                'nullable',
                'exists:products,id',
            ],
            'selected_best_sales_products_id' => [
                'nullable',
                'exists:products,id',
            ],
            'selected_brands_id' => [
                'nullable',
                'exists:brands,id',
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
                $rules['slides.'.$index.'.image'] = [
                    (isset($slide['id']) && $slide['id']) ? 'nullable' : 'required',
                    'image',
                ];
                $rules['slides.'.$index.'.image_mobile'] = [
                    (isset($slide['id']) && $slide['id']) ? 'nullable' : 'required',
                    'image',
                ];
                $rules['slides.'.$index.'.overlay_opacity'] = [
                    'nullable',
                    'integer',
                    'between:0,100',
                ];
                $rules['slides.'.$index.'.button_url'] = [
                    'required',
                    'string',
                ];
                $rules['slides.'.$index.'.slide_url'] = [
                    'nullable',
                    'string',
                ];
                $rules['slides.'.$index.'.display_button'] = [
                    'nullable',
                    'boolean',
                ];
            }
        }

        if ($this->input('testimonials')) {
            foreach ($this->input('testimonials') as $index => $testimonial) {
                $rules['testimonials.'.$index.'.image'] = [
                    (isset($testimonial['id']) && $testimonial['id']) ? 'nullable' : 'required',
                    'image',
                ];
            }
        }

        foreach ($this->availableLanguages as $availableLanguage) {
            $rules['meta_title.'.$availableLanguage] = [
                'nullable',
                'string',
            ];
            $rules['meta_description.'.$availableLanguage] = [
                'nullable',
                'string',
            ];
            $rules['meta_keywords.'.$availableLanguage] = [
                'nullable',
                'string',
            ];
            $rules['slides.*.title.'.$availableLanguage] = [
                'nullable',
                'string',
            ];
            $rules['slides.*.description.'.$availableLanguage] = [
                'nullable',
                'string',
            ];
            $rules['slides.*.button_text.'.$availableLanguage] = [
                'required',
                'string',
            ];

            $rules['testimonials.*.name.'.$availableLanguage] = [
                'required',
                'string',
            ];
            $rules['testimonials.*.review.'.$availableLanguage] = [
                'required',
                'string',
            ];

            $rules['faqs.*.question.'.$availableLanguage] = [
                'required',
                'string',
            ];
            $rules['faqs.*.answer.'.$availableLanguage] = [
                'required',
                'string',
            ];
            $rules['seo_title.'.$availableLanguage] = [
                'nullable',
                'string',
            ];
            $rules['seo_text.'.$availableLanguage] = [
                'nullable',
                'string',
            ];
            $rules['style_section.kicker.'.$availableLanguage] = [
                'nullable',
                'string',
                'max:100',
            ];
            $rules['style_section.title.'.$availableLanguage] = [
                'nullable',
                'string',
                'max:180',
            ];
            $rules['style_section.description.'.$availableLanguage] = [
                'nullable',
                'string',
                'max:600',
            ];
            $rules['style_section.cta_label.'.$availableLanguage] = [
                'nullable',
                'string',
                'max:160',
            ];
            $rules['style_section.items.*.name.'.$availableLanguage] = [
                'nullable',
                'string',
                'max:100',
            ];
        }

        foreach ($this->input('style_section.items', []) as $index => $item) {
            $hasExistingImage = filled($item['existing_image_path'] ?? null)
                && ! (bool) ($item['image_deleted'] ?? false);

            if (! $hasExistingImage) {
                $rules['style_section.items.'.$index.'.image'][] = 'required';
            }
        }

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [
            'meta_title' => mb_strtolower(trans('admin.meta_title')),
            'meta_description' => mb_strtolower(trans('admin.meta_description')),
            'meta_keywords' => mb_strtolower(trans('admin.meta_keywords')),
            'slider_logo' => mb_strtolower(trans('admin.slider_logo')),
            'slides.*.image' => mb_strtolower(trans('admin.slide_image')),
            'slides.*.image_mobile' => mb_strtolower(trans('admin.slide_image')),
            'selected_field_id' => mb_strtolower(trans('admin.field')),
            //            'selected_field_options_id' => mb_strtolower(trans('admin.field_options')),
        ];

        if ($this->input('slides')) {
            foreach ($this->input('slides') as $index => $slide) {
                $attributes['slides.'.$index.'.image'] = mb_strtolower(trans('admin.slide_image'));
                $attributes['slides.'.$index.'.image_mobile'] = mb_strtolower(trans('admin.slide_image_mobile'));

                $attributes['slides.'.$index.'.button_url'] = mb_strtolower(trans('admin.slide_text_link'));
            }
        }

        foreach ($this->availableLanguages as $availableLanguage) {
            $attributes['meta_title.'.$availableLanguage] = $this->prepareAttribute(trans('admin.meta_title'), $availableLanguage);
            $attributes['meta_description.'.$availableLanguage] = $this->prepareAttribute(trans('admin.meta_description'), $availableLanguage);
            $attributes['meta_keywords.'.$availableLanguage] = $this->prepareAttribute(trans('admin.meta_keywords'), $availableLanguage);
            $attributes['slides.*.title.'.$availableLanguage] = $this->prepareAttribute(trans('admin.slide_title'), $availableLanguage);
            $attributes['slides.*.description.'.$availableLanguage] = $this->prepareAttribute(trans('admin.slide_description'), $availableLanguage);
            $attributes['slides.*.button_text.'.$availableLanguage] = $this->prepareAttribute(trans('admin.slide_text_button'), $availableLanguage);
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
            $this->validated('style_section'),
            explode(',', $this->input('selected_products_id')),
            explode(',', $this->input('selected_best_sales_products_id')),
            explode(',', $this->input('selected_brands_id')),
            $this->validated('testimonials'),
            $this->validated('faqs'),
            $this->input('seo_title'),
            $this->input('seo_text'),
        );
    }
}
