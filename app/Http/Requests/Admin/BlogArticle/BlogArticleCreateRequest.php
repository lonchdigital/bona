<?php

namespace App\Http\Requests\Admin\BlogArticle;

use App\DataClasses\BlogArticleBlockTypesDataClass;
use App\Http\Requests\BaseRequest;
use App\Models\BlogArticle;
use App\Models\BlogArticleSlugRedirect;
use App\Services\BlogArticle\DTO\EditBlogArticleDTO;

class BlogArticleCreateRequest extends BaseRequest
{
    protected function prepareForValidation(): void
    {
        $submittedSlugs = $this->input('slug', []);
        $currentArticle = $this->route('blogArticle');

        // Accept the old scalar payload during a rolling deployment, but save
        // every article with one explicit URL slug per storefront language.
        if (! is_array($submittedSlugs)) {
            $submittedSlugs = [config('app.fallback_locale') => $submittedSlugs];
        }

        $slugs = [];

        foreach ($this->availableLanguages as $locale) {
            $submittedSlug = trim((string) ($submittedSlugs[$locale] ?? ''));
            $title = trim((string) $this->input('name.'.$locale, ''));
            $currentSlug = $currentArticle instanceof BlogArticle
                ? $currentArticle->slugForLocale($locale)
                : null;

            $slugs[$locale] = $currentSlug !== null && $submittedSlug === $currentSlug
                ? $currentSlug
                : BlogArticle::slugFromTitle($submittedSlug !== '' ? $submittedSlug : $title, $locale);
        }

        $this->merge(['slug' => $slugs]);
    }

    public function baseRules(): array
    {
        $rules = [
            'name' => [
                'array',
            ],
            'slug' => [
                'array',
                'required',
            ],
            'preview_text' => [
                'array',
            ],
            'meta_tags' => [
                'nullable',
                'string',
            ],
            'hero_image_deleted' => [
                'nullable',
            ],
            'block' => [
                'array',
            ],
            'block.*.type_id' => [
                'integer',
                'in:'.BlogArticleBlockTypesDataClass::get()->pluck('id')->implode(','),
            ],
        ];

        if ($this->input('block')) {
            foreach ($this->input('block') as $index => $customBlock) {
                if ($customBlock['type_id'] == BlogArticleBlockTypesDataClass::TYPE_TEXT) {
                    $this->addTextBlockRules($rules, $index);
                } elseif ($customBlock['type_id'] == BlogArticleBlockTypesDataClass::TYPE_IMAGE) {
                    $this->addImageBlockRules($rules, $index);
                } elseif ($customBlock['type_id'] == BlogArticleBlockTypesDataClass::TYPE_QUOTE) {
                    $this->addQuoteBlockRules($rules, $index);
                } elseif ($customBlock['type_id'] == BlogArticleBlockTypesDataClass::TYPE_SPONSOR) {
                    $this->addSponsorBlockRules($rules, $index);
                } elseif ($customBlock['type_id'] == BlogArticleBlockTypesDataClass::TYPE_VIDEO) {
                    $this->addVideoBlockRules($rules, $index);
                } elseif ($customBlock['type_id'] == BlogArticleBlockTypesDataClass::TYPE_SLIDER) {
                    $this->addImageBlockRules($rules, $index);
                } elseif ($customBlock['type_id'] == BlogArticleBlockTypesDataClass::TYPE_QUESTIONS_AND_ANSWERS) {
                    $this->addQuestionsAndAnswersBlockRules($rules, $index);
                }
            }
        }

        foreach ($this->availableLanguages as $availableLanguage) {
            $rules['name.'.$availableLanguage] = [
                'required',
                'string',
            ];
            $rules['preview_text.'.$availableLanguage] = [
                'required',
                'string',
            ];
            $rules['slug.'.$availableLanguage] = [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) use ($availableLanguage): void {
                    $currentArticle = $this->route('blogArticle');
                    $currentArticleId = $currentArticle instanceof BlogArticle ? $currentArticle->getKey() : null;
                    $isUnchangedCurrentSlug = $currentArticle instanceof BlogArticle
                        && $currentArticle->slugForLocale($availableLanguage) === $value;

                    if (! $isUnchangedCurrentSlug && mb_strlen((string) $value) > 180) {
                        $fail(trans('validation.max.string', [
                            'attribute' => $this->prepareAttribute(trans('admin.slug'), $availableLanguage),
                            'max' => 180,
                        ]));
                    }

                    $duplicateArticle = BlogArticle::query()
                        ->where("slugs->{$availableLanguage}", $value)
                        ->when($currentArticleId, fn ($query) => $query->whereKeyNot($currentArticleId))
                        ->exists();

                    $duplicateRedirect = BlogArticleSlugRedirect::query()
                        ->where('locale', $availableLanguage)
                        ->where('slug', $value)
                        ->when($currentArticleId, fn ($query) => $query->where('blog_article_id', '!=', $currentArticleId))
                        ->exists();

                    if ($duplicateArticle || $duplicateRedirect) {
                        $fail(trans('admin.blog_article_slug_already_exists'));
                    }

                    $otherSlugs = collect($this->input('slug', []))
                        ->except($availableLanguage)
                        ->filter();

                    $isUnchangedLegacyPair = $currentArticle instanceof BlogArticle
                        && $currentArticle->slugForLocale($availableLanguage) === $value
                        && collect($currentArticle->localizedSlugs())
                            ->except($availableLanguage)
                            ->containsStrict($value);

                    if ($otherSlugs->containsStrict($value) && ! $isUnchangedLegacyPair) {
                        $fail(trans('admin.blog_article_slug_must_differ'));
                    }
                },
            ];
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
        }

        return $rules;
    }

    private function addTextBlockRules(array &$rules, int $index): void
    {
        foreach ($this->availableLanguages as $availableLanguage) {
            $rules['block.'.$index.'.'.$availableLanguage] = [
                'required',
                'string',
            ];
        }
    }

    private function addImageBlockRules(array &$rules, int $index): void
    {
        $rules['block.'.$index.'.images.*.image_deleted'] = [
            'nullable',
        ];

        foreach ($this->input('block')[$index]['images'] as $subIndex => $customSubBlock) {
            $rules['block.'.$index.'.images.'.$subIndex.'.product_id'] = [
                'nullable',
                'integer',
                'exists:products,id',
            ];

            if ($customSubBlock['product_id']) {
                $rules['block.'.$index.'.images.'.$subIndex.'.top'] = [
                    'required',
                    'numeric',
                ];
                $rules['block.'.$index.'.images.'.$subIndex.'.left'] = [
                    'required',
                    'numeric',
                ];
            }
        }
    }

    private function addQuoteBlockRules(array &$rules, int $index): void
    {
        $quoteAuthorIsRequired = false;
        $quoteAuthorPositionIsRequired = false;
        if (is_array($this->input('block')[$index]['quote_author'])) {
            foreach ($this->input('block')[$index]['quote_author'] as $quoteAuthorOnLanguage) {
                if ($quoteAuthorOnLanguage) {
                    $quoteAuthorIsRequired = true;
                    $quoteAuthorPositionIsRequired = true;
                }
            }
        }

        if (is_array($this->input('block')[$index]['quote_author_position'])) {
            foreach ($this->input('block')[$index]['quote_author_position'] as $quoteAuthorPositionOnLanguage) {
                if ($quoteAuthorPositionOnLanguage) {
                    $quoteAuthorPositionIsRequired = true;
                    $quoteAuthorIsRequired = true;
                }
            }
        }

        foreach ($this->availableLanguages as $availableLanguage) {
            $rules['block.'.$index.'.quote.'.$availableLanguage] = [
                'required',
                'string',
            ];
            $rules['block.'.$index.'.quote_author.'.$availableLanguage] = [
                $quoteAuthorIsRequired ? 'required' : 'nullable',
                'string',
            ];
            $rules['block.'.$index.'.quote_author_position.'.$availableLanguage] = [
                $quoteAuthorPositionIsRequired ? 'required' : 'nullable',
                'string',
            ];
        }
    }

    private function addSponsorBlockRules(array &$rules, int $index): void
    {
        foreach ($this->availableLanguages as $availableLanguage) {
            $rules['block.'.$index.'.sponsor_text.'.$availableLanguage] = [
                'required',
                'string',
            ];
        }
        $rules['block.'.$index.'.sponsor_link'] = [
            'required',
            'string',
            'url',
        ];
    }

    private function addVideoBlockRules(array &$rules, int $index): void
    {
        $rules['block.'.$index.'.video_link'] = [
            'required',
            'string',
            'url',
            'regex:/^((?:https?:)?\/\/)?((?:www|m)\.)?((?:youtube?\.com|youtu.be))(\/(?:[\w\-]+\?v=|embed\/|live\/|v\/)?)([\w\-]+)(\S+)?$/',
        ];
    }

    private function addQuestionsAndAnswersBlockRules(array &$rules, int $index): void
    {
        foreach ($this->availableLanguages as $availableLanguage) {
            $rules['block.'.$index.'.questions.*.question.'.$availableLanguage] = [
                'required',
                'string',
            ];
            $rules['block.'.$index.'.questions.*.answer.'.$availableLanguage] = [
                'required',
                'string',
            ];
        }
    }

    public function rules(): array
    {
        $rules = $this->baseRules();

        $rules['hero_image'] = [
            'required',
            'mimes:jpeg,png,jpg',
        ];

        if ($this->input('block')) {
            foreach ($this->input('block') as $index => $customBlock) {
                if ($customBlock['type_id'] == BlogArticleBlockTypesDataClass::TYPE_IMAGE) {
                    $rules['block.'.$index.'.images.*.image'] = [
                        'required',
                        'mimes:jpeg,png,jpg',
                    ];
                } elseif ($customBlock['type_id'] == BlogArticleBlockTypesDataClass::TYPE_QUOTE) {
                    $rules['block.'.$index.'.quote_author_image'] = [
                        'nullable',
                        'mimes:jpeg,png,jpg',
                    ];
                } elseif ($customBlock['type_id'] == BlogArticleBlockTypesDataClass::TYPE_SPONSOR) {
                    $rules['block.'.$index.'.sponsor_image'] = [
                        'required',
                        'mimes:jpeg,png,jpg',
                    ];
                } elseif ($customBlock['type_id'] == BlogArticleBlockTypesDataClass::TYPE_SLIDER) {
                    $rules['block.'.$index.'.images.*.image'] = [
                        'required',
                        'mimes:jpeg,png,jpg',
                        'dimensions:ratio=2/1,ratio=1/2',
                    ];
                }
            }
        }

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [];

        $attributes['hero_image'] = mb_strtolower(trans('admin.blog_article_block_image_title'));

        foreach ($this->availableLanguages as $availableLanguage) {
            $attributes['name.'.$availableLanguage] = $this->prepareAttribute(trans('admin.name'), $availableLanguage);
            $attributes['slug.'.$availableLanguage] = $this->prepareAttribute(trans('admin.slug'), $availableLanguage);
            $attributes['preview_text.'.$availableLanguage] = $this->prepareAttribute(trans('admin.blog_article_preview_text'), $availableLanguage);
        }

        if ($this->input('block')) {
            foreach ($this->input('block') as $index => $customBlock) {
                if ($customBlock['type_id'] == BlogArticleBlockTypesDataClass::TYPE_TEXT) {
                    foreach ($this->availableLanguages as $availableLanguage) {
                        $attributes['block.'.$index.'.'.$availableLanguage] = $this->prepareAttribute(trans('admin.blog_article_block_text'), $availableLanguage);
                    }
                } elseif ($customBlock['type_id'] == BlogArticleBlockTypesDataClass::TYPE_IMAGE) {
                    $attributes['block.'.$index.'.images.*.image'] = mb_strtolower(trans('admin.image'));
                } elseif ($customBlock['type_id'] == BlogArticleBlockTypesDataClass::TYPE_QUOTE) {
                    foreach ($this->availableLanguages as $availableLanguage) {
                        $attributes['block.'.$index.'.quote.'.$availableLanguage] = $this->prepareAttribute(trans('admin.blog_article_block_quote'), $availableLanguage);
                        $attributes['block.'.$index.'.quote_author.'.$availableLanguage] = $this->prepareAttribute(trans('admin.blog_article_block_quote_author'), $availableLanguage);
                        $attributes['block.'.$index.'.quote_author_position.'.$availableLanguage] = $this->prepareAttribute(trans('admin.blog_article_block_quote_author_position'), $availableLanguage);
                    }
                } elseif ($customBlock['type_id'] == BlogArticleBlockTypesDataClass::TYPE_SPONSOR) {
                    $attributes['block.'.$index.'.sponsor_image'] = mb_strtolower(trans('admin.sponsor_image'));
                    $attributes['block.'.$index.'.sponsor_link'] = mb_strtolower(trans('admin.sponsor_link'));
                    foreach ($this->availableLanguages as $availableLanguage) {
                        $attributes['block.'.$index.'.sponsor_text.'.$availableLanguage] = $this->prepareAttribute(trans('admin.sponsor_text'), $availableLanguage);
                    }
                } elseif ($customBlock['type_id'] == BlogArticleBlockTypesDataClass::TYPE_VIDEO) {
                    $attributes['block.'.$index.'.video_link'] = mb_strtolower(trans('admin.video_link'));
                } elseif ($customBlock['type_id'] == BlogArticleBlockTypesDataClass::TYPE_SLIDER) {
                    $attributes['block.'.$index.'.images.*.image'] = mb_strtolower(trans('admin.slide_image'));
                } elseif ($customBlock['type_id'] == BlogArticleBlockTypesDataClass::TYPE_QUESTIONS_AND_ANSWERS) {
                    foreach ($this->availableLanguages as $availableLanguage) {
                        $attributes['block.'.$index.'.questions.*.question.'.$availableLanguage] = $this->prepareAttribute(trans('admin.blog_article_block_question'), $availableLanguage);
                        $attributes['block.'.$index.'.questions.*.answer.'.$availableLanguage] = $this->prepareAttribute(trans('admin.blog_article_block_answer'), $availableLanguage);
                    }

                }
            }
        }

        return $attributes;
    }

    public function toDTO(): EditBlogArticleDTO
    {
        return new EditBlogArticleDTO(
            $this->input('name'),
            $this->input('slug'),
            $this->input('preview_text'),
            $this->input('meta_title'),
            $this->input('meta_description'),
            $this->input('meta_keywords'),
            $this->input('meta_tags'),
            $this->file('hero_image'),
            $this->validated('block'),
        );
    }
}
