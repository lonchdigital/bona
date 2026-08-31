<?php

namespace App\Http\Requests\Admin\HomePage;

use App\Http\Requests\BaseRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
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
                'string',
                'max:1000',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $selections = collect(explode(',', (string) $value))
                        ->map(fn (string $selection) => trim($selection))
                        ->filter()
                        ->unique();

                    if ($selections->count() > 20) {
                        $fail(trans('validation.max.array', ['attribute' => $attribute, 'max' => 20]));

                        return;
                    }

                    foreach ($selections as $selection) {
                        if (ctype_digit($selection) && ProductType::query()->whereKey($selection)->exists()) {
                            continue;
                        }

                        if (preg_match('/^category:(\d+)$/', $selection, $matches)
                            && Category::query()->whereKey($matches[1])->exists()) {
                            continue;
                        }

                        $fail(trans('validation.exists', ['attribute' => $attribute]));

                        return;
                    }
                },
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
                $this->relativeOrHttpUrlRule(),
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
            'content_sections' => [
                'nullable',
                'array',
            ],
            'content_sections.numbers.items' => [
                'nullable',
                'array',
                'max:6',
            ],
            'content_sections.numbers.items.*.value' => [
                'nullable',
                'string',
                'max:40',
            ],
            'content_sections.ideas.items' => [
                'nullable',
                'array',
                'max:6',
            ],
            'content_sections.steps.items' => [
                'nullable',
                'array',
                'max:10',
            ],
            'content_sections.steps.items.*.number' => [
                'nullable',
                'string',
                'max:20',
            ],
            'content_sections.works.items' => [
                'nullable',
                'array',
                'max:6',
            ],
            'selected_products_id' => [
                'nullable',
                'string',
                'max:1000',
                $this->commaSeparatedExistsRule(Product::class, 12),
            ],
            'selected_best_sales_products_id' => [
                'nullable',
                'string',
                'max:1000',
                $this->commaSeparatedExistsRule(Product::class, 12),
            ],
            'selected_brands_id' => [
                'nullable',
                'string',
                'max:1000',
                $this->commaSeparatedExistsRule(Brand::class, 12),
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

        foreach (['hero', 'catalog', 'popular', 'numbers', 'ideas', 'steps', 'works', 'reviews', 'instagram', 'blog', 'faq', 'partners', 'seo'] as $section) {
            $rules["content_sections.{$section}"] = ['nullable', 'array'];
            $rules["content_sections.{$section}.enabled"] = ['nullable', 'boolean'];
        }

        foreach ([
            'content_sections.hero.secondary_url',
            'content_sections.popular.link_url',
            'content_sections.steps.cta_url',
            'content_sections.works.link_url',
            'content_sections.reviews.link_url',
            'content_sections.instagram.link_url',
            'content_sections.blog.link_url',
            'content_sections.works.items.*.url',
        ] as $urlField) {
            $rules[$urlField] = ['nullable', 'string', 'max:500', $this->relativeOrHttpUrlRule()];
        }

        foreach (['ideas', 'works'] as $section) {
            $rules["content_sections.{$section}.items.*.existing_image_path"] = ['nullable', 'string', 'max:500'];
            $rules["content_sections.{$section}.items.*.image_deleted"] = ['nullable', 'boolean'];
            $rules["content_sections.{$section}.items.*.image"] = ['nullable', 'image', 'max:10240'];
            $rules["content_sections.{$section}.items.*.default_image"] = [
                'nullable',
                'string',
                'in:'.($section === 'ideas' ? 'bedroom,living,hall' : 'apartment,house,office'),
            ];
        }

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

            foreach ([
                'hero' => ['eyebrow', 'secondary_label'],
                'catalog' => ['kicker', 'title'],
                'popular' => ['kicker', 'title', 'link_label'],
                'numbers' => ['kicker', 'title'],
                'ideas' => ['kicker', 'title'],
                'steps' => ['kicker', 'title', 'cta_label'],
                'works' => ['kicker', 'title', 'link_label'],
                'reviews' => ['kicker', 'title', 'link_label'],
                'instagram' => ['kicker', 'title', 'link_label'],
                'blog' => ['kicker', 'title', 'link_label'],
                'faq' => ['kicker', 'title'],
                'partners' => ['kicker', 'title'],
            ] as $section => $fields) {
                foreach ($fields as $field) {
                    $rules["content_sections.{$section}.{$field}.{$availableLanguage}"] = [
                        'nullable',
                        'string',
                        'max:600',
                    ];
                }
            }

            $rules['content_sections.numbers.items.*.label.'.$availableLanguage] = ['nullable', 'string', 'max:180'];
            $rules['content_sections.ideas.items.*.title.'.$availableLanguage] = ['nullable', 'string', 'max:180'];
            $rules['content_sections.ideas.items.*.text.'.$availableLanguage] = ['nullable', 'string', 'max:600'];
            $rules['content_sections.steps.items.*.title.'.$availableLanguage] = ['nullable', 'string', 'max:180'];
            $rules['content_sections.steps.items.*.text.'.$availableLanguage] = ['nullable', 'string', 'max:600'];
            $rules['content_sections.works.items.*.title.'.$availableLanguage] = ['nullable', 'string', 'max:180'];
            $rules['content_sections.works.items.*.text.'.$availableLanguage] = ['nullable', 'string', 'max:600'];
        }

        foreach ($this->input('style_section.items', []) as $index => $item) {
            $hasExistingImage = filled($item['existing_image_path'] ?? null)
                && ! (bool) ($item['image_deleted'] ?? false);

            if (! $hasExistingImage) {
                $rules['style_section.items.'.$index.'.image'][] = 'required';
            }
        }

        foreach (['ideas', 'works'] as $section) {
            foreach ($this->input("content_sections.{$section}.items", []) as $index => $item) {
                $hasExistingImage = filled($item['existing_image_path'] ?? null)
                    && ! (bool) ($item['image_deleted'] ?? false);
                $hasDefaultImage = filled($item['default_image'] ?? null)
                    && ! (bool) ($item['image_deleted'] ?? false);

                if (! $hasExistingImage && ! $hasDefaultImage) {
                    $rules["content_sections.{$section}.items.{$index}.image"][] = 'required';
                }
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
            collect(explode(',', (string) $this->input('selected_product_types')))
                ->map(fn (string $selection) => trim($selection))
                ->filter()
                ->unique()
                ->values()
                ->all(),
            $this->validated('style_section'),
            $this->validated('content_sections'),
            $this->commaSeparatedValues('selected_products_id'),
            $this->commaSeparatedValues('selected_best_sales_products_id'),
            $this->commaSeparatedValues('selected_brands_id'),
            $this->validated('testimonials'),
            $this->validated('faqs'),
            $this->input('seo_title'),
            $this->input('seo_text'),
        );
    }

    private function commaSeparatedExistsRule(string $modelClass, int $max): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($modelClass, $max): void {
            $ids = collect(explode(',', (string) $value))
                ->map(fn (string $id) => trim($id))
                ->filter()
                ->unique();

            if ($ids->count() > $max) {
                $fail(trans('validation.max.array', ['attribute' => $attribute, 'max' => $max]));

                return;
            }

            if ($ids->contains(fn (string $id) => ! ctype_digit($id))
                || $modelClass::query()->whereKey($ids->all())->count() !== $ids->count()) {
                $fail(trans('validation.exists', ['attribute' => $attribute]));
            }
        };
    }

    private function relativeOrHttpUrlRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            $value = trim((string) $value);

            if ($value === '' || str_starts_with($value, '#')) {
                return;
            }

            if (str_starts_with($value, '/')
                && ! str_starts_with($value, '//')
                && ! str_contains($value, '\\')) {
                return;
            }

            if (filter_var($value, FILTER_VALIDATE_URL)
                && in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true)) {
                return;
            }

            $fail(trans('validation.url', ['attribute' => $attribute]));
        };
    }

    private function commaSeparatedValues(string $key): array
    {
        return collect(explode(',', (string) $this->input($key)))
            ->map(fn (string $value) => trim($value))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
