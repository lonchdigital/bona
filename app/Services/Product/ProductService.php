<?php

namespace App\Services\Product;

use App\Models\CartProducts;
use App\Models\Category;
use App\Models\Color;
use App\Models\Currency;
use App\Models\Faqs;
use App\Models\HomePageBestSalesProducts;
use App\Models\HomePageNewProducts;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductField;
use App\Models\ProductSeoText;
use App\Models\ProductText;
use App\Models\ProductType;
use App\Models\SeoText;
use App\Models\User;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\Product\DTO\EditProductDTO;
use App\Services\Product\DTO\FilterProductAdminDTO;
use App\Services\Product\DTO\FilterProductDTO;
use App\Services\Product\DTO\SearchProductDTO;
use App\Support\Search\SearchTerm;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProductService extends BaseService
{
    private const PRODUCT_CONTENT_IMAGES_FOLDER = 'product-content-images';

    public function __construct(
        private readonly ProductFiltersAdminService $filtersAdminService,
        private readonly ProductFiltersService $filterService,
        private readonly ProductRelationsService $relationsService,
        private readonly ProductMediaService $mediaService,
    ) {}

    const PRODUCT_IMAGES_FOLDER = ProductMediaService::PRODUCT_IMAGES_FOLDER;

    public function getParentProductData(Product $product): Product
    {
        return $product;
    }

    public function getProductsByTypePaginatedAdmin(ProductType $productType, FilterProductAdminDTO $request, ?int $styleFieldId = null): LengthAwarePaginator
    {
        $query = Product::query()
            ->with(['brand', 'categories'])
            ->where('product_type_id', $productType->id);

        $query = $this->filtersAdminService->handleProductFilters($request, $query, $styleFieldId);

        if ($request->sort === 'name') {
            $query->orderBy('name->'.app()->getLocale(), $request->direction)
                ->orderBy('id', $request->direction);
        } elseif ($request->sort === 'created_at') {
            $query->orderBy('created_at', $request->direction)
                ->orderBy('id', $request->direction);
        } else {
            $query->orderByCatalogPosition();
        }

        return $query->paginate($request->perPage);
    }

    public function reorderProducts(ProductType $productType, array $orderedProductIds): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($productType, $orderedProductIds) {
            $orderedProductIds = array_values(array_map('intval', $orderedProductIds));
            $products = Product::query()
                ->where('product_type_id', $productType->id)
                ->orderByCatalogPosition()
                ->lockForUpdate()
                ->get(['id']);
            $allProductIds = $products->pluck('id')->map(fn ($id) => (int) $id)->all();
            $allowedIds = array_flip($allProductIds);

            if (count($orderedProductIds) !== count(array_unique($orderedProductIds)) ||
                collect($orderedProductIds)->contains(fn (int $id) => ! isset($allowedIds[$id]))) {
                return ServiceActionResult::make(false, trans('admin.product_reorder_invalid'));
            }

            $requestedIds = array_flip($orderedProductIds);
            $slots = [];

            foreach ($allProductIds as $index => $productId) {
                if (isset($requestedIds[$productId])) {
                    $slots[] = $index;
                }
            }

            if (count($slots) !== count($orderedProductIds)) {
                return ServiceActionResult::make(false, trans('admin.product_reorder_invalid'));
            }

            foreach ($slots as $index => $slot) {
                $allProductIds[$slot] = $orderedProductIds[$index];
            }

            foreach ($allProductIds as $index => $productId) {
                Product::query()->whereKey($productId)->update(['sort_order' => $index + 1]);
            }

            return ServiceActionResult::make(true, trans('admin.product_reorder_success'));
        });
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

    public function getProductsByTypePaginated(ProductType $productType, FilterProductDTO $request, int $perPage, int $page): LengthAwarePaginator
    {
        $query = Product::query()
            ->with(['brand', 'productType', 'colors', 'galleries']);

        $query = $this->filterService->handleProductFilters($productType, $request->filters, $query);

        return $query->where(function ($query) use ($productType) {
            $query->where('product_type_id', $productType->id)
                ->orWhereHas('productTypes', function ($query) use ($productType) {
                    $query->where('product_types.id', $productType->id);
                });
        })->paginate($perPage, '*', 'page', $page);
    }

    public function getAllProductsPaginated(FilterProductDTO $request, int $perPage, int $page, array $allFilters): LengthAwarePaginator
    {
        $query = Product::query()
            ->with(['brand', 'productType', 'colors', 'galleries']);

        $query = $this->filterService->handleAllProductFilters($request->filters, $query, false, $allFilters);

        return $query->paginate($perPage, '*', 'page', $page);
    }

    public function getAllProductsCountByFilters(FilterProductDTO $request, array $allFilters): array
    {
        $query = Product::query()->orderByAvailabilityStatus();

        $productsCount = $this->filterService->handleAllProductFilters($request->filters, $query, false, $allFilters)->count();

        return ['count' => $productsCount];
    }

    public function getAllProductsMaxPrice(FilterProductDTO $request): int
    {
        $query = Product::query();
        //        $maxPrice = $this->filterService->handleAllProductFilters($request->filters, $query, false, [])->max('price');

        $maxPrice = $query->max('price');

        return (! is_null($maxPrice)) ? $maxPrice : 0;
    }

    public function getProductsMaxPrice(ProductType $productType): int
    {
        // TODO:: this function was improved
        //        $maxPrice = Product::where('product_type_id', $productType->id)->max('price');

        $query = Product::query();
        $maxPrice = $query->where(function ($query) use ($productType) {
            $query->where('product_type_id', $productType->id)
                ->orWhereHas('productTypes', function ($query) use ($productType) {
                    $query->where('product_types.id', $productType->id);
                });
        })->max('price');

        return (! is_null($maxPrice)) ? $maxPrice : 0;
    }

    public function getProductsMaxPriceByCategory(ProductType $productType, Category $category): int
    {
        $query = Product::query();

        $query->whereHas('categories', function (Builder $query) use ($category) {
            $query->where('category_id', $category->id);
        });

        $maxPrice = $query->where(function ($query) use ($productType) {
            $query->where('product_type_id', $productType->id)
                ->orWhereHas('productTypes', function ($query) use ($productType) {
                    $query->where('product_types.id', $productType->id);
                });
        })->max('price');

        return (! is_null($maxPrice)) ? $maxPrice : 0;
    }

    public function getProductsMaxPriceByAvailability(ProductType $productType): int
    {
        $query = Product::query();
        $maxPrice = $query->where('availability_status_id', 2)->where(function ($query) use ($productType) {
            $query->where('product_type_id', $productType->id)
                ->orWhereHas('productTypes', function ($query) use ($productType) {
                    $query->where('product_types.id', $productType->id);
                });
        })->max('price');

        return (! is_null($maxPrice)) ? $maxPrice : 0;
    }

    public function getProductsMaxPriceByAvailabilityWithCategory(ProductType $productType, Category $category): int
    {
        $query = Product::query();

        $query->whereHas('categories', function (Builder $query) use ($category) {
            $query->where('category_id', $category->id);
        });

        $maxPrice = $query->where('availability_status_id', 2)->where(function ($query) use ($productType) {
            $query->where('product_type_id', $productType->id)
                ->orWhereHas('productTypes', function ($query) use ($productType) {
                    $query->where('product_types.id', $productType->id);
                });
        })->max('price');

        return (! is_null($maxPrice)) ? $maxPrice : 0;
    }

    public function getProductsByColorPaginated(int $perPage, int $page, Color $color): LengthAwarePaginator
    {
        return Product::orderByCatalogPosition()
            ->whereHas('colors', function ($query) use ($color) {
                $query->where('colors.id', $color->id);
            })
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function getProductTypeByColorPaginated(int $perPage, int $page, ProductType $productType, Color $color): LengthAwarePaginator
    {
        // TODO:: old request without main_color_id
        /*return Product::where('product_type_id', $productType->id)->whereHas('colors', function($query) use ($color) {
            $query->where('colors.id', $color->id);
        })
            ->paginate($perPage, ['*'], null, $page);*/

        return Product::orderByCatalogPosition()
            ->where('product_type_id', $productType->id)
            ->where(function ($query) use ($color) {
                $query->where('main_color_id', $color->id)
                    ->orWhereHas('colors', function ($query) use ($color) {

                        // $query->where('colors.id', $color->id);
                        // We need to show more white colors...
                        if ($color->id == 7) {
                            $query->whereIn('colors.id', [$color->id, 166, 40, 52, 177, 189]);
                        } else {
                            $query->where('colors.id', $color->id);
                        }
                    });
            })
            ->paginate($perPage);
    }

    public function getProductsByFieldPaginated(int $perPage, int $page, ProductField $productField, string $productOptionID)
    {
        return Product::orderByCatalogPosition()
            ->whereJsonContains('custom_fields', [$productField->id => $productOptionID])
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function getProductsByDiscountPaginated(int $perPage, int $page): LengthAwarePaginator
    {
        return Product::orderByCatalogPosition()->where('old_price', '>', 0)
            ->whereNotNull('old_price')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function getProductsByAvailabilityPaginated(int $perPage, int $page): LengthAwarePaginator
    {
        return Product::where('availability_status_id', 2)
            ->orderByCatalogPosition()
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function getProductsDoorsByAvailabilityPaginated(int $perPage, int $page): LengthAwarePaginator
    {
        $targetTypeIds = [1, 2, 3, 4, 5, 19, 20, 21];

        return Product::where('availability_status_id', 2)
            ->orderByCatalogPosition()
            ->whereHas('productType', function ($query) use ($targetTypeIds) {
                $query->whereIn('id', $targetTypeIds);
            })
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function getProductsByBrandPaginated(int $perPage, int $page, int $brandId): LengthAwarePaginator
    {
        $query = Product::query()
            ->with(['brand', 'productType', 'colors', 'galleries'])
            ->orderByCatalogPosition();

        /*return $query->where('brand_id', $brandId)
            ->paginate($perPage, '*', null, $page);*/

        return $query->where('brand_id', $brandId)
            ->paginate($perPage);
    }

    public function getProductsByCollectionAndTypePaginated(\App\Models\Collection $collection, FilterProductDTO $request, int $perPage, int $page): LengthAwarePaginator
    {
        $query = Product::with(['children']);

        $query = $query->where('collection_id', $collection->id);

        $query = $this->filterService->handleSortingFilter($query, $request->filters);

        return $query->paginate($perPage, '*', 'page', $page);
    }

    public function getProductsByTypePaginatedByCategory(ProductType $productType, Category $category, FilterProductDTO $request, int $perPage, int $page): LengthAwarePaginator
    {
        $query = Product::query()
            ->with(['brand', 'productType', 'colors', 'galleries']);

        $query = $this->filterService->handleProductFilters($productType, $request->filters, $query);

        $query->whereHas('categories', function (Builder $query) use ($category) {
            $query->where('category_id', $category->id);
        });

        return $query->where(function (Builder $query) use ($productType) {
            $query->where('product_type_id', $productType->id)
                ->orWhereHas('productTypes', function (Builder $query) use ($productType) {
                    $query->where('product_types.id', $productType->id);
                });
        })->paginate($perPage, '*', 'page', $page);
    }

    public function getProductsCategoryByAvailability(ProductType $productType, Category $category, FilterProductDTO $request, int $perPage, int $page): LengthAwarePaginator
    {
        $query = Product::query();

        $query = $this->filterService->handleProductFilters($productType, $request->filters, $query);

        $query->where('availability_status_id', 2)->whereHas('categories', function (Builder $query) use ($category) {
            $query->where('category_id', $category->id);
        });

        return $query->where(function (Builder $query) use ($productType) {
            $query->where('product_type_id', $productType->id)
                ->orWhereHas('productTypes', function (Builder $query) use ($productType) {
                    $query->where('product_types.id', $productType->id);
                });
        })->paginate($perPage, '*', 'page', $page);
    }

    public function getProductsCountWithCategoryByAvailability(ProductType $productType, Category $category, FilterProductDTO $request): array
    {
        $query = Product::query();

        $query = $this->filterService->handleProductFilters($productType, $request->filters, $query);

        $query->where('availability_status_id', 2)->whereHas('categories', function (Builder $query) use ($category) {
            $query->where('category_id', $category->id);
        });

        $productsCount = $query->where(function (Builder $query) use ($productType) {
            $query->where('product_type_id', $productType->id)
                ->orWhereHas('productTypes', function (Builder $query) use ($productType) {
                    $query->where('product_types.id', $productType->id);
                });
        })->count();

        return ['count' => $productsCount];
    }

    public function getProductsCountByFilters(ProductType $productType, FilterProductDTO $request): array
    {
        $query = Product::query();
        $query = $this->filterService->handleProductFilters($productType, $request->filters, $query, true);

        $productsCount = $query->where(function ($query) use ($productType) {
            $query->where('product_type_id', $productType->id)
                ->orWhereHas('productTypes', function ($query) use ($productType) {
                    $query->where('product_types.id', $productType->id);
                });
        })->count();

        return ['count' => $productsCount];
    }

    public function getProductsWithCategoryCountByFilters(ProductType $productType, Category $category, FilterProductDTO $request): array
    {
        $query = Product::query();
        $query = $this->filterService->handleProductFilters($productType, $request->filters, $query, true);

        $query->whereHas('categories', function (Builder $query) use ($category) {
            $query->where('category_id', $category->id);
        });

        $productsCount = $query->where(function ($query) use ($productType) {
            $query->where('product_type_id', $productType->id)
                ->orWhereHas('productTypes', function ($query) use ($productType) {
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
        $productText = ProductText::where('product_id', $id)
            ->where('language', $language)
            ->first();

        return [
            'short_content' => $productText?->short_content,
            'content' => $productText?->content,
        ];
    }

    public function searchParentProducts(FilterProductAdminDTO $request): Collection
    {
        $query = Product::query()->orderByAvailabilityStatus();

        if ($request->search) {
            $query->orWhere(function (Builder $query) use ($request) {
                return $query->where('name', 'like', '%'.$request->search.'%');
            });
        }

        return $query->limit(10)->get();
    }

    public function getProductTypeWithFields(int $productTypeId)
    {
        return ProductType::with('fields')->where('id', $productTypeId)->first();
    }

    public function searchAllProducts(SearchProductDTO $request): Collection
    {
        $query = Product::query()->select(['id', 'name', 'sku']);

        if ($request->query) {
            $query->where(function (Builder $query) use ($request) {
                if (ctype_digit($request->query)) {
                    $query->where('id', (int) $request->query)
                        ->orWhere(function (Builder $textQuery) use ($request) {
                            SearchTerm::applyToProducts($textQuery, $request->query);
                        });

                    return;
                }

                SearchTerm::applyToProducts($query, $request->query);
            });
        }

        return $query->orderBy('id')->limit(20)->get();
    }

    public function getSubProducts(int $subProductsTypeId): Collection
    {
        return Product::where('product_type_id', $subProductsTypeId)->get();
    }

    public function searchSubProducts(SearchProductDTO $request, int $subProductsTypeId): Collection
    {
        $query = Product::where('product_type_id', $subProductsTypeId)->select(['id', 'name', 'sku'])->limit(10);

        if ($request->query) {
            $query->where(function ($query) use ($request) {
                return $query->where('name', 'like', '%'.$request->query.'%')
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%'.strtolower($request->query).'%'])
                    ->orWhere('sku', 'like', '%'.$request->query.'%');
            });
        }

        return $query->get();
    }

    public function createProduct(User $creator, ProductType $productType, EditProductDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($productType, $request, $creator) {
            [$contentBlocks] = $this->prepareContentBlocks($request->contentBlocks);
            $productData = [
                'is_active' => 0,
                'creator_id' => $creator->id,
                'product_type_id' => $productType->id,
                'sort_order' => ((int) Product::query()->where('product_type_id', $productType->id)->max('sort_order')) + 1,
                'sku' => $request->sku,
                'sub_products' => $this->encodeSubProductIds($request->selectedSubProductsId),
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
                'content_blocks' => $contentBlocks ?: null,
            ];

            if ($productType->has_color) {
                $productData['main_color_id'] = $request->colorId;
            }

            if ($productType->has_brand) {
                $productData['brand_id'] = $request->brandId;
            }

            // handle images
            if (! is_null($request->mainImage)) {
                $storagePath = self::PRODUCT_IMAGES_FOLDER.'/'.date('m.Y');
                $previewImagePath = sha1(time()).'_'.Str::random(10).'_preview';
                $mainImagePath = sha1(time()).'_'.Str::random(10).'_main';

                $productData['preview_image_path'] = $storagePath.'/'.$previewImagePath.'.webp';
                $productData['main_image_path'] = $storagePath.'/'.$mainImagePath.'.webp';
            }

            if ($request->customFields && count($productType->fields)) {
                $productData['custom_fields'] = $this->prepareCustomFieldsToSync($request->customFields);
            }

            $product = Product::create($productData);

            if (! is_null($request->gallery)) {
                $this->mediaService->syncGallery($product->id, $request->gallery, $request->galleryColorIds);
            }
            if (! is_null($request->characteristics)) {
                $this->relationsService->syncCharacteristics($product->id, $request->characteristics);
            }
            if (! is_null($request->videos)) {
                $this->relationsService->syncVideos($product->id, $request->videos);
            }
            if (! is_null($request->attributes)) {
                $this->relationsService->syncAttributes($product->id, $request->attributes);
            }

            ProductText::updateProductShortText($product->id, $request->productShortText);
            ProductText::updateProductText($product->id, $request->productText);

            if (! is_null($request->faqs)) {
                $this->relationsService->syncFaqs($product->id, $request->faqs);
            }
            ProductSeoText::updateProductSeoText($product->id, $request->seoTitle, $request->seoText);

            // all colors
            if ($productType->has_color) {
                $this->relationsService->syncColors($request->allColorIds, $product);
            }

            // all categories
            if ($productType->has_category) {
                $product->categories()->sync($request->categoryIds);
            }

            if (! is_null($request->mainImage)) {
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
        return $this->coverWithDBTransaction(function () use ($productType, $product, $request) {
            [$contentBlocks, $contentImagesToDelete] = $this->prepareContentBlocks(
                $request->contentBlocks,
                $product->content_blocks ?? [],
            );
            $this->mediaService->syncGallery($product->id, $request->gallery, $request->galleryColorIds);
            $this->relationsService->syncCharacteristics($product->id, $request->characteristics);
            $this->relationsService->syncVideos($product->id, $request->videos);
            $this->relationsService->syncAttributes($product->id, $request->attributes);
            ProductText::updateProductShortText($product->id, $request->productShortText);
            ProductText::updateProductText($product->id, $request->productText);
            $this->relationsService->syncFaqs($product->id, $request->faqs);
            ProductSeoText::updateProductSeoText($product->id, $request->seoTitle, $request->seoText);

            $dataToUpdate = [
                //                'is_active' => $request->isActive,
                'is_active' => 0,
                'product_type_id' => $productType->id,
                'sku' => $request->sku,
                'sub_products' => $this->encodeSubProductIds($request->selectedSubProductsId),
                'name' => $request->name,
                'slug' => $request->slug,
                'created_at' => $request->createdAt,
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
                'content_blocks' => $contentBlocks ?: null,
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
                $storagePath = self::PRODUCT_IMAGES_FOLDER.'/'.date('m.Y');

                $previewImagePath = sha1(time()).'_'.Str::random(10).'_preview';
                $mainImagePath = sha1(time()).'_'.Str::random(10).'_main';

                $dataToUpdate['preview_image_path'] = $storagePath.'/'.$previewImagePath.'.webp';
                $dataToUpdate['main_image_path'] = $storagePath.'/'.$mainImagePath.'.webp';

                $mainImage['image'] = $request->mainImage;
                $mainImage['path'] = $mainImagePath;

                $previewImage['image'] = $request->mainImage;
                $previewImage['path'] = $previewImagePath;
            }

            // Remove product image
            if (! $request->mainImage && $request->mainImageDeleted) {
                $imagesToDelete[] = $product->main_image_path;
                $imagesToDelete[] = $product->preview_image_path;
                $product->main_image_path = null;
                $product->preview_image_path = null;
            }

            if ($request->customFields && count($productType->fields)) {
                $dataToUpdate['custom_fields'] = $this->prepareCustomFieldsToSync($request->customFields);
            }

            $product->update($dataToUpdate);

            // all colors
            if ($productType->has_color) {
                $this->relationsService->syncColors($request->allColorIds, $product);
            }

            // all categories
            if ($productType->has_category) {
                $product->categories()->sync($request->categoryIds);
                //                $product->categories()->sync([19]);
            }

            // store images
            if ($mainImage) {
                $this->storeProductImage($mainImage['path'], $mainImage['image'], 'webp');
                $this->storeProductImage($mainImage['path'], $mainImage['image'], 'jpg');
            }
            if ($previewImage) {
                $this->storePreviewImage($previewImage['path'], $previewImage['image'], 'webp');
                $this->storePreviewImage($previewImage['path'], $previewImage['image'], 'jpg');
            }

            // delete images
            foreach ($imagesToDelete as $imageToDelete) {
                if (! is_null($imageToDelete)) {
                    $this->mediaService->delete($imageToDelete);
                }
            }

            foreach ($contentImagesToDelete as $imageToDelete) {
                $this->deleteImage($imageToDelete);
            }

            return ServiceActionResult::make(true, trans('admin.product_edit_success'));
        });
    }

    /**
     * Normalise flexible product content and keep media lifecycle predictable.
     * Empty blocks may remain in the editor, but the storefront deliberately
     * ignores them until a translated title/body/item is actually filled.
     *
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, string>}
     */
    private function prepareContentBlocks(?array $blocks, array $existingBlocks = []): array
    {
        $existingById = collect($existingBlocks)->keyBy('id');
        $prepared = [];
        $keptImages = [];

        foreach ($blocks ?? [] as $block) {
            $id = (string) ($block['id'] ?? Str::uuid());
            $existing = $existingById->get($id, []);
            $normalised = Arr::only($block, [
                'type',
                'eyebrow',
                'title',
                'content',
                'quote',
                'author',
                'button_label',
                'button_url',
                'image_position',
                'items',
            ]);
            $normalised['id'] = $id;

            if (($block['image'] ?? null) instanceof UploadedFile) {
                $imageBasePath = self::PRODUCT_CONTENT_IMAGES_FOLDER.'/'.sha1((string) microtime(true)).'_'.Str::random(10);
                $this->storeImage($imageBasePath, $block['image'], 'webp');
                $this->storeImage($imageBasePath, $block['image'], 'jpg');
                $normalised['image_path'] = $imageBasePath.'.webp';
                $keptImages[] = $normalised['image_path'];
            } elseif (empty($block['image_deleted']) && ! empty($existing['image_path'])) {
                $normalised['image_path'] = $existing['image_path'];
                $keptImages[] = $existing['image_path'];
            }

            $prepared[] = $normalised;
        }

        $existingImages = collect($existingBlocks)->pluck('image_path')->filter()->all();

        return [$prepared, array_values(array_diff($existingImages, $keptImages))];
    }

    public function getProductCharacteristics(int $id): Collection
    {
        return $this->relationsService->getCharacteristics($id);
    }

    public function replaceTagsWithData(?string $text, Product $product): string
    {
        $allTags = [
            '%title%' => $product->name,
            '%price%' => $product->price,
            '%product_type%' => $product->productType->name,
        ];

        return str_replace(array_keys($allTags), array_values($allTags), (string) $text);
    }

    private function encodeSubProductIds(?array $ids): ?string
    {
        $normalizedIds = collect($ids ?? [])
            ->filter(static fn (mixed $id): bool => is_numeric($id) && (int) $id > 0)
            ->map(static fn (mixed $id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        return $normalizedIds === [] ? null : json_encode($normalizedIds, JSON_THROW_ON_ERROR);
    }

    public function getSelectedSubItems(?array $sub_products): Collection|array
    {
        if (! empty($sub_products)) {
            return Product::whereIn('id', $sub_products)->get();
        } else {
            return [];
        }
    }

    public function getSelectedSubItemsWithCategories(array|bool $sub_products): array
    {
        if ($sub_products) {
            $subProducts = Product::whereIn('id', $sub_products)->get();

            $categoryProducts = [];
            foreach ($subProducts as $item) {
                $categoryName = $item->categories->first()?->name ?? '—';

                if (! isset($categoryProducts[$categoryName])) {
                    $categoryProducts[$categoryName] = [];
                }

                $categoryProducts[$categoryName][] = $item;
            }

            return $categoryProducts;
        }

        return [];
    }

    public function getProductVideos(int $id): Collection
    {
        return $this->relationsService->getVideos($id);
    }

    public function getProductFaqs(int $id): Collection
    {
        return $this->relationsService->getFaqs($id);
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
            foreach ($attribute->productAttributeOptions as $attributeOption) {
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

        if (count($currentAttributeOptions)) {
            foreach ($currentAttributeOptions as $key => $attribute) {
                $atr_options = [];
                foreach ($attribute->productAttributeOptions as $attributeOption) {
                    $atr_options[] = $attributeOption;
                }
                $attributeOptions[$attribute->id][$attribute->attribute_name] = $atr_options;
            }
        }

        return $attributeOptions;
    }

    public function getProductGallery(int $id): Collection
    {
        return $this->mediaService->getGallery($id);
    }

    public function handleProductPriceInCurrency(Currency $currency, Currency $baseCurrency, float $priceInCurrency, ?float $oldPriceInCurrency): array
    {
        $baseCurrencyId = $baseCurrency->id;

        $oldPrice = null;
        if ($baseCurrencyId == $currency->id || ($baseCurrencyId != $currency->id && ! $currency->rate)) {
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
            'old_price' => $oldPrice,
        ];
    }

    public function productDelete(Product $product): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($product) {

            if (HomePageNewProducts::where('product_id', $product->id)->exists() || HomePageBestSalesProducts::where('product_id', $product->id)->exists()) {
                return ServiceActionResult::make(false, trans('admin.product_in_use_on_homepage'));
            }

            if (OrderProduct::where('product_id', $product->id)->exists()) {
                return ServiceActionResult::make(false, trans('admin.product_in_use_in_orders'));
            }

            if (count($product->productTypes) > 0) {
                $productTypesInUse = '';

                foreach ($product->productTypes as $productType) {
                    $productTypesInUse .= ' '.$productType->name.',';
                }

                $productTypesInUse = substr($productTypesInUse, 0, -1);

                return ServiceActionResult::make(false, trans('admin.product_in_use_as_additional_product_in').$productTypesInUse);
            }

            $cartProducts = CartProducts::where('product_id', $product->id)->get();
            if (count($cartProducts) >= 1) {
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

            $this->mediaService->syncGallery($product->id, [], []);
            $this->relationsService->syncCharacteristics($product->id, []);
            $this->relationsService->syncVideos($product->id, []);
            $this->relationsService->syncAttributes($product->id, []);
            ProductText::deleteProductText($product->id);
            $this->relationsService->syncFaqs($product->id, []);
            ProductSeoText::where('product_id', $product->id)->delete();

            $product->delete();

            foreach ($imagesToDelete as $imageToDelete) {
                if (! is_null($imageToDelete)) {
                    $this->mediaService->delete($imageToDelete);
                }
            }

            return ServiceActionResult::make(true, trans('admin.product_delete_success'));
        });

    }

    public function getSameTypeProducts(Product $product): Collection
    {
        return Product::where('product_type_id', $product->product_type_id)->whereNot('id', $product->id)->limit(6)->get();
    }

    public function updateProductsPriceByCurrencyRate(int $currencyId): void
    {
        $currency = Currency::find($currencyId);
        $this->coverWithDBTransactionWithoutResponse(function () use ($currency) {
            Product::where('price_currency_id', $currency->id)->chunk(500, function ($products) use ($currency) {
                if ($currency->rate) {
                    Product::whereIn('id', $products->pluck('id'))->update([
                        'price' => \DB::raw('price_in_currency * '.$currency->rate),
                    ]);
                } else {
                    Product::whereIn('id', $products->pluck('id'))->update([
                        'price' => \DB::raw('price_in_currency'),
                    ]);
                }
            });
        });
    }

    public function storePreviewImage(string $path, UploadedFile $image, string $format = 'webp', int $quality = 70): void
    {
        $this->mediaService->storePreviewImage($path, $image, $format, $quality);
    }

    public function storeProductImage(string $path, UploadedFile $image, string $format = 'webp', int $quality = 70): void
    {
        $this->mediaService->storeProductImage($path, $image, $format, $quality);
    }

    private function prepareCustomFieldsToSync(array $rawCustomFieldsArray): array
    {
        // result should be '$fieldId' => ['value' => ['$value']]
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

        if (count($seoTextData)) {
            $data['title'] = $seoTextData->where('language', $language)->first()->title;
            $data['content'] = $seoTextData->where('language', $language)->first()->content;

            return $data;
        }

        return null;
    }
}
