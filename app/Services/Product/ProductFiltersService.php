<?php

namespace App\Services\Product;

use App\DataClasses\NumericFieldFilerTypesDataClass;
use App\DataClasses\ProductFieldTypeOptionsDataClass;
use App\DataClasses\ProductFilterFullPositionOptionsDataClass;
use App\DataClasses\ProductSizeTypesDataClass;
use App\DataClasses\ProductSortOptionsDataClass;
use App\Models\Brand;
use App\Models\Color;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductField;
use App\Models\ProductType;
use App\Services\Base\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ProductFiltersService extends BaseService
{
    private array $defaultOptions = ['sort_by', 'page', 'per_page'];

    public static function filterOptionChecked(array $filterData, string $filterNameSlug, string $filterOptionSlug): bool
    {
        return isset($filterData[$filterNameSlug]) &&
            (is_array($filterData[$filterNameSlug]) && in_array($filterOptionSlug, $filterData[$filterNameSlug]) ||
                $filterData[$filterNameSlug] === $filterOptionSlug);
    }

    public static function mainColorFilterOptionChecked(array $filterData, string $filterNameSlug, Color $color): bool
    {
        // TODO:: I have commented it because of a lot of requests to DB
        /*if (count($color->children)) {
            foreach ($color->children as $childColor) {
                return isset($filterData[$filterNameSlug]) &&
                    (is_array($filterData[$filterNameSlug]) && in_array($childColor->slug, $filterData[$filterNameSlug]) ||
                        $filterData[$filterNameSlug] === $childColor->slug);
            }
        }*/

        return isset($filterData[$filterNameSlug]) &&
            (is_array($filterData[$filterNameSlug]) && in_array($color->slug, $filterData[$filterNameSlug]) ||
                $filterData[$filterNameSlug] === $color->slug);
    }

    public function getOptionsByFilterData(
        ProductType $productType,
        array $filterData,
        Currency $baseCurrency,
        Collection $colors,
//        Collection $countries,
        Collection $brands,
    ): array
    {
        $options = [];
        $fields = $productType->fields;
        $sizeOptions = null;

        foreach ($filterData as $filterNameSlug => $filterValue) {
            $field = $fields->filter(function ($item) use ($filterNameSlug) {
                return $item->slug == $filterNameSlug ||
                    $item->slug == str_replace('_from', '', $filterNameSlug) ||
                    $item->slug == str_replace('_to', '', $filterNameSlug);
            })->first();


            if ($field) {
                if ($field->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION) {

                    if (!is_array($filterValue)) {
                        $filterValue = [$filterValue];
                    }

                    foreach ($filterValue as $value) {
                        $option = $field->options->where('slug', $value)->first();

                        if ($option) {
                            $options[] = [
                                'option_name' => $option->name,
                                'filter_slug' => $field->slug,
                                'option_slug' => $value,
                            ];
                        } else {
                            Log::error('CatalogService@getOptionsByFilterData: error: invalid field option slug: ' . $value);
                        }
                    }

                } elseif (($field->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_SIZE ||
                    $field->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION)
                ) {

                    if ($field->numeric_field_filter_type_id === NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_OPTIONS_TYPE) {
                        if (!is_array($filterValue)) {
                            $filterValue = [$filterValue];
                        }

                        foreach ($filterValue as $value) {
                            $filterOption = $field->fieldFilterOptions->where('slug', $value)->first();

                            if ($filterOption) {
                                $options[] = [
                                    'option_name' => $filterOption->name,
                                    'filter_slug' => $field->slug,
                                    'option_slug' => $value,
                                ];
                            } else {
                                Log::error('CatalogService@getOptionsByFilterData: error: invalid field filter option slug: ' . $value);
                            }
                        }
                    } elseif ($field->numeric_field_filter_type_id === NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_FROM_TO_INPUTS_TYPE) {
                        if ($filterNameSlug == $field->slug . '_from') {
                            $options[] = [
                                'option_name' => trans('base.from') . ': ' . $filterValue . ' ' . $field->field_size_name,
                                'filter_slug' => $filterNameSlug,
                                'option_slug' => $filterValue,
                            ];
                        }

                        if ($filterNameSlug == $field->slug . '_to') {
                            $options[] = [
                                'option_name' => trans('base.to') . ': ' . $filterValue . ' ' . $field->field_size_name,
                                'filter_slug' => $filterNameSlug,
                                'option_slug' => $filterValue,
                            ];
                        }
                    }
                }
            } else {
                if($filterNameSlug === 'color') {

                    if (!is_array($filterValue)) {
                        $filterValue = [$filterValue];
                    }

                    foreach ($filterValue as $value) {
                        $color = $colors->where('slug', $value)->first();

                        if ($color) {
                            $options[] = [
                                'option_name' => $color->name,
                                'filter_slug' => $filterNameSlug,
                                'option_slug' => $value,
                            ];
                        } else {
                            Log::error('CatalogService@getOptionsByFilterData: error: invalid color slug: ' . $value);
                        }
                    }
                } elseif ($filterNameSlug === 'country') {

                    if (!is_array($filterValue)) {
                        $filterValue = [$filterValue];
                    }

                    /*foreach ($filterValue as $value) {
                        $country = $countries->where('code', $value)->first();

                        if ($country) {
                            $options[] = [
                                'option_name' => $country->name,
                                'filter_slug' => $filterNameSlug,
                                'option_slug' => $value,
                            ];
                        } else {
                            Log::error('CatalogService@getOptionsByFilterData: error: invalid country slug: ' . $value);
                        }
                    }*/
                } elseif ($filterNameSlug === 'brand') {

                    if (!is_array($filterValue)) {
                        $filterValue = [$filterValue];
                    }

                    foreach ($filterValue as $value) {
                        $brand = $brands->where('slug', $value)->first();

                        if ($brand) {
                            $options[] = [
                                'option_name' => $brand->name,
                                'filter_slug' => $filterNameSlug,
                                'option_slug' => $value,
                            ];
                        } else {
                            Log::error('CatalogService@getOptionsByFilterData: error: invalid brand slug: ' . $value);
                        }
                    }
                } elseif ($filterNameSlug === 'price_from') {
                    $options[] = [
                        'option_name' => trans('base.from') . ': ' . $filterValue . ' ' . $baseCurrency->name_short,
                        'filter_slug' => $filterNameSlug,
                        'option_slug' => $filterValue,
                    ];
                } elseif ($filterNameSlug === 'price_to') {
                    $options[] = [
                        'option_name' => trans('base.to') . ': ' . $filterValue . ' ' . $baseCurrency->name_short,
                        'filter_slug' => $filterNameSlug,
                        'option_slug' => $filterValue,
                    ];

                    //SIZE

                    //dynamic size filters
                } elseif ($filterNameSlug === 'product_length_from') {
                    $options[] = [
                        'option_name' => trans('base.from') . ': ' . $filterValue . ' ' . $productType->size_points,
                        'filter_slug' => $filterNameSlug,
                        'option_slug' => $filterValue,
                    ];
                } elseif ($filterNameSlug === 'product_length_to') {
                    $options[] = [
                        'option_name' => trans('base.to') . ': ' . $filterValue . ' ' . $productType->size_points,
                        'filter_slug' => $filterNameSlug,
                        'option_slug' => $filterValue,
                    ];
                } elseif ($filterNameSlug === 'product_width_from') {
                    $options[] = [
                        'option_name' => trans('base.from') . ': ' . $filterValue . ' ' . $productType->size_points,
                        'filter_slug' => $filterNameSlug,
                        'option_slug' => $filterValue,
                    ];
                } elseif ($filterNameSlug === 'product_width_to') {
                    $options[] = [
                        'option_name' => trans('base.to') . ': ' . $filterValue . ' ' . $productType->size_points,
                        'filter_slug' => $filterNameSlug,
                        'option_slug' => $filterValue,
                    ];
                } elseif ($filterNameSlug === 'product_height_from') {
                    $options[] = [
                        'option_name' => trans('base.from') . ': ' . $filterValue . ' ' . $productType->size_points,
                        'filter_slug' => $filterNameSlug,
                        'option_slug' => $filterValue,
                    ];
                } elseif ($filterNameSlug === 'product_height_to') {
                    $options[] = [
                        'option_name' => trans('base.to') . ': ' . $filterValue . ' ' . $productType->size_points,
                        'filter_slug' => $filterNameSlug,
                        'option_slug' => $filterValue,
                    ];
                    //fixes size options
                } elseif ($filterNameSlug === 'product_length') {
                    $filterValue = is_array($filterValue) ? $filterValue : [$filterValue];

                    if (!$sizeOptions) {
                        $sizeOptions = $productType->sizeFilterOptions;
                    }

                    foreach ($filterValue as $value) {

                        $sizeOption = $sizeOptions
                            ->where('type', ProductSizeTypesDataClass::LENGTH)
                            ->where('slug', $value)
                            ->first();

                        if ($sizeOption) {
                            $options[] = [
                                'option_name' => $sizeOption->name,
                                'filter_slug' => $filterNameSlug,
                                'option_slug' => $value,
                            ];
                        } else {
                            Log::error('CatalogService@getOptionsByFilterData: error: invalid brand slug: ' . $value);
                        }
                    }
                } elseif ($filterNameSlug === 'product_width') {
                    $filterValue = is_array($filterValue) ? $filterValue : [$filterValue];

                    if (!$sizeOptions) {
                        $sizeOptions = $productType->sizeFilterOptions;
                    }

                    foreach ($filterValue as $value) {
                        $sizeOption = $sizeOptions
                            ->where('type', ProductSizeTypesDataClass::WIDTH)
                            ->where('slug', $value)
                            ->first();

                        if ($sizeOption) {
                            $options[] = [
                                'option_name' => $sizeOption->name,
                                'filter_slug' => $filterNameSlug,
                                'option_slug' => $value,
                            ];
                        } else {
                            Log::error('CatalogService@getOptionsByFilterData: error: invalid brand slug: ' . $value);
                        }
                    }
                } elseif ($filterNameSlug === 'product_height') {
                    $filterValue = is_array($filterValue) ? $filterValue : [$filterValue];

                    if (!$sizeOptions) {
                        $sizeOptions = $productType->sizeFilterOptions;
                    }

                    foreach ($filterValue as $value) {
                        $sizeOption = $sizeOptions
                            ->where('type', ProductSizeTypesDataClass::HEIGHT)
                            ->where('slug', $value)
                            ->first();

                        if ($sizeOption) {
                            $options[] = [
                                'option_name' => $sizeOption->name,
                                'filter_slug' => $filterNameSlug,
                                'option_slug' => $value,
                            ];
                        } else {
                            Log::error('CatalogService@getOptionsByFilterData: error: invalid brand slug: ' . $value);
                        }
                    }
                } elseif ($filterNameSlug === 'search') {
                    $options[] = [
                        'option_name' => $filterValue,
                        'filter_slug' => $filterNameSlug,
                        'option_slug' => $filterValue,
                    ];
                } else {
                    if (!in_array($filterNameSlug, $this->defaultOptions)) {
                        Log::error('CatalogService@getOptionsByFilterData: error: invalid filter slug: ' . $filterNameSlug);
                    }
                }
            }
        }

        return $options;
    }

    public function handleAllProductFilters(array $filterData, Builder $query, bool $disableSorting = false, array $allFilters): Builder
    {
        $sizeOptions = null;

        foreach ($filterData as $filterNameSlug => $filterValue) {
            if ($filterNameSlug === 'color') {

                if (!is_array($filterValue)) {
                    $filterValue = [$filterValue];
                }

//                $colors = Color::with(['children'])->whereIn('slug', $filterValue)->get();
                $colors = Color::whereIn('slug', $filterValue)->get();

                $colorsToFilter = $colors->pluck('id');

                /*foreach ($colors as $color) {
                    if (count($color->children)) {
                        foreach ($color->children as $childColor) {
                            $colorsToFilter[] = $childColor->id;
                        }
                    }
                }*/

                $query->whereHas('colors', function ($query) use ($colorsToFilter) {
                    $query->whereIn('color_id', $colorsToFilter);
                });
            } else if ($filterNameSlug === 'country') {
                if (!is_array($filterValue)) {
                    $filterValue = [$filterValue];
                }

                $countries = Country::whereIn('code', $filterValue)->get();
                $query->where(function (Builder $query) use($countries) {
                    $query->whereIn('country_id', $countries->pluck('id'));
                });
            } else if ($filterNameSlug === 'brand') {

                if (!is_array($filterValue)) {
                    $filterValue = [$filterValue];
                }

                $brands = Brand::whereIn('slug', $filterValue)->get();

                $query->where(function (Builder $query) use($brands) {
                    $query->whereIn('brand_id', $brands->pluck('id'));
                });
            } else if ($filterNameSlug === 'price_from') {
                $query->where('price', '>=', floatval($filterValue));
            } else if ($filterNameSlug === 'price_to') {
                $query->where('price', '<=', floatval($filterValue));
            } else if($filterNameSlug === 'search') {
                $query->where(function (Builder $query) use ($filterValue) {
                    $query->whereRaw('UPPER(`name`) LIKE \'%' . mb_strtoupper($filterValue) . '%\'')
                        ->orWhereRaw('UPPER(`sku`) LIKE \'%' . mb_strtoupper($filterValue) . '%\'');
                });
                // dynamic inputs
            } else if ($filterNameSlug === 'product_length_from') {
                $query->where('length', '>=', $filterValue);
            } else if ($filterNameSlug === 'product_length_to') {
                $query->where('length', '<=', $filterValue);
            } else if ($filterNameSlug === 'product_width_from') {
                $query->where('width', '>=', $filterValue);
            } else if ($filterNameSlug === 'product_width_to') {
                $query->where('width', '<=', $filterValue);
            } else if ($filterNameSlug === 'product_height_from') {
                $query->where('height', '>=', $filterValue);
            } else if ($filterNameSlug === 'product_height_to') {
                $query->where('height', '<=', $filterValue);
                //fixed inputs

            } else {

                if (!isset($allFilters['main'])) {
                    $allFilters['main'] = collect();
                }

                $field = $allFilters['main']->filter(function ($item) use ($filterNameSlug) {
                    return $item->slug == $filterNameSlug ||
                        $item->slug == str_replace('_from', '', $filterNameSlug) ||
                        $item->slug == str_replace('_to', '', $filterNameSlug);
                })->first();


                if ($field) {

                    if ($field->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION) {
                        if (!is_array($filterValue)) {
                            $filterValue = [$filterValue];
                        }

                        $options = $field
                            ->options()
                            ->whereIn('slug', $filterValue)
                            ->get();

                        if (count($options)) {

                            if ($field->is_multiselectable) {
                                $query->where(function (Builder $query) use($options, $field) {
                                    foreach ($options as $option) {
                                        $query->orWhereRaw('CAST(JSON_EXTRACT(custom_fields, ?) AS UNSIGNED) = CAST(? AS UNSIGNED)')
                                            ->addBinding('$."' . $field->id . '"')
                                            ->addBinding((string)$option->id);
                                    }
                                });
                            } else {

                                $query->where(function (Builder $query) use ($options, $field) {
                                    foreach ($options as $option) {
                                        $query->orWhere(function (Builder $query) use ($field, $option) {
                                            $query->whereJsonContains('custom_fields->'.$field->id, (string) $option->id);
                                        });
                                    }
                                });
                            }
                        }
                    } elseif ($field->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_SIZE ||
                        $field->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_NUMBER
                    ) {
                        if ($field->numeric_field_filter_type_id === NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_FROM_TO_INPUTS_TYPE) {

                            if ($filterNameSlug == $field->slug . '_from') {
                                $query->whereRaw('CAST(JSON_EXTRACT(custom_fields, ?) AS DECIMAL(2)) >= ?')
                                    ->addBinding('$."' . $field->id . '"')
                                    ->addBinding(doubleval($filterValue));

                            }

                            if ($filterNameSlug == $field->slug . '_to') {
                                $query->whereRaw('CAST(JSON_EXTRACT(custom_fields, ?) AS DECIMAL(2)) <= ?')
                                    ->addBinding('$."' . $field->id . '"')
                                    ->addBinding(intval($filterValue));
                            }

                        } elseif ($field->numeric_field_filter_type_id === NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_OPTIONS_TYPE) {

                            if (!is_array($filterValue)) {
                                $filterValue = [$filterValue];
                            }

                            $filterOptions = $field->fieldFilterOptions->filter(fn($filterOption) => in_array($filterOption->slug, $filterValue));

                            $query->where(function (Builder $query) use($filterOptions, $field) {
                                foreach ($filterOptions as $filterOption) {
                                    $query->orWhere(function (Builder $query) use($field, $filterOption) {
                                        $query
                                            ->whereRaw('CAST(JSON_EXTRACT(custom_fields, ?) AS DECIMAL(2)) >= ?')
                                            ->addBinding('$."' . $field->id . '"')
                                            ->addBinding($filterOption->from)
                                            ->whereRaw('CAST(JSON_EXTRACT(custom_fields, ?) AS DECIMAL(2)) <= ?')
                                            ->addBinding('$."' . $field->id . '"')
                                            ->addBinding($filterOption->to);
                                    });
                                }
                            });
                        }
                    }
                } else {
                    if (!in_array($filterNameSlug, $this->defaultOptions)) {
                        Log::error('CatalogService@handleProductFilters: error: invalid filter slug: ' . $filterNameSlug);
                    }
                }
            }
        }


        if (!$disableSorting) {
            $query = $this->handleSortingFilter($query, $filterData);
        }

        return  $query;
    }

    public function handleProductFilters(ProductType $productType, array $filterData, Builder $query, bool $disableSorting = false): Builder
    {
        $sizeOptions = null;

        foreach ($filterData as $filterNameSlug => $filterValue) {
            if ($filterNameSlug === 'color') {

                if (!is_array($filterValue)) {
                    $filterValue = [$filterValue];
                }

//                $colors = Color::with(['children'])->whereIn('slug', $filterValue)->get();
                $colors = Color::whereIn('slug', $filterValue)->get();

                $colorsToFilter = $colors->pluck('id');

                /*foreach ($colors as $color) {
                    if (count($color->children)) {
                        foreach ($color->children as $childColor) {
                            $colorsToFilter[] = $childColor->id;
                        }
                    }
                }*/

                // TODO:: remove when finish
                /*$query->where(function (Builder $query) use($colorsToFilter) {
//                    dd('test?', $colorsToFilter);
                    $query->whereIn('main_color_id', [7]);
//                    $query->whereIn('main_color_id', $colorsToFilter);
                });*/

                $query->whereHas('colors', function ($query) use ($colorsToFilter) {
                    $query->whereIn('color_id', $colorsToFilter);
                });
            } else if ($filterNameSlug === 'country') {
                if (!is_array($filterValue)) {
                    $filterValue = [$filterValue];
                }

                $countries = Country::whereIn('code', $filterValue)->get();
                $query->where(function (Builder $query) use($countries) {
                    $query->whereIn('country_id', $countries->pluck('id'));
                });
            } else if ($filterNameSlug === 'brand') {

                if (!is_array($filterValue)) {
                    $filterValue = [$filterValue];
                }

                $brands = Brand::whereIn('slug', $filterValue)->get();

                $query->where(function (Builder $query) use($brands) {
                    $query->whereIn('brand_id', $brands->pluck('id'));
                });
            } else if ($filterNameSlug === 'price_from') {
                $query->where('price', '>=', floatval($filterValue));
            } else if ($filterNameSlug === 'price_to') {
                $query->where('price', '<=', floatval($filterValue));
            } else if($filterNameSlug === 'search') {
                $query->where(function (Builder $query) use ($filterValue) {
                    $query->whereRaw('UPPER(`name`) LIKE \'%' . mb_strtoupper($filterValue) . '%\'')
                        ->orWhereRaw('UPPER(`sku`) LIKE \'%' . mb_strtoupper($filterValue) . '%\'');
                });
                // dynamic inputs
            } else if ($filterNameSlug === 'product_length_from') {
                $query->where('length', '>=', $filterValue);
            } else if ($filterNameSlug === 'product_length_to') {
                $query->where('length', '<=', $filterValue);
            } else if ($filterNameSlug === 'product_width_from') {
                $query->where('width', '>=', $filterValue);
            } else if ($filterNameSlug === 'product_width_to') {
                $query->where('width', '<=', $filterValue);
            } else if ($filterNameSlug === 'product_height_from') {
                $query->where('height', '>=', $filterValue);
            } else if ($filterNameSlug === 'product_height_to') {
                $query->where('height', '<=', $filterValue);
                //fixed inputs
            } else if ($filterNameSlug === 'product_length') {
                $filterValue = is_array($filterValue) ? $filterValue : [$filterValue];

                if (!$sizeOptions) {
                    $sizeOptions = $productType->sizeFilterOptions;
                }

                $query->where(function (Builder $query) use ($filterValue, $sizeOptions) {
                    foreach ($filterValue as $value) {
                        $sizeOption = $sizeOptions
                            ->where('type', ProductSizeTypesDataClass::LENGTH)
                            ->where('slug', $value)
                            ->first();

                        if ($sizeOption) {
                            $query->orWhere(function (Builder $query) use($sizeOption) {
                                $query->where('length', '>=', $sizeOption->from)
                                    ->where('length', '<=', $sizeOption->to);
                            });
                        } else {
                            Log::error('CatalogService@handleProductFilters: error: invalid size slug: ' . $value);
                        }
                    }
                });
            } else if ($filterNameSlug === 'product_width') {
                $filterValue = is_array($filterValue) ? $filterValue : [$filterValue];

                if (!$sizeOptions) {
                    $sizeOptions = $productType->sizeFilterOptions;
                }

                $query->where(function (Builder $query) use ($filterValue, $sizeOptions) {
                    foreach ($filterValue as $value) {
                        $sizeOption = $sizeOptions
                            ->where('type', ProductSizeTypesDataClass::WIDTH)
                            ->where('slug', $value)
                            ->first();

                        if ($sizeOption) {
                            $query->orWhere(function (Builder $query) use($sizeOption) {
                                $query->where('width', '>=', $sizeOption->from)
                                    ->where('width', '<=', $sizeOption->to);
                            });
                        } else {
                            Log::error('CatalogService@handleProductFilters: error: invalid size slug: ' . $value);
                        }
                    }
                });
            } else if ($filterNameSlug === 'product_height') {
                $filterValue = is_array($filterValue) ? $filterValue : [$filterValue];

                if (!$sizeOptions) {
                    $sizeOptions = $productType->sizeFilterOptions;
                }

                $query->where(function (Builder $query) use ($filterValue, $sizeOptions) {
                    foreach ($filterValue as $value) {
                        $sizeOption = $sizeOptions
                            ->where('type', ProductSizeTypesDataClass::HEIGHT)
                            ->where('slug', $value)
                            ->first();

                        if ($sizeOption) {
                            $query->orWhere(function (Builder $query) use($sizeOption) {
                                $query->where('height', '>=', $sizeOption->from)
                                    ->where('height', '<=', $sizeOption->to);
                            });
                        } else {
                            Log::error('CatalogService@handleProductFilters: error: invalid size slug: ' . $value);
                        }
                    }
                });
            } else {

                $field = $productType->fields->filter(function ($item) use ($filterNameSlug) {
                    return $item->slug == $filterNameSlug ||
                        $item->slug == str_replace('_from', '', $filterNameSlug) ||
                        $item->slug == str_replace('_to', '', $filterNameSlug);
                })->first();



                if ($field) {


                    if ($field->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION) {

                        if (!is_array($filterValue)) {
                            $filterValue = [$filterValue];
                        }

//                        dd($filterValue);
                        $options = $field
                            ->options()
                            ->whereIn('slug', $filterValue)
                            ->get();


                        if (count($options)) {


                            if ($field->is_multiselectable) {
                                $query->where(function (Builder $query) use($options, $field) {
                                    foreach ($options as $option) {
                                        $query->orWhereRaw('CAST(JSON_EXTRACT(custom_fields, ?) AS UNSIGNED) = CAST(? AS UNSIGNED)')
                                            ->addBinding('$."' . $field->id . '"')
                                            ->addBinding((string)$option->id);
                                    }
                                });
                            } else {

                                // TODO:: old request
                                /*$query->where(function (Builder $query) use($options, $field) {
                                    foreach ($options as $option) {
                                        $query->orWhereRaw('CAST(JSON_EXTRACT(custom_fields, ?) AS UNSIGNED) = CAST(? AS UNSIGNED)')
                                            ->addBinding('$."' . $field->id . '"')
                                            ->addBinding((integer)$option->id);
                                    }
                                });*/


                                $query->where(function (Builder $query) use ($options, $field) {
                                    foreach ($options as $option) {
                                        $query->orWhere(function (Builder $query) use ($field, $option) {
                                            $query->whereJsonContains('custom_fields->'.$field->id, (string) $option->id);
                                        });
                                    }
                                });


                            }
                        }
                    } elseif ($field->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_SIZE ||
                        $field->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_NUMBER
                    ) {
                        if ($field->numeric_field_filter_type_id === NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_FROM_TO_INPUTS_TYPE) {

                            if ($filterNameSlug == $field->slug . '_from') {
                                $query->whereRaw('CAST(JSON_EXTRACT(custom_fields, ?) AS DECIMAL(2)) >= ?')
                                    ->addBinding('$."' . $field->id . '"')
                                    ->addBinding(doubleval($filterValue));

                            }

                            if ($filterNameSlug == $field->slug . '_to') {
                                $query->whereRaw('CAST(JSON_EXTRACT(custom_fields, ?) AS DECIMAL(2)) <= ?')
                                    ->addBinding('$."' . $field->id . '"')
                                    ->addBinding(intval($filterValue));
                            }

                        } elseif ($field->numeric_field_filter_type_id === NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_OPTIONS_TYPE) {

                            if (!is_array($filterValue)) {
                                $filterValue = [$filterValue];
                            }

                            $filterOptions = $field->fieldFilterOptions->filter(fn($filterOption) => in_array($filterOption->slug, $filterValue));

                            $query->where(function (Builder $query) use($filterOptions, $field) {
                                foreach ($filterOptions as $filterOption) {
                                    $query->orWhere(function (Builder $query) use($field, $filterOption) {
                                        $query
                                            ->whereRaw('CAST(JSON_EXTRACT(custom_fields, ?) AS DECIMAL(2)) >= ?')
                                            ->addBinding('$."' . $field->id . '"')
                                            ->addBinding($filterOption->from)
                                            ->whereRaw('CAST(JSON_EXTRACT(custom_fields, ?) AS DECIMAL(2)) <= ?')
                                            ->addBinding('$."' . $field->id . '"')
                                            ->addBinding($filterOption->to);
                                    });
                                }
                            });
                        }
                    }
                } else {
                    if (!in_array($filterNameSlug, $this->defaultOptions)) {
                        Log::error('CatalogService@handleProductFilters: error: invalid filter slug: ' . $filterNameSlug);
                    }
                }
            }
        }


        if (!$disableSorting) {
            $query = $this->handleSortingFilter($query, $filterData);
        }

        return  $query;
    }

    public function handleSortingFilter(Builder $query, array $filterData): Builder
    {
        if (isset($filterData['sort_by'])) {
            if ($filterData['sort_by'] === ProductSortOptionsDataClass::SORT_BY_POPULARITY) {
                $query = $this->handleSortByPopularity($query);
            } elseif ($filterData['sort_by'] === ProductSortOptionsDataClass::SORT_BY_NEW) {
                $query->orderByDesc('created_at');
            } elseif ($filterData['sort_by'] === ProductSortOptionsDataClass::SORT_BY_PRICE_FROM_LOW) {
                $query->orderBy('price');
            } elseif ($filterData['sort_by'] === ProductSortOptionsDataClass::SORT_BY_PRICE_FROM_HIGH) {
                $query->orderByDesc('price');
            }
        } else {
            $query = $this->handleSortByPopularity($query);
        }

        return $query;
    }

    public function handleSortByPopularity(Builder $query): Builder
    {
        $query->orderByDesc('orders_count')->orderBy('id');

        return $query;
    }

    public function getFiltersByProductType(ProductType $productType): array
    {
        $mainFilters = collect();
        $fullFilters = [
            'left' => collect(),
            'middle' => collect(),
            'right' => collect(),
        ];

        foreach ($productType->fields as $filed) {
            if ($filed->pivot->show_as_filter) {
                if ($filed->pivot->filter_full_position_id === ProductFilterFullPositionOptionsDataClass::FILTER_POSITION_LEFT) {
                    $fullFilters['left'][] = $filed;
                }
                /*if ($filed->pivot->filter_full_position_id === ProductFilterFullPositionOptionsDataClass::FILTER_POSITION_MIDDLE) {);
                    $fullFilters['middle'][] = $filed;
                }*/
            }



            if ($filed->pivot->show_as_filter && $filed->pivot->show_on_main_filters_list) {
                $mainFilters[] = $filed;
            }
        }

        return [
            'main' => $mainFilters,
            'full' => $fullFilters,
        ];
    }

    public function getAllFilters(): array
    {
        /*$mainFilters = collect();
        $productTypes = ProductType::all();
        $addedFieldIds = [];

        foreach ($productTypes as $productType) {
            foreach ($productType->fields as $filed) {

                if ($filed->pivot->show_as_filter && $filed->pivot->show_on_main_filters_list) {
                    if (!in_array($filed->id, $addedFieldIds)) {
                        $mainFilters[] = $filed;
                        $addedFieldIds[] = $filed->id;
                    }
                }
            }
        }*/

        $mainFilters = collect();
        $addedFieldIds = [];

        // Жадная загрузка связей 'fields' и 'pivot' сразу для всех типов продуктов
        $productTypes = ProductType::with(['fields' => function($query) {
            $query->wherePivot('show_as_filter', true)
                ->wherePivot('show_on_main_filters_list', true);
        }])->get();

        foreach ($productTypes as $productType) {
            foreach ($productType->fields as $field) {
                if (!in_array($field->id, $addedFieldIds)) {
                    $mainFilters->push($field);
                    $addedFieldIds[] = $field->id;
                }
            }
        }

        return [
            'main' => $mainFilters,
        ];
    }
}
