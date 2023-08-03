<?php

namespace App\Excel\Imports\Sheet;

use App\DataClasses\ProductFieldTypeOptionsDataClass;
use App\DataClasses\ProductSpecialOfferOptionsDataClass;
use App\DataClasses\ProductStatusDataClass;
use App\Models\ProductType;
use App\Services\Application\ApplicationConfigService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductsSheet implements ToArray, WithValidation, WithStartRow, SkipsEmptyRows, SkipsOnFailure
{
    use Importable, SkipsFailures;

    protected array $rowsToSave = [];

    public function __construct(
        private readonly ProductType              $productType,
        private readonly ApplicationConfigService $applicationService,
        private readonly Collection               $countries,
        private readonly Collection               $categories,
        private readonly Collection               $brands,
        private readonly Collection               $currencies,
        private readonly Collection               $collections,
        private readonly Collection               $colors,
        private readonly Collection               $productFieldOptions,
    ) { }

    public function array(array $array)
    {
        $this->rowsToSave = $array;
    }

    public function isEmptyWhen(array $row): bool
    {
        $rowIsEmpty = false;
        foreach ($row as $column) {
            if (str_replace(' ', '', $column) !== '') {
                $rowIsEmpty = false;
            }
        }

        return $rowIsEmpty;
    }

    public function rules(): array
    {
        $rules = [
            //parent_article
            0 => [
                'nullable',
                //'string'
            ],

            //article
            1 => [
                'required',
                //'string',
            ],

        ];

        //name
        foreach ($this->applicationService->getAvailableLanguages() as $languageCode) {
            $rules[] = [
                'required',
                'string',
            ];
        }

        //meta title
        foreach ($this->applicationService->getAvailableLanguages() as $languageCode) {
            $rules[] = [
                'nullable',
                'string',
            ];
        }

        //meta description
        foreach ($this->applicationService->getAvailableLanguages() as $languageCode) {
            $rules[] = [
                'nullable',
                'string',
            ];
        }

        //meta keywords
        foreach ($this->applicationService->getAvailableLanguages() as $languageCode) {
            $rules[] = [
                'nullable',
                'string',
            ];
        }

        //availability status
        $rules[] = [
            'required',
            'string',
            function ($attribute, $value, $failure) {
                $attribute = $this->customValidationAttributes()['*.'.preg_replace('/^.+\./', '', $attribute)] ?? $attribute;
                $statusesInAllLanguages = collect([]);
                $transKeys = ProductStatusDataClass::get()->pluck('trans_key');

                foreach (app()->make(ApplicationConfigService::class)->getAvailableLanguages() as $availableLanguage) {
                    foreach ($transKeys as $transKey) {
                        $statusesInAllLanguages->push(trans($transKey, [], $availableLanguage));
                    }
                }

                if (!$statusesInAllLanguages->contains($value)) {
                    $failure(trans('admin.products_import_field_incorrect', ['ATTRIBUTE' => $attribute, 'VALUE' => $value]));
                }
            }
        ];

        //special offer
        $rules[] = [
            'nullable',
            function ($attribute, $value, $failure) {
                $attribute = $this->customValidationAttributes()['*.'.preg_replace('/^.+\./', '', $attribute)] ?? $attribute;

                if ($value) {
                    $value = str_replace(', ', ',', $value);
                    $values = explode(',', $value);


                    foreach ($values as $parsedValue) {
                        if (!ProductSpecialOfferOptionsDataClass::get()->pluck('name')->contains($parsedValue)) {
                            $failure(trans('admin.products_import_field_incorrect', ['ATTRIBUTE' => $attribute, 'VALUE' => $parsedValue]));
                        }
                    }

                }
            }
        ];

        //price in currency
        $rules[] = [
            'required',
            'numeric',
        ];

        //purchase price in currency
        $rules[] = [
            'required',
            'numeric',
        ];


        //old price in currency
        $rules[] = [
            'nullable',
            'numeric',
        ];

        //currency
        $rules[] = [
            'required',
            'in:' .$this->currencies->pluck('code')->implode(','),
        ];

        //country
        $rules[] = [
            'required',
            function ($attribute, $value, $failure) {
                $attribute = $this->customValidationAttributes()['*.'.preg_replace('/^.+\./', '', $attribute)] ?? $attribute;

                $found = false;
                foreach ($this->countries as $country) {
                    if (in_array($value, array_values($country->getTranslations('name')))) {
                        $found = true;
                    }
                }

                if (!$found) {
                    $failure(trans('validation.exists', ['attribute' => $attribute]));
                }
            }
        ];

        //brand
        $rules[] = [
            'required',
            function ($attribute, $value, $failure) {
                $attribute = $this->customValidationAttributes()['*.'.preg_replace('/^.+\./', '', $attribute)] ?? $attribute;

                $found = false;
                foreach ($this->brands as $brand) {
                    if (in_array($value, array_values($brand->getTranslations('name')))) {
                        $found = true;
                    }
                }

                if (!$found) {
                    $failure(trans('validation.exists', ['attribute' => $attribute]));
                }
            }
        ];

        //collection
        $rules[] = [
            'required',
            function ($attribute, $value, $failure) {
                $attribute = $this->customValidationAttributes()['*.'.preg_replace('/^.+\./', '', $attribute)] ?? $attribute;

                $found = false;
                foreach ($this->collections as $collection) {
                    if (in_array($value, array_values($collection->getTranslations('name')))) {
                        $found = true;
                    }
                }

                if (!$found) {
                    $failure(trans('validation.exists', ['attribute' => $attribute]));
                }
            }
        ];

        //categories
        $rules[] = [
            'nullable',
            function ($attribute, $value, $failure) {
                $attribute = $this->customValidationAttributes()['*.'.preg_replace('/^.+\./', '', $attribute)] ?? $attribute;

                if ($value) {
                    $value = str_replace(', ', ',', $value);
                    $values = explode(',', $value);

                    foreach ($values as $parsedValue) {
                        $found = false;

                        foreach ($this->categories as $category) {
                            if (in_array($parsedValue, array_values($category->getTranslations('name')))) {
                                $found = true;
                            }
                        }

                        if (!$found) {
                            $failure(trans('admin.products_import_field_incorrect', ['ATTRIBUTE' => $attribute, 'VALUE' => $parsedValue]));
                        }
                    }
                }
            }
        ];

        //color
        $rules[] = [
            'required',
            function ($attribute, $value, $failure) {
                $attribute = $this->customValidationAttributes()['*.'.preg_replace('/^.+\./', '', $attribute)] ?? $attribute;

                $found = false;
                foreach ($this->colors as $color) {
                    if (in_array($value, array_values($color->getTranslations('name')))) {
                        $found = true;
                    }
                }

                if (!$found) {
                    $failure(trans('validation.exists', ['attribute' => $attribute]));
                }
            }
        ];

        //all colors
        $rules[] = [
            'nullable',
            function ($attribute, $value, $failure) {
                $attribute = $this->customValidationAttributes()['*.'.preg_replace('/^.+\./', '', $attribute)] ?? $attribute;

                if ($value) {
                    $value = str_replace(', ', ',', $value);
                    $values = explode(',', $value);

                    foreach ($values as $parsedValue) {
                        $found = false;

                        foreach ($this->colors as $color) {
                            if (in_array($parsedValue, array_values($color->getTranslations('name')))) {
                                $found = true;
                            }
                        }

                        if (!$found) {
                            $failure(trans('admin.products_import_field_incorrect', ['ATTRIBUTE' => $attribute, 'VALUE' => $parsedValue]));
                        }
                    }
                }
            }
        ];

        if ($this->productType->has_size) {
            if ($this->productType->has_length) {
                $rules[] = [
                    'required',
                    'numeric',
                ];
            }

            if ($this->productType->has_width) {
                $rules[] = [
                    'required',
                    'numeric',
                ];
            }

            if ($this->productType->has_height) {
                $rules[] = [
                    'required',
                    'numeric',
                ];
            }
        }

        foreach ($this->productType->fields as $customField) {
            if ($customField->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_STRING) {
                $rules[] = [
                    'required',
                    'string',
                ];
            } else if ($customField->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_NUMBER ||
                $customField->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_SIZE) {
                $rules[] = [
                    'required',
                    'numeric'
                ];
            } else if ($customField->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION) {
                if ($customField->is_multiselectable) {
                    $rules[] = [
                        'nullable',
                        function ($attribute, $value, $failure) {
                            $attribute = $this->customValidationAttributes()['*.'.preg_replace('/^.+\./', '', $attribute)] ?? $attribute;

                            if ($value) {
                                $value = str_replace(', ', ',', $value);
                                $values = explode(',', $value);

                                foreach ($values as $parsedValue) {
                                    $found = false;

                                    foreach ($this->productFieldOptions as $option) {
                                        if (in_array($parsedValue, array_values($option->getTranslations('name')))) {
                                            $found = true;
                                        }
                                    }

                                    if (!$found) {
                                        $failure(trans('admin.products_import_field_incorrect', ['ATTRIBUTE' => $attribute, 'VALUE' => $parsedValue]));
                                    }
                                }
                            }
                        }
                    ];
                } else {
                    $rules[] = [
                        'required',
                        function ($attribute, $value, $failure) {
                            $attribute = $this->customValidationAttributes()['*.'.preg_replace('/^.+\./', '', $attribute)] ?? $attribute;

                            $found = false;
                            foreach ($this->productFieldOptions as $option) {
                                if (in_array($value, array_values($option->getTranslations('name')))) {
                                    $found = true;
                                }
                            }

                            if (!$found) {
                                $failure(trans('validation.exists', ['attribute' => $attribute]));
                            }
                        }
                    ];
                }
            }
        }

        return $rules;
    }


    public function customValidationAttributes(): array
    {
        $attributes = [
            0 => mb_strtolower(trans('admin.parent_sku')),
            1 => mb_strtolower(trans('admin.sku')),
        ];

        //name
        foreach ($this->applicationService->getAvailableLanguages() as $languageCode) {
            $attributes[] = mb_strtolower(trans('admin.name')) . ' ' . mb_strtoupper($languageCode);
        }

        //meta title
        foreach ($this->applicationService->getAvailableLanguages() as $languageCode) {
            $attributes[] = mb_strtolower(trans('admin.meta_title')) . ' ' . mb_strtoupper($languageCode);
        }

        //meta description
        foreach ($this->applicationService->getAvailableLanguages() as $languageCode) {
            $attributes[] = mb_strtolower(trans('admin.meta_description')) . ' ' . mb_strtoupper($languageCode);
        }

        //meta keywords
        foreach ($this->applicationService->getAvailableLanguages() as $languageCode) {
            $attributes[] = mb_strtolower(trans('admin.meta_keywords')) . ' ' . mb_strtoupper($languageCode);
        }

        //availability status
        $attributes[] = mb_strtolower(trans('admin.availability_status'));

        //special offers
        $attributes[] = mb_strtolower(trans('admin.special_offer'));

        //price in currency
        $attributes[] = mb_strtolower(trans('admin.price_in_currency'));

        //purchase price in currency
        $attributes[] = mb_strtolower(trans('admin.purchase_price_in_currency'));

        //old price in currency
        $attributes[] = mb_strtolower(trans('admin.old_price_in_currency'));

        //price currency
        $attributes[] = mb_strtolower(trans('admin.price_currency'));

        //country
        $attributes[] = mb_strtolower(trans('admin.country'));

        //brand
        $attributes[] = mb_strtolower(trans('admin.brand'));

        //collection
        $attributes[] = mb_strtolower(trans('admin.collection'));

        //categories
        $attributes[] = mb_strtolower(trans('admin.product_categories'));

        //color
        $attributes[] = mb_strtolower(trans('admin.color'));

        //all colors
        $attributes[] = mb_strtolower(trans('admin.all_colors'));

        if ($this->productType->has_size) {
            if ($this->productType->has_length) {
                $attributes[] = mb_strtolower(trans('admin.length'));
            }

            if ($this->productType->has_width) {
                $attributes[] = mb_strtolower(trans('admin.width'));
            }

            if ($this->productType->has_height) {
                $attributes[] = mb_strtolower(trans('admin.height'));
            }
        }

        foreach ($this->productType->fields as $customField) {
            $attributes[] = $customField->field_name;
        }

        $attributesMapped = [];
        foreach (array_keys($attributes) as $key) {
            $attributesMapped['*.' . $key] = $attributes[$key];
        }

        return $attributesMapped;
    }

    public function startRow(): int
    {
        return 2;
    }

    public function getRowsToSave(): array
    {
        return $this->rowsToSave;
    }

}
