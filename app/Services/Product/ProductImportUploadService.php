<?php

namespace App\Services\Product;

use App\DataClasses\ProductFieldTypeOptionsDataClass;
use App\DataClasses\ProductSpecialOfferOptionsDataClass;
use App\DataClasses\ProductStatusDataClass;
use App\Excel\Exports\ProductImportExampleExcelExport;
use App\Excel\Imports\NumberOfRowsImport;
use App\Excel\Imports\ProductImportImport;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Country;
use App\Models\Currency;
use App\Models\ImportedProduct;
use App\Models\ProductFieldOption;
use App\Models\ProductType;
use App\Services\Application\ApplicationConfigService;
use App\Services\Base\BaseService;
use App\Services\Product\DTO\UploadProductsImportFileDTO;
use Illuminate\Support\Collection;
use Laravel\Telescope\Telescope;
use Maatwebsite\Excel\Facades\Excel;

class ProductImportUploadService extends BaseService
{
    private ?Collection $currencies;
    private ?Collection $countries;
    private ?Collection $brands;
    private ?Collection $collections;
    private ?Collection $categories;
    private ?Collection $colors;
    private ?Collection $productFieldOptions = null;

    public function __construct(
        private readonly ApplicationConfigService $applicationService
    ) {
    }

    public function importProductsFromFile(ProductType $productType, UploadProductsImportFileDTO $request): array
    {
        return $this->coverWithDBTransactionWithoutResponse(function () use($productType, $request) {
            $linesReader = new NumberOfRowsImport();
            $linesReader->import($request->file);

            //header line + 5000 lines
            if ($linesReader->getNumberOfRows() > 2001) {
                return [
                    'isSuccess' => false,
                    'singleError' => trans('admin.products_import_file_for_import_explanation'),
                    'errorsByRow' => null,
                    'allErrorsShowed' => true,
                ];
            }

            Telescope::stopRecording();
            $this->currencies = Currency::select(['code', 'id'])->get();
            $this->countries = Country::select(['name', 'id'])->get();
            $this->brands = Brand::select(['name', 'id'])->get();
            $this->categories = Category::select(['name', 'id'])->get();
            $this->colors = Color::select(['name', 'id'])->get();
            $this->collections = \App\Models\Collection::select(['name', 'brand_id', 'id'])->get();

            //load custom fields options
            $productFieldsId = $productType->fields->pluck('id');

            $this->productFieldOptions = ProductFieldOption::select(['name', 'id'])
                ->whereIn('product_field_id', $productFieldsId)
                ->get();

            //cover with transaction
            $import = new ProductImportImport(
                $productType,
                $this->applicationService,
                $this->countries,
                $this->categories,
                $this->brands,
                $this->currencies,
                $this->collections,
                $this->colors,
                $this->productFieldOptions,
            );
            $import->import($request->file);

            $errorsByRow = [];
            $allErrorsShowed = true;

            foreach ($import->failures() as $failure) {
                if (count($errorsByRow) >= 50) {
                    $allErrorsShowed = false;
                    break;
                }

                if (!(isset($errorsByRow[$failure->row()]) && is_array($errorsByRow[$failure->row()]))) {
                    $errorsByRow[$failure->row()] = $failure->errors();
                } else {
                    $errorsByRow[$failure->row()] = array_merge($errorsByRow[$failure->row()], $failure->errors());
                }
            }

            if (!count($import->failures())) {
                $rowsToSave = $import->getRowsToSave();

                $duplicatedSkuErrorsList = $this->checkSkuDuplicatesInList($rowsToSave);
                if (count($duplicatedSkuErrorsList['errorsByRow'])) {
                    return [
                        'isSuccess' => false,
                        'singleError' => null,
                        'errorsByRow' => $duplicatedSkuErrorsList['errorsByRow'],
                        'allErrorsShowed' => $duplicatedSkuErrorsList['allErrorsShowed'],
                    ];
                }

                $parenSkuErrorsList = $this->checkParentSkuInList($rowsToSave);
                if (count($parenSkuErrorsList['errorsByRow'])) {
                    return [
                        'isSuccess' => false,
                        'singleError' => null,
                        'errorsByRow' => $parenSkuErrorsList['errorsByRow'],
                        'allErrorsShowed' => $parenSkuErrorsList['allErrorsShowed'],
                    ];
                }

                $this->saveImportedProducts($productType, $rowsToSave);

            }

            Telescope::startRecording();

            return [
                'isSuccess' => !count($import->failures()) > 0,
                'singleError' => null,
                'errorsByRow' => $errorsByRow,
                'allErrorsShowed' => $allErrorsShowed,
            ];
        });
    }

    private function checkSkuDuplicatesInList(array $rowsToSave): array
    {
        $errors = [];
        $allErrorsShowed = true;
        $skuList = (array_column($rowsToSave, 1));
        foreach ($skuList as $index => $sku) {
            if(count($errors) > 50) {
                $allErrorsShowed = false;
                break;
            }

            if (count(array_keys($skuList, $sku)) > 1) {
                $errors[$index+2] = [trans('admin.products_import_duplicated_sku', ['SKU' => $sku])];
            }
        }

        return [
            'allErrorsShowed' => $allErrorsShowed,
            'errorsByRow' => $errors,
        ];
    }

    private function checkParentSkuInList(array $rowsToSave): array
    {
        $errors = [];
        $allErrorsShowed = true;
        $skuList = (array_column($rowsToSave, 1));
        $parentSkuList = (array_column($rowsToSave, 0));
        foreach ($parentSkuList as $index => $parentSku) {
            if(count($errors) > 50) {
                $allErrorsShowed = false;
                break;
            }

            if ($parentSku !== null && $parentSku !== '' && !in_array($parentSku, $skuList)) {
                $errors[$index+2] = [trans('admin.products_import_parent_sku_incorrect', ['SKU' => $parentSku])];
            }
        }

        return [
            'allErrorsShowed' => $allErrorsShowed,
            'errorsByRow' => $errors,
        ];
    }

    private function saveImportedProducts(ProductType $productType, array $rows): void
    {
            $parentProductsSKUMapping = [];
            //parent products
            foreach (array_filter($rows, fn($element) => $element[0] === null || $element[0] === '') as $row) {
                $parentRow = $this->parseProductRow($productType, $row);
                $importedProduct = ImportedProduct::create($this->prepareProductDataByRow($productType, $parentRow));
                $this->syncCategories($importedProduct->id, $parentRow['categories']);
                $this->syncColors($importedProduct->id, $parentRow['all_color_ids']);
                $parentProductsSKUMapping[$importedProduct->sku] = $importedProduct->id;

                foreach (array_filter($rows, fn($element) => $element[0] == $importedProduct->sku) as $chRow) {
                    $childRow = $this->parseProductRow($productType, $chRow);
                    $preparedChildRow = $this->prepareProductDataByRow($productType, $childRow);
                    $preparedChildRow['parent_product_id'] = $parentProductsSKUMapping[$childRow['parent_sku']];
                    $importedProduct = ImportedProduct::create($preparedChildRow);
                    $this->syncCategories($importedProduct->id, $childRow['categories']);
                    $this->syncColors($importedProduct->id, $childRow['all_color_ids']);
                }
            }
    }

    private function syncCategories(int $importedProductId, array $categories): void
    {
        $categoriesToInsert = [];
        foreach ($categories as $categoryId) {
            $categoriesToInsert[] = [
                'imported_product_id' => $importedProductId,
                'category_id' => $categoryId,
            ];
        }

        \DB::table('imported_product_categories')->insert($categoriesToInsert);
    }

    private function syncColors(int $importedProductId, array $colors): void
    {
        $colorsToInsert = [];
        foreach ($colors as $colorId) {
            $colorsToInsert[] = [
                'imported_product_id' => $importedProductId,
                'color_id' => $colorId,
            ];
        }

        \DB::table('imported_product_colors')->insert($colorsToInsert);
    }

    private function prepareProductDataByRow(ProductType $productType, array $row): array
    {
        return [
            'product_type_id' => $productType->id,
            'slug' => \Str::slug($row['name'][config('app.locale')] . ' ' .  $row['sku']),
            'sku' => $row['sku'],
            'name' => $row['name'],
            'meta_title' => $row['meta_title'],
            'meta_description' => $row['meta_description'],
            'meta_keywords' => $row['meta_keywords'],
            'availability_status_id' => $row['availability_status_id'],
            'special_offers' => $row['special_offers'],
            'price_in_currency' => $row['price_in_currency'],
            'purchase_price_in_currency' => $row['purchase_price_in_currency'],
            'old_price_in_currency' => $row['old_price_in_currency'],
            'price_currency_id' => $row['price_currency_id'],
            'country_id' => $row['country_id'],
            'brand_id' => $row['brand_id'] ?? null,
            'collection_id' => $row['collection_id'] ?? null,
            'main_color_id' => $row['main_color_id'] ?? null,
            'length' => $row['length'] ?? null,
            'width' => $row['width'] ?? null,
            'height' => $row['height'] ?? null,
            'custom_fields' => $row['custom_fields'] ?? [],
        ];
    }

    private function parseProductRow(ProductType $productType, array $row): array
    {
        $parsers = $this->getParsers($productType, $row);

        $parsedRow = [];
        foreach ($row as $position => $value) {
            if (isset($parsers[$position])) {
                $result = $parsers[$position]($value);
                if (isset($parsedRow[array_keys($result)[0]]) && is_array($parsedRow[array_keys($result)[0]])) {
                    //array_merger changes keys here :(
                    $mergedArray = [];
                    foreach ($parsedRow[array_keys($result)[0]] as $rowKey => $rowValue) {
                        $mergedArray[$rowKey] = $rowValue;
                    }

                    foreach ($result[array_keys($result)[0]] as $rowKey => $rowValue) {
                        $mergedArray[$rowKey] = $rowValue;
                    }

                    $parsedRow[array_keys($result)[0]] = $mergedArray;
                } else {
                    $parsedRow = array_merge($parsedRow, $parsers[$position]($value));
                }
            }
        }

        return $parsedRow;
    }

    private function getParsers(ProductType $productType, array $row): array
    {
        $parsers = [];

        $parsers[] = function ($data) {
            return ['parent_sku' => $data];
        };

        $parsers[] = function ($data) {
             return ['sku' => $data];
        };

        foreach ($this->applicationService->getAvailableLanguages() as $availableLanguage) {
            $parsers[] = function ($data) use ($availableLanguage) {
                return ['name' => [$availableLanguage => $data]];
            };
        }

        foreach ($this->applicationService->getAvailableLanguages() as $availableLanguage) {
            $parsers[] = function ($data) use ($availableLanguage) {
                return ['meta_title' => [$availableLanguage => $data]];
            };
        }

        foreach ($this->applicationService->getAvailableLanguages() as $availableLanguage) {
            $parsers[] = function ($data) use ($availableLanguage) {
                return ['meta_description' => [$availableLanguage => $data]];
            };
        }

        foreach ($this->applicationService->getAvailableLanguages() as $availableLanguage) {
            $parsers[] = function ($data) use ($availableLanguage) {
                return ['meta_keywords' => [$availableLanguage => $data]];
            };
        }

        $parsers[] = function ($data) {
            $statusesInAllLanguages = collect([]);
            $transKeys = ProductStatusDataClass::get()->pluck('trans_key', 'id');

            foreach (app()->make(ApplicationConfigService::class)->getAvailableLanguages() as $availableLanguage) {
                foreach ($transKeys as $id => $transKey) {
                    $statusesInAllLanguages->push(['name' => trans($transKey, [], $availableLanguage), 'id' => $id]);
                }
            }

            return ['availability_status_id' => $statusesInAllLanguages->where('name', $data)->first()['id']];
        };

        $parsers[] = function ($data) {
            $parsedData = [];
            if ($data) {
                $value = str_replace(', ', ',', $data);
                $values = explode(',', $value);

                foreach ($values as $parsedValue) {
                    $parsedData[] = ProductSpecialOfferOptionsDataClass::get()->where('name', $parsedValue)->first()['id'];
                }
            }

            return ['special_offers' => $parsedData];
        };

        $parsers[] = function ($data) {
            return ['price_in_currency' => floatval($data)];
        };

        $parsers[] = function ($data) {
            return ['purchase_price_in_currency' => floatval($data)];
        };

        $parsers[] = function ($data) {
            return ['old_price_in_currency' => floatval($data)];
        };

        $parsers[] = function ($data) {
            return ['price_currency_id' => $this->currencies->where('code', $data)->first()['id']];
        };

        $parsers[] = function ($data) {
            $country = $this->countries->filter(function ($country) use($data) {
                return in_array($data, $country->getTranslations('name'));
            })->first();

            return ['country_id' => $country->id];
        };

        if ($productType->has_brand) {
            $parsers[] = function ($data) {
                $brand = $this->brands->filter(function ($brand) use($data) {
                    return in_array($data, $brand->getTranslations('name'));
                })->first();

                return ['brand_id' => $brand->id];
            };
        }

        if ($productType->has_collection) {
            $brandPosition = count($parsers) - 1;
            $brandId = $parsers[$brandPosition]($row[$brandPosition])['brand_id'];

            $parsers[] = function ($data) use($brandId) {
                $collection = $this->collections->filter(function ($collection) use($data, $brandId) {
                    return in_array($data, $collection->getTranslations('name')) &&
                        $collection->brand_id === $brandId;
                })->first();

                return ['collection_id' => $collection->id];
            };
        }

        if ($productType->has_category) {
            $parsers[] = function ($data) {
                $parsedData = [];
                if ($data) {
                    $value = str_replace(', ', ',', $data);
                    $values = explode(',', $value);

                    foreach ($values as $parsedValue) {
                        $parsedData[] = $this->categories->filter(function ($category) use($parsedValue) {
                            return in_array($parsedValue, $category->getTranslations('name'));
                        })->first()['id'];
                    }
                }

                return ['categories' => $parsedData];
            };
        }

        if ($productType->has_color) {
            $parsers[] = function ($data) {
                $color = $this->colors->filter(function ($color) use($data) {
                    return in_array($data, $color->getTranslations('name'));
                })->first();

                return ['main_color_id' => $color->id];
            };

            $parsers[] = function ($data) {
                $parsedData = [];
                if ($data) {
                    $value = str_replace(', ', ',', $data);
                    $values = explode(',', $value);

                    foreach ($values as $parsedValue) {
                        $parsedData[] = $this->colors->filter(function ($color) use($parsedValue) {
                            return in_array($parsedValue, $color->getTranslations('name'));
                        })->first()['id'];
                    }
                }

                return ['all_color_ids' => $parsedData];
            };
        }

        if ($productType->has_size) {
            if ($productType->has_length) {
                $parsers[] = function ($data) {
                    return ['length' => floatval($data)];
                };
            }
            if ($productType->has_width) {
                $parsers[] = function ($data) {
                    return ['width' => floatval($data)];
                };
            }
            if ($productType->has_height) {
                $parsers[] = function ($data) {
                    return ['height' => floatval($data)];
                };
            }
        }

        foreach ($productType->fields as $customField) {
            if ($customField->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_STRING) {
                $parsers[] = function ($data) use($customField) {
                    return ['custom_fields' => [
                        (string) $customField->id => $data
                    ]];
                };
            } else if ($customField->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_NUMBER ||
                $customField->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_SIZE) {
                $parsers[] = function ($data) use($customField) {
                    return ['custom_fields' => [
                        (string) $customField->id => floatval($data),
                    ]];
                };

            } else if ($customField->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION) {
                if ($customField->is_multiselectable) {

                    $parsers[] = function ($data) use($customField) {
                        $parsedData = [];
                        if ($data) {
                            $value = str_replace(', ', ',', $data);
                            $values = explode(',', $value);

                            foreach ($values as $parsedValue) {
                                $parsedData[] = $this->productFieldOptions->filter(function ($option) use($parsedValue) {
                                    return in_array($parsedValue, $option->getTranslations('name'));
                                })->first()['id'];
                            }
                        }

                        return ['custom_fields' => [
                            (string) $customField->id => $parsedData,
                        ]];
                    };
                } else {
                    $parsers[] = function ($data) use($customField) {
                        return ['custom_fields' => [
                            (string) $customField->id => $this->productFieldOptions->filter(function ($option) use($data) {
                                return in_array($data, $option->getTranslations('name'));
                            })->first()['id'],
                        ]];
                    };
                }
            }
        }

        return $parsers;
    }
}
