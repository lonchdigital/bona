<?php

namespace App\Services\Product;

use App\Models\Category;
use App\Models\Color;
use App\Models\Currency;
use App\Models\HomePageBestSalesProducts;
use App\Models\HomePageNewProducts;
use App\Models\Product;
use App\Models\Faqs;
use App\Models\ProductFaqs;
use App\Models\ProductField;
use App\Models\ProductFieldOption;
use App\Models\SeoText;
use App\Models\ProductText;
use App\Models\ProductSeoText;
use App\Models\ProductGalleries;
use App\Models\ProductType;
use App\Models\User;
use App\Models\CartProducts;
use App\Models\ProductCharacteristics;
use App\Models\ProductSubItems;
use App\Models\ProductVideos;
use App\Models\ProductAttributeOptions;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\Currency\CurrencyService;
use App\Services\Product\DTO\EditProductDTO;
use App\Services\Product\DTO\FilterProductDTO;
use App\Services\Product\DTO\FilterProductAdminDTO;
use App\Services\Product\DTO\SearchProductDTO;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ProductService extends BaseService
{
    public function __construct(
        private readonly ProductFiltersAdminService $filtersAdminService,
        private readonly ProductFiltersService      $filterService,
//        private readonly CurrencyService            $currencyService,
    ) { }

    const PRODUCT_IMAGES_FOLDER = 'product-images';

    public function getParentProductData(Product $product): Product
    {
        return $product;
    }

    public function getProductsByTypePaginatedAdmin(int $productTypeId, FilterProductAdminDTO $request, ?int $perPage = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Product::query();

        $query->with([
//            'collection',
            'brand',
//            'color',
            'creator',
//            'children.brand',
//            'children.color',
//            'children.collection',
//            'children.creator',
        ]);

        $query = $this->filtersAdminService->handleProductFilters($request, $query);

        return $query->where('product_type_id', $productTypeId)
            ->paginate(config('domain.items_per_page'));
    }

    public function getBestSellersByBrandId(int $brandId): Collection
    {
        return Product::where('brand_id', $brandId)->limit(6)->orderBy('orders_count')->get();
    }

    // TODO: Remove when finish
    /*public function getProductsByTypePaginated(ProductType $productType, FilterProductDTO $request, int $perPage, int $page): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Product::query();

        $query = $this->filterService->handleProductFilters($productType, $request->filters, $query);

        return $query->where('product_type_id', 8)
            ->paginate($perPage, '*', null, $page);
    }*/


    public function getProductsByTypePaginated(ProductType $productType, FilterProductDTO $request, int $perPage, int $page): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Product::query();

        $query = $this->filterService->handleProductFilters($productType, $request->filters, $query);


        /*dd($query->where(function($query) use ($productType) {
            $query->where('product_type_id', $productType->id)
                ->orWhereHas('productTypes', function($query) use ($productType) {
                    $query->where('product_types.id', $productType->id);
                });
        })->get());*/

        return $query->where(function($query) use ($productType) {
            $query->where('product_type_id', $productType->id)
                ->orWhereHas('productTypes', function($query) use ($productType) {
                    $query->where('product_types.id', $productType->id);
                });
        })->paginate($perPage, '*', null, $page);
    }

    public function getAllProductsPaginated(FilterProductDTO $request,int $perPage, int $page, array $allFilters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Product::query();

        $query = $this->filterService->handleAllProductFilters($request->filters, $query, false, $allFilters);

        return $query->paginate($perPage, '*', null, $page);
    }

    public function getAllProductsCountByFilters(FilterProductDTO $request, array $allFilters): array
    {
        $query = Product::query();

        $productsCount = $this->filterService->handleAllProductFilters($request->filters, $query, false, $allFilters)->count();

        return ['count' => $productsCount];
    }

    public function getAllProductsMaxPrice(FilterProductDTO $request): int
    {
        $query = Product::query();
        $maxPrice = $this->filterService->handleAllProductFilters($request->filters, $query, false, [])->max('price');

        return ( !is_null($maxPrice) ) ? $maxPrice : 0;
    }

    public function getProductsMaxPrice(ProductType $productType): int
    {
        // TODO:: this function was improved
//        $maxPrice = Product::where('product_type_id', $productType->id)->max('price');

        $query = Product::query();
        $maxPrice = $query->where(function($query) use ($productType) {
            $query->where('product_type_id', $productType->id)
                ->orWhereHas('productTypes', function($query) use ($productType) {
                    $query->where('product_types.id', $productType->id);
                });
        })->max('price');

        return ( !is_null($maxPrice) ) ? $maxPrice : 0;
    }

    public function getProductsMaxPriceByAvailability(ProductType $productType): int
    {
        // TODO:: this function was improved
//        $maxPrice = Product::where('product_type_id', $productType->id)->max('price');

        $query = Product::query();
        $maxPrice = $query->where('availability_status_id', 2)->where(function($query) use ($productType) {
            $query->where('product_type_id', $productType->id)
                ->orWhereHas('productTypes', function($query) use ($productType) {
                    $query->where('product_types.id', $productType->id);
                });
        })->max('price');

        return ( !is_null($maxPrice) ) ? $maxPrice : 0;
    }

    public function getProductsByColorPaginated(int $perPage, int $page, Color $color): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Product::whereHas('colors', function($query) use ($color) {
            $query->where('colors.id', $color->id);
        })
            ->paginate($perPage, ['*'], null, $page);
    }

    public function getProductTypeByColorPaginated(int $perPage, int $page, ProductType $productType, Color $color): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Product::where('product_type_id', $productType->id)->whereHas('colors', function($query) use ($color) {
            $query->where('colors.id', $color->id);
        })
            ->paginate($perPage, ['*'], null, $page);
    }

    public function getProductsByFieldPaginated(int $perPage, int $page, ProductField $productField, string $productOptionID)
    {
        return Product::whereJsonContains('custom_fields', [$productField->id => $productOptionID])
            ->paginate($perPage, ['*'], null, $page);
    }

    public function getProductsByDiscountPaginated(int $perPage, int $page): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Product::where('old_price', '>', 0)
            ->whereNotNull('old_price')
            ->paginate($perPage, ['*'], null, $page);
    }

    public function getProductsByAvailabilityPaginated(int $perPage, int $page): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Product::where('availability_status_id', 2)
            ->paginate($perPage, ['*'], null, $page);
    }

    public function getProductsDoorsByAvailabilityPaginated(int $perPage, int $page): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $targetTypeIds = [1, 2, 3, 4, 5, 19, 20, 21];

        return Product::where('availability_status_id', 2)
            ->whereHas('productType', function ($query) use ($targetTypeIds) {
                $query->whereIn('id', $targetTypeIds);
            })
            ->paginate($perPage, ['*'], null, $page);
    }

    public function getProductsByBrandPaginated(int $perPage, int $page, int $brandId): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Product::query();

        /*return $query->where('brand_id', $brandId)
            ->paginate($perPage, '*', null, $page);*/

        return $query->where('brand_id', $brandId)
            ->paginate($perPage);
    }

    public function getProductsByCollectionAndTypePaginated(\App\Models\Collection $collection, FilterProductDTO $request, int $perPage, int $page): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Product::with(['children']);

        $query = $query->where('collection_id', $collection->id);

        $query = $this->filterService->handleSortingFilter($query, $request->filters);

        return $query->paginate($perPage, '*', null, $page);
    }

    public function getProductsByTypePaginatedByCategory(ProductType $productType, Category $category, FilterProductDTO $request, int $perPage, int $page): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Product::query();

        $query = $this->filterService->handleProductFilters($productType, $request->filters, $query);

        $query->whereHas('categories', function (Builder $query) use($category) {
            $query->where('category_id', $category->id);
        });

        return $query->where('product_type_id', $productType->id)
            ->paginate($perPage, '*', null, $page);
    }

    public function getProductsCategoryByAvailability(ProductType $productType, Category $category, FilterProductDTO $request, int $perPage, int $page): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Product::query();

        $query = $this->filterService->handleProductFilters($productType, $request->filters, $query);

        $query->where('availability_status_id', 2)->whereHas('categories', function (Builder $query) use($category) {
            $query->where('category_id', $category->id);
        });

        return $query->where('product_type_id', $productType->id)
            ->paginate($perPage, '*', null, $page);
    }

    public function getProductsCountByFilters(ProductType $productType, FilterProductDTO $request): array
    {
        $query = Product::query();
        $query = $this->filterService->handleProductFilters($productType, $request->filters, $query, true);

        $productsCount = $query->where(function($query) use ($productType) {
            $query->where('product_type_id', $productType->id)
                ->orWhereHas('productTypes', function($query) use ($productType) {
                    $query->where('product_types.id', $productType->id);
                });
        })->count();

        return ['count' => $productsCount];
    }


    public function getProductShortText(int $id): array
    {
        $result = ProductText::where('product_id', $id)->get();
        $data = [];

        foreach ($result as $value) {
            $data['content'][$value['language']] = $value['short_content'];
        }

        return $data;
    }

    public function getProductText(int $id): array
    {
        $result = ProductText::where('product_id', $id)->get();
        $data = [];

        foreach ($result as $value) {
            $data['content'][$value['language']] = $value['content'];
        }

        return $data;
    }

    public function getProductTextByLanguage(int $id, string $language)
    {
        $productTextData = ProductText::where('product_id', $id)->get();
        $data = [];

        if ($productTextData) {
            $data['short_content'] = $productTextData->where('language', $language)->first()->short_content;
            $data['content'] = $productTextData->where('language', $language)->first()->content;
            return $data;
        }

        return null;
    }

    public function searchParentProducts(FilterProductAdminDTO $request): Collection
    {
        $query = Product::query();

        if ($request->search) {
            $query->orWhere(function (Builder $query) use($request) {
                return $query->where('name', 'like', '%' . $request->search . '%');
            });
        }

        return $query->limit(10)->get();
    }

    public function getProductTypeWithFields(int $productTypeId): ?ProductType
    {
        return ProductType::with('fields')->where('id', $productTypeId)->first();
    }

    /*public function searchProducts(SearchProductDTO $request): Collection
    {
        $query = Product::query();

        if ($request->query) {
            $query->where(function (Builder $query) use($request) {
                return $query->where('name', 'like', '%' . $request->query . '%')
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($request->query) . '%'])
                    ->orWhere('sku', 'like', '%' . $request->query . '%');
            });

            return $query->limit(5)->get();

        } else {
            return collect([]);
        }
    }*/

    public function searchProducts(SearchProductDTO $request)
    {
        $query = Product::query();

        if ($request->query) {
            $searchTerm = '%' . $request->query . '%';
            $query->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(name, "$.ru")) LIKE ? OR LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ru"))) LIKE ?', [$searchTerm, $searchTerm])
                ->orWhereRaw('JSON_UNQUOTE(JSON_EXTRACT(name, "$.uk")) LIKE ? OR LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.uk"))) LIKE ?', [$searchTerm, $searchTerm]);

            return $query->limit(5)->get();
        }else {
            return collect([]);
        }
    }

    public function searchAllProducts(SearchProductDTO $request): Collection
    {
        $query = Product::select(['id', 'name', 'sku'])->limit(10);

        if ($request->query) {
            $query->where(function ($query) use($request) {
                return $query->where('name', 'like', '%' . $request->query . '%')
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($request->query) . '%'])
                    ->orWhere('sku', 'like', '%' . $request->query . '%');
            });
        }

        return $query->get();
    }

    public function getSubProducts(int $subProductsTypeId): Collection
    {
        return Product::where('product_type_id', $subProductsTypeId)->get();
    }

    public function searchSubProducts(SearchProductDTO $request, int $subProductsTypeId): Collection
    {
        $query = Product::where('product_type_id', $subProductsTypeId)->select(['id', 'name', 'sku'])->limit(10);

        if ($request->query) {
            $query->where(function ($query) use($request) {
                return $query->where('name', 'like', '%' . $request->query . '%')
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($request->query) . '%'])
                    ->orWhere('sku', 'like', '%' . $request->query . '%');
            });
        }

        return $query->get();
    }

    public function createProduct(User $creator, ProductType $productType, EditProductDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($productType, $request, $creator) {
            $productData = [
                'is_active' => 0,
                'creator_id' => $creator->id,
                'product_type_id' => $productType->id,
                'sku' => $request->sku,
                'sub_products' => ( !is_null($request->selectedSubProductsId) ) ? json_encode($request->selectedSubProductsId) : null,
                'name' => $request->name,
                'slug' => $request->slug,
                'old_price' => $request->oldPrice,
                'price' => $request->price,
//                'price_in_currency' => $request->priceInCurrency,
                'price_currency_id' => $request->currencyId,
                'availability_status_id' => $request->availabilityStatusId,
                'country_id' => $request->countryId,
                'meta_title' => $request->metaTitle,
                'meta_description' => $request->metaDescription,
                'meta_keywords' => $request->metaKeyWords,
                'meta_tags' => $request->metaTags,
                'length' => $request->length,
                'width' => $request->width,
                'height' => $request->height,
                'special_offers' => $request->specialOfferIds,
            ];

            if ($productType->has_color) {
                $productData['main_color_id'] = $request->colorId;
            }

            if ($productType->has_brand) {
                $productData['brand_id'] = $request->brandId;
            }

            //handle images
            if( !is_null($request->mainImage) ) {
                $storagePath = self::PRODUCT_IMAGES_FOLDER .'/'. date('m.Y');
                $previewImagePath = sha1(time()) . '_' . Str::random(10) . '_preview';
                $mainImagePath = sha1(time()) . '_' . Str::random(10) . '_main';

                $productData['preview_image_path'] = $storagePath .'/'. $previewImagePath . '.webp';
                $productData['main_image_path'] = $storagePath .'/'. $mainImagePath . '.webp';
            }

            if ($request->customFields && count($productType->fields)) {
                $productData['custom_fields'] = $this->prepareCustomFieldsToSync($request->customFields);
            }

            $product = Product::create($productData);


            if( !is_null($request->gallery) ) {
                $this->syncGallery($product->id, $request->gallery, $request->galleryColorIds);
            }
            if( !is_null($request->characteristics) ) {
                $this->syncCharacteristics($product->id, $request->characteristics);
            }
            if( !is_null($request->videos) ) {
                $this->syncVideos($product->id, $request->videos);
            }
            if( !is_null($request->attributes) ) {
                $this->syncAttributes($product->id, $request->attributes);
            }

            ProductText::updateProductShortText($product->id, $request->productShortText);
            ProductText::updateProductText($product->id, $request->productText);

            if( !is_null($request->faqs) ) {
                $this->syncProductFaqs($product->id, $request->faqs);
            }
            ProductSeoText::updateProductSeoText($product->id, $request->seoTitle, $request->seoText);

            //all colors
            if ($productType->has_color) {
                $this->syncColors($request->allColorIds, $product);
            }

            //all categories
            if ($productType->has_category) {
                $product->categories()->sync($request->categoryIds);
            }

            if( !is_null($request->mainImage) ) {
                $this->storePreviewImage($previewImagePath, $request->mainImage, 'webp');
                $this->storePreviewImage($previewImagePath, $request->mainImage, 'jpg');

                $this->storeProductImage($mainImagePath, $request->mainImage, 'webp');
                $this->storeProductImage($mainImagePath, $request->mainImage, 'jpg');
            }

            return ServiceActionResult::make(true, trans('admin.product_create_success'));
        });
    }

    public function productEdit(ProductType $productType, Product $product, EditProductDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($productType, $product, $request) {
            $this->syncGallery($product->id, $request->gallery, $request->galleryColorIds);
            $this->syncCharacteristics($product->id, $request->characteristics);
            $this->syncVideos($product->id, $request->videos);
            $this->syncAttributes($product->id, $request->attributes);
            ProductText::updateProductShortText($product->id, $request->productShortText);
            ProductText::updateProductText($product->id, $request->productText);
            $this->syncProductFaqs($product->id, $request->faqs);
            ProductSeoText::updateProductSeoText($product->id, $request->seoTitle, $request->seoText);

            $dataToUpdate = [
//                'is_active' => $request->isActive,
                'is_active' => 0,
                'product_type_id' => $productType->id,
                'sku' => $request->sku,
                'sub_products' => $request->selectedSubProductsId,
                'name' => $request->name,
                'slug' => $request->slug,
                'old_price' => $request->oldPrice,
                'price' => $request->price,
//                'price_in_currency' => $request->priceInCurrency,
                'price_currency_id' => $request->currencyId,
                'availability_status_id' => $request->availabilityStatusId,
                'meta_title' => $request->metaTitle,
                'meta_description' => $request->metaDescription,
                'meta_keywords' => $request->metaKeyWords,
                'meta_tags' => $request->metaTags,
                'length' => $request->length,
                'width' => $request->width,
                'height' => $request->height,
                'special_offers' => $request->specialOfferIds,
            ];

            if ($productType->has_color) {
                $dataToUpdate['main_color_id'] = $request->colorId;
            }

            if ($productType->has_brand) {
                $dataToUpdate['brand_id'] = $request->brandId;
            }


            $imagesToDelete = [];
            $mainImage = null;
            $previewImage = null;
            if ($request->mainImage) {
                $imagesToDelete[] = $product->main_image_path;
                $imagesToDelete[] = $product->preview_image_path;
                $storagePath = self::PRODUCT_IMAGES_FOLDER .'/'. date('m.Y');

                $previewImagePath = sha1(time()) . '_' . Str::random(10) . '_preview';
                $mainImagePath = sha1(time()) . '_' . Str::random(10) . '_main';

                $dataToUpdate['preview_image_path'] = $storagePath .'/'. $previewImagePath . '.webp';
                $dataToUpdate['main_image_path'] = $storagePath .'/'. $mainImagePath . '.webp';

                $mainImage['image'] = $request->mainImage;
                $mainImage['path'] = $mainImagePath;

                $previewImage['image'] = $request->mainImage;
                $previewImage['path'] = $previewImagePath;
            }

            // Remove product image
            if( !$request->mainImage && $request->mainImageDeleted ) {
                $imagesToDelete[] = $product->main_image_path;
                $imagesToDelete[] = $product->preview_image_path;
                $product->main_image_path = null;
                $product->preview_image_path = null;
            }


            if ($request->customFields && count($productType->fields)) {
                $dataToUpdate['custom_fields'] = $this->prepareCustomFieldsToSync($request->customFields);
            }

            $product->update($dataToUpdate);


            //all colors
            if ($productType->has_color) {
                $this->syncColors($request->allColorIds, $product);
            }

            //all categories
            if ($productType->has_category) {
                $product->categories()->sync($request->categoryIds);
//                $product->categories()->sync([19]);
            }


            //store images
            if ($mainImage) {
                $this->storeProductImage($mainImage['path'], $mainImage['image'], 'webp');
                $this->storeProductImage($mainImage['path'], $mainImage['image'], 'jpg');
            }
            if ($previewImage) {
                $this->storePreviewImage($previewImage['path'], $previewImage['image'], 'webp');
                $this->storePreviewImage($previewImage['path'], $previewImage['image'], 'jpg');
            }

            //delete images
            foreach ($imagesToDelete as $imageToDelete) {
                if( !is_null($imageToDelete) ) {
                    $this->deleteImage($imageToDelete);
                }
            }

            return ServiceActionResult::make(true, trans('admin.product_edit_success'));
        });
    }

    public function getProductCharacteristics(int $id): Collection
    {
        return ProductCharacteristics::where('product_id', $id)->get();
    }

    private function syncCharacteristics(int $product_id, ?array $characteristics): void
    {
        $existingCharacteristics = ProductCharacteristics::where('product_id', $product_id)->get();
        if ($characteristics) {
            foreach ($characteristics as $characteristic) {
                $dataToUpdate = [
                    'product_id' => $product_id,
                    'name' => $characteristic['name'],
                    'value' => $characteristic['value'],
                ];

                if (isset($characteristic['id']) && $characteristic['id']) {
                    $existingCharacteristic = $existingCharacteristics->where('id', $characteristic['id'])->first();
                    if (!$existingCharacteristic) {
                        throw new \Exception('Incorrect faq id: ' . $characteristic['id']);
                    }

                    $existingCharacteristic->update($dataToUpdate);
                } else {
                    ProductCharacteristics::create($dataToUpdate);
                }
            }
        }

        $existingCharacteristicsInRequest = $characteristics ? array_filter(array_column($characteristics, 'id'), function ($item) {
            return $item !== null;
        }): [];

        $faqsToDelete = $existingCharacteristics->whereNotIn('id', $existingCharacteristicsInRequest);

        foreach ($faqsToDelete as $faqToDelete) {
            $faqToDelete->delete();
        }

    }

    public function replaceTagsWithData(string $text, Product $product): string
    {
        $allTags = [
            '%title%' => $product->name,
            '%price%' => $product->price,
            '%product_type%' => $product->productType->name,
        ];
        return str_replace(array_keys($allTags), array_values($allTags), $text);
    }

    public function getSelectedSubItems(?array $sub_products): Collection|array
    {
        if(!empty($sub_products)) {
            return Product::whereIn('id', $sub_products)->get();
        } else {
            return [];
        }
    }
    public function getSelectedSubItemsWithCategories(array|bool $sub_products): array
    {
        if( $sub_products ) {
            $subProducts = Product::whereIn('id', $sub_products)->get();

            $categoryProducts = [];
            foreach ($subProducts as $item) {
                $categoryName = $item->categories[0]->name;

                if (!isset($categoryProducts[$categoryName])) {
                    $categoryProducts[$categoryName] = [];
                }

                $categoryProducts[$categoryName][] = $item;
            }

            return $categoryProducts;
        }

        return [];
    }

    private function syncSubProducts(int $product_id, ?array $subItems): void
    {
        $existingSubItems = ProductSubItems::where('product_id', $product_id)->get();
        if ($subItems) {
            foreach ($subItems as $subItem) {
                $dataToUpdate = [
                    'product_id' => $product_id,
                    'sub_sub_item_id' => $subItem,
                ];

                if (isset($subItem['id']) && $subItem['id']) {
                    $existingVideo = $existingSubItems->where('id', $subItem['id'])->first();
                    if (!$existingVideo) {
                        throw new \Exception('Incorrect faq id: ' . $subItem['id']);
                    }

                    $existingVideo->update($dataToUpdate);
                } else {
                    ProductSubItems::create($dataToUpdate);
                }
            }
        }

        $existingSubItemsInRequest = $subItems ? array_filter(array_column($subItems, 'id'), function ($item) {
            return $item !== null;
        }): [];

        $subItemsToDelete = $existingSubItems->whereNotIn('id', $existingSubItemsInRequest);

        foreach ($subItemsToDelete as $subItemToDelete) {
            $subItemToDelete->delete();
        }

    }

    public function getProductVideos(int $id): Collection
    {
        return ProductVideos::where('product_id', $id)->get();
    }

    public function getProductFaqs(int $id): Collection
    {
        return ProductFaqs::where('product_id', $id)->get();
    }

    public function getProductSeoText(int $id): array
    {
        $result = ProductSeoText::where('product_id', $id)->get();
        $data = [];

        foreach ($result as $value) {
            $data['title'][$value['language']] = $value['title'];
            $data['content'][$value['language']] = $value['content'];
        }

        return $data;
    }

    public function getAttributeOptions(int $product_id, $productType): array
    {
        $attributeOptions = [];

        $currentAttributeOptions = $productType->attributes()
            ->with(['productAttributeOptions' => function ($query) use ($product_id) {
                $query->where('product_id', $product_id);
            }])
            ->get();

        foreach ($currentAttributeOptions as $key => $attribute) {

            $atr_options = [];
            foreach ($attribute->productAttributeOptions as $attributeOption){
                $atr_options[] = $attributeOption;
            }

            $attributeOptions[$attribute->id] = $atr_options;
        }

        return $attributeOptions;
    }

    public function getAttributeNamesWithOptions(int $product_id, $productType): array
    {
        $attributeOptions = [];

        $currentAttributeOptions = $productType->attributes()
            ->with(['productAttributeOptions' => function ($query) use ($product_id) {
                $query->where('product_id', $product_id);
            }])
            ->get();

        if(count($currentAttributeOptions)) {
            foreach ($currentAttributeOptions as $key => $attribute) {
                $atr_options = [];
                foreach ($attribute->productAttributeOptions as $attributeOption){
                    $atr_options[] = $attributeOption;
                }
                $attributeOptions[$attribute->id][$attribute->attribute_name] = $atr_options;
            }
        }

        return $attributeOptions;
    }
    private function syncVideos(int $product_id, ?array $videos): void
    {
        $existingVideos = ProductVideos::where('product_id', $product_id)->get();
        if ($videos) {
            foreach ($videos as $video) {

                $dataToUpdate = [
                    'product_id' => $product_id,
                    'tab' => $video['tab'],
                    'iframe' => $video['iframe'],
                ];

                if (isset($video['id']) && $video['id']) {
                    $existingVideo = $existingVideos->where('id', $video['id'])->first();
                    if (!$existingVideo) {
                        throw new \Exception('Incorrect faq id: ' . $video['id']);
                    }

                    $existingVideo->update($dataToUpdate);
                } else {
                    ProductVideos::create($dataToUpdate);
                }
            }
        }

        $existingVideosInRequest = $videos ? array_filter(array_column($videos, 'id'), function ($item) {
            return $item !== null;
        }): [];

        $videosToDelete = $existingVideos->whereNotIn('id', $existingVideosInRequest);

        foreach ($videosToDelete as $videoToDelete) {
            $videoToDelete->delete();
        }

    }

    private function syncAttributes(int $product_id, ?array $attributes): void
    {

        $allExistingAttributeOptions = ProductAttributeOptions::where('product_id', $product_id)->get();

        if ($attributes) {
            foreach ($attributes as $attribute_id => $attribute) :

//                $existingAttributeOptions = ProductAttributeOptions::where('product_id', $product_id)->where('product_attribute_id', $attribute_id)->get();

                foreach($attribute as $item):

                    $dataToUpdate = [
                        'product_id' => $product_id,
                        'product_attribute_id' => $attribute_id,
                        'name' => $item['name'],
                        'price' => $item['price'],
                    ];

                    if (isset($attribute['id']) && $attribute['id']) {

                        dd($attribute['id']);

                        /*$existingAttributeOption = $existingAttributeOptions->where('id', $item['id'])->first();
                        if (!$existingAttributeOption) {
                            throw new \Exception('Incorrect faq id: ' . $item['id']);
                        }

                        $existingAttributeOption->update($dataToUpdate);*/
                    } else {
                        ProductAttributeOptions::create($dataToUpdate);
                    }

                endforeach;
            endforeach;
        }


        $existingAttributeOptionsInRequest = $attributes ? array_filter(array_column($attributes, 'id'), function ($item) {
            return $item !== null;
        }): [];

        $attributeOptionsToDelete = $allExistingAttributeOptions->whereNotIn('id', $existingAttributeOptionsInRequest);

        foreach ($attributeOptionsToDelete as $attributeOptionToDelete) {
            $attributeOptionToDelete->delete();
        }

    }

    private function syncColors($allColors, $product)
    {
        if (!is_null($allColors) && count($allColors)) {
            $colorsToUpdate = [];
            foreach ( $allColors as $color ) {
                $colorsToUpdate[$color['color_id']] = ['price' => $color['price']];
            }
            $product->colors()->sync($colorsToUpdate);
        } else {
            $product->colors()->sync([]);
        }
    }


    public function getProductGallery(int $id): Collection
    {
        return ProductGalleries::where('product_id', $id)->get();
    }
    private function syncGallery(int $product_id, ?array $gallery_images, ?array $gallery_color_ids): void
    {
        $imagesToDelete = [];

        $existingGalleryImages = ProductGalleries::where('product_id', $product_id)->get();

        if ($gallery_images) {
            foreach ($gallery_images as $key => $gallery_image) {
                $dataToUpdate = [
                    'product_id' => $product_id,
                    'color_id' => $gallery_color_ids[$key]['color_id']
                ];

                if (isset($gallery_image['image'])) {
                    $storagePath = self::PRODUCT_IMAGES_FOLDER .'/'. date('m.Y');
                    $galleryImagePath = sha1(time()) . '_' . Str::random(10);

                    $this->storeProductImage($galleryImagePath, $gallery_image['image'], 'webp');
                    $this->storeProductImage($galleryImagePath, $gallery_image['image'], 'jpg');

                    $dataToUpdate['image_path'] = $storagePath .'/'. $galleryImagePath . '.webp';
                }

                if (isset($gallery_image['id']) && $gallery_image['id']) {
                    $existingGalleryImage = $existingGalleryImages->where('id', $gallery_image['id'])->first();
                    if (!$existingGalleryImage) {
                        throw new \Exception('Incorrect testimonial id: ' . $gallery_image['id']);
                    }

                    if (isset($gallery_image['image'])) {
                        $imagesToDelete[] = $existingGalleryImage->image_path;
                    }

                    $existingGalleryImage->update($dataToUpdate);
                } else {
                    ProductGalleries::create($dataToUpdate);
                }
            }
        }

        $existingGalleryImagesInRequest = $gallery_images ? array_filter(array_column($gallery_images, 'id'), function ($item) {
            return $item !== null;
        }): [];

        $galleryImagesToDelete = $existingGalleryImages->whereNotIn('id', $existingGalleryImagesInRequest);

        foreach ($galleryImagesToDelete as $galleryImageToDelete) {
            $imagesToDelete[] = $galleryImageToDelete->image_path;
            $galleryImageToDelete->delete();
        }
        foreach ($imagesToDelete as $imageToDelete) {
            $this->deleteImage($imageToDelete);
        }

    }


    public function handleProductPriceInCurrency(Currency $currency, Currency $baseCurrency, float $priceInCurrency, ?float $oldPriceInCurrency): array
    {
        $baseCurrencyId = $baseCurrency->id;

        $oldPrice = null;
        if ($baseCurrencyId == $currency->id || ($baseCurrencyId != $currency->id && !$currency->rate)) {
            $price = $priceInCurrency;

            if ($oldPriceInCurrency) {
                $oldPrice = $oldPriceInCurrency;
            }
        } else {
            $price = $priceInCurrency * $currency->rate;

            if ($oldPriceInCurrency) {
                $oldPrice = $oldPriceInCurrency * $currency->rate;
            }
        }

        return [
            'price' => $price,
            'old_price' => $oldPrice
        ];
    }

    public function productDelete(Product $product): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($product) {

            if (HomePageNewProducts::where('product_id', $product->id)->exists() || HomePageBestSalesProducts::where('product_id', $product->id)->exists()) {
                return ServiceActionResult::make(false, trans('admin.product_in_use_on_homepage'));
            }

            $cartProducts = CartProducts::where('product_id', $product->id)->get();
            if( count($cartProducts) >= 1 ) {
                foreach ($cartProducts as $cartProduct) {
                    $cartProduct->delete();
                }
            }


            $imagesToDelete = [];
            if ($product->main_image_path) {
                $imagesToDelete[] = $product->main_image_path;
                $imagesToDelete[] = $product->preview_image_path;
            }

            $product->colors()->sync([]);
            $product->categories()->sync([]);

            $this->syncGallery($product->id, [], []);
            $this->syncCharacteristics($product->id, []);
            $this->syncVideos($product->id, []);
            $this->syncAttributes($product->id, []);
            ProductText::deleteProductText($product->id);
            $this->syncProductFaqs($product->id, []);
            ProductSeoText::where('product_id', $product->id)->delete();

            $product->delete();

            foreach ($imagesToDelete as $imageToDelete) {
                if( !is_null($imageToDelete) ) {
                    $this->deleteImage($imageToDelete);
                }
            }

            return ServiceActionResult::make(true, trans('admin.product_delete_success'));
        });

    }

    public function getProductsBySameCollection(Product $product): Collection
    {
        $collectionId = $product->collection_id;
        $productsToExclude = [];

        $query = Product::where('collection_id', $collectionId)
            ->where('product_type_id', $product->product_type_id)
            ->whereNot('id', $product->id)
            ->whereNotIn('id', $productsToExclude)
            ->limit(6);

            $query = $this->filterService->handleSortByPopularity($query);

        return $query->get();
    }

    public function getSameTypeProducts(Product $product): Collection
    {
        return Product::where('product_type_id', $product->product_type_id)->whereNot('id', $product->id)->limit(6)->get();
    }

    public function updateProductsPriceByCurrencyRate(int $currencyId): void
    {
        $currency = Currency::find($currencyId);
            $this->coverWithDBTransactionWithoutResponse(function () use($currency) {
                Product::where('price_currency_id', $currency->id)->chunk(500, function ($products) use($currency) {
                    if ($currency->rate) {
                        Product::whereIn('id', $products->pluck('id'))->update([
                            'price' => \DB::raw('price_in_currency * ' . $currency->rate),
                        ]);
                    } else {
                        Product::whereIn('id', $products->pluck('id'))->update([
                            'price' => \DB::raw('price_in_currency'),
                        ]);
                    }
                });
            });
    }

    public function storePreviewImage(string $path, UploadedFile $image, string $format, $quality = 70): void
    {
        $storagePath = self::PRODUCT_IMAGES_FOLDER .'/'. date('m.Y');
        if (!Storage::disk(config('app.images_disk_default'))->exists($storagePath)) {
            Storage::disk(config('app.images_disk_default'))->makeDirectory($storagePath);
        }

        $image = Image::make($image);

        $imageWidth = intval(round($image->width() / 2, 0));
        $imageHeight = intval(round($image->height() / 2, 0));

        if($imageWidth > 400) {
            $image = $image->fit($imageWidth, $imageHeight)->encode($format, $quality);
        } else {
            $image = $image->encode($format, $quality);
        }

        Storage::disk(config('app.images_disk_default'))->put($storagePath . '/' . $path . '.'.$format, $image);
    }

    protected function storeProductImage(string $path, UploadedFile $image, string $format, $quality = 70): void
    {
        $storagePath = self::PRODUCT_IMAGES_FOLDER .'/'. date('m.Y');
        if (!Storage::disk(config('app.images_disk_default'))->exists($storagePath)) {
            Storage::disk(config('app.images_disk_default'))->makeDirectory($storagePath);
        }

        $image = Image::make($image)->encode($format, $quality);
        Storage::disk(config('app.images_disk_default'))->put($storagePath . '/' . $path . '.' . $format, $image);
    }

    private function prepareCustomFieldsToSync(array $rawCustomFieldsArray): array
    {
        //result should be '$fieldId' => ['value' => ['$value']]
        $result = [];
        foreach (array_column($rawCustomFieldsArray, 'field_id') as $customField) {
            $result[$customField] = $rawCustomFieldsArray[$customField]['value'];
        }

        return $result;
    }

    public function getLimitedProducts(int $limit): Collection
    {
        return Product::all()->take($limit);
    }

    public function getProductTypeFaqs(string $productTypeSlug): Collection
    {
        return Faqs::where('page_type', $productTypeSlug)->get();
    }


    public function getProductTypeSeoTextByLanguage(string $productTypeSlug, string $language)
    {
        $seoTextData = SeoText::where('page_type', $productTypeSlug)->get();
        $data = [];

        if ( count($seoTextData) ) {
            $data['title'] = $seoTextData->where('language', $language)->first()->title;
            $data['content'] = $seoTextData->where('language', $language)->first()->content;
            return $data;
        }
        return null;
    }

    private function syncProductFaqs(int $product_id, ?array $faqs): void
    {
        $existingFaqs = ProductFaqs::where('product_id', $product_id)->get();
        if ($faqs) {
            foreach ($faqs as $faq) {
                $dataToUpdate = [
                    'product_id' => $product_id,
                    'question' => $faq['question'],
                    'answer' => $faq['answer'],
                ];

                if (isset($faq['id']) && $faq['id']) {
                    $existingFaq = $existingFaqs->where('id', $faq['id'])->first();
                    if (!$existingFaq) {
                        throw new \Exception('Incorrect faq id: ' . $faq['id']);
                    }

                    $existingFaq->update($dataToUpdate);
                } else {
                    ProductFaqs::create($dataToUpdate);
                }
            }
        }

        $existingFaqsInRequest = $faqs ? array_filter(array_column($faqs, 'id'), function ($item) {
            return $item !== null;
        }): [];

        $faqsToDelete = $existingFaqs->whereNotIn('id', $existingFaqsInRequest);

        foreach ($faqsToDelete as $faqToDelete) {
            $faqToDelete->delete();
        }

    }

}
