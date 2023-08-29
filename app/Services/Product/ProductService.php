<?php

namespace App\Services\Product;

use App\Models\Category;
use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\User;
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
        private readonly CurrencyService            $currencyService,
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
            'collection',
            'brand',
            'color',
            'creator',
            'children.brand',
            'children.color',
            'children.collection',
            'children.creator',
        ]);

        $query = $this->filtersAdminService->handleProductFilters($request, $query);

        $query->whereNull('parent_product_id');

        return $query->where('product_type_id', $productTypeId)
            ->paginate(config('domain.items_per_page'));
    }

    public function getBestSellersByBrandId(int $brandId): Collection
    {
        return Product::where('brand_id', $brandId)->limit(6)->orderBy('orders_count')->get();
    }

    public function getProductsByTypePaginated(ProductType $productType, FilterProductDTO $request, int $perPage, int $page): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Product::query();

        $query = $this->filterService->handleProductFilters($productType, $request->filters, $query);

        return $query->where('product_type_id', $productType->id)
            ->paginate($perPage, '*', null, $page);
    }

    public function getProductsByCollectionAndTypePaginated(\App\Models\Collection $collection, FilterProductDTO $request, int $perPage, int $page): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Product::with(['children']);

        $query = $query->where('collection_id', $collection->id);

        $query = $this->filterService->handleSortingFilter($query, $request->filters);

        $query->whereNull('parent_product_id');

        return $query->paginate($perPage, '*', null, $page);
    }

    public function getProductsByTypePaginatedByCategory(ProductType $productType, Category $category, FilterProductDTO $request, int $perPage, int $page): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Product::with(['children']);

        $query = $this->filterService->handleProductFilters($productType, $request->filters, $query);

        $query->whereNull('parent_product_id');

        $query->whereHas('categories', function (Builder $query) use($category) {
            $query->where('category_id', $category->id);
        });

        return $query->where('product_type_id', $productType->id)
            ->paginate($perPage, '*', null, $page);
    }

    public function getProductsCountByFilters(ProductType $productType, FilterProductDTO $request): array
    {
        $query = Product::query();

        //we don't need to sort this query
        $filters = $request->filters;

        $query = $this->filterService->handleProductFilters($productType, $request->filters, $query, true);

        return ['count' => $query->count()];
    }

    public function searchParentProducts(FilterProductAdminDTO $request): Collection
    {
        $query = Product::query();

        if ($request->search) {
            $query->orWhere(function (Builder $query) use($request) {
                return $query->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $query->whereNull('parent_product_id');

        return $query->limit(10)->get();
    }

    public function getProductTypeWithFields(int $productTypeId): ?ProductType
    {
        return ProductType::with('fields')->where('id', $productTypeId)->first();
    }

    public function searchProducts(SearchProductDTO $request): Collection
    {
        $query = Product::query();

        if ($request->query) {
            $query->where(function (Builder $query) use($request) {
                return $query->where('name', 'like', '%' . $request->query . '%')
                    ->orWhere('sku', 'like', '%' . $request->query . '%');
            });
        }

        return $query->limit(100)->get();
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

    public function createProduct(User $creator, ProductType $productType, EditProductDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($productType, $request, $creator) {

            $currency = Currency::find($request->currencyId);
            $baseCurrency = $this->currencyService->getBaseCurrency();

            $prices = $this->handleProductPriceInCurrency($currency, $baseCurrency, $request->priceInCurrency, $request->oldPriceInCurrency);

            $productData = [
                'is_active' => $request->isActive,
                'creator_id' => $creator->id,
                'parent_product_id' => $request->parentProductId,
                'product_type_id' => $productType->id,
                'sku' => $request->sku,
                'name' => $request->name,
                'slug' => $request->slug,
                'price' => $prices['price'],
                'old_price' => $prices['old_price'],
                'old_price_in_currency' => $request->oldPriceInCurrency,
                'price_in_currency' => $request->priceInCurrency,
                'purchase_price_in_currency' => $request->purchasePriceInCurrency,
                'price_currency_id' => $request->currencyId,
                'availability_status_id' => $request->availabilityStatusId,
                'country_id' => $request->countryId,
                'meta_title' => $request->metaTitle,
                'meta_description' => $request->metaDescription,
                'meta_keywords' => $request->metaKeyWords,
                'length' => $request->length,
                'width' => $request->width,
                'height' => $request->height,
                'special_offers' => $request->specialOfferIds,
            ];

            if ($productType->has_color) {
                $productData['main_color_id'] = $request->colorId;
            }

            if ($productType->has_brand || $productType->has_collection) {
                $productData['brand_id'] = $request->brandId;
            }

            if ($productType->has_collection) {
                $productData['collection_id'] = $request->collectionId;
            }

            //handle images
            $previewImagePath = self::PRODUCT_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_preview.jpg';
            $mainImagePath = self::PRODUCT_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_main.jpg';
            $patternImagePath = self::PRODUCT_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_pattern.jpg';

            $productData['preview_image_path'] = $previewImagePath;
            $productData['main_image_path'] = $mainImagePath;
            $productData['pattern_image_path'] = $patternImagePath;

            $galleryImages = [];
            if ($request->galleryImage1) {
                $galleryImage1Path = self::PRODUCT_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_gallery_image_1.jpg';
                $galleryImages['image_1'] = $galleryImage1Path;
            }

            if ($request->galleryImage2) {
                $galleryImage1Path = self::PRODUCT_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_gallery_image_2.jpg';
                $galleryImages['image_2'] = $galleryImage1Path;
            }

            if ($request->galleryImage3) {
                $galleryImage1Path = self::PRODUCT_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_gallery_image_3.jpg';
                $galleryImages['image_3'] = $galleryImage1Path;
            }

            if ($request->galleryImage4) {
                $galleryImage1Path = self::PRODUCT_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_gallery_image_4.jpg';
                $galleryImages['image_4'] = $galleryImage1Path;
            }

            if ($request->galleryImage5) {
                $galleryImage1Path = self::PRODUCT_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_gallery_image_5.jpg';
                $galleryImages['image_5'] = $galleryImage1Path;
            }

            if ($galleryImages) {
                $productData['gallery_images'] = $galleryImages;
            } else {
                $productData['gallery_images'] = [];
            }

            if ($request->customFields && count($productType->fields)) {
                $productData['custom_fields'] = $this->prepareCustomFieldsToSync($request->customFields);
            }

            $product = Product::create($productData);

            //all colors
            if ($productType->has_color) {
                $product->colors()->sync($request->allColorIds);
            }

            //all categories
            if ($productType->has_category) {
                $product->categories()->sync($request->categoryIds);
            }

            $this->storePreviewImage($previewImagePath, $request->mainImage);
            $this->storeProductImage($mainImagePath, $request->mainImage);
            $this->storeProductImage($patternImagePath, $request->patternImage);

            if (isset($galleryImages['image_1'])) {
                $this->storeProductImage($galleryImages['image_1'], $request->galleryImage1);
            }

            if (isset($galleryImages['image_2'])) {
                $this->storeProductImage($galleryImages['image_2'], $request->galleryImage2);
            }

            if (isset($galleryImages['image_3'])) {
                $this->storeProductImage($galleryImages['image_3'], $request->galleryImage3);
            }

            if (isset($galleryImages['image_4'])) {
                $this->storeProductImage($galleryImages['image_4'], $request->galleryImage4);
            }

            if (isset($galleryImages['image_5'])) {
                $this->storeProductImage($galleryImages['image_5'], $request->galleryImage5);
            }

            return ServiceActionResult::make(true, trans('admin.product_create_success'));
        });
    }

    public function productEdit(ProductType $productType, Product $product, EditProductDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($productType, $product, $request) {

            $currency = Currency::find($request->currencyId);
            $baseCurrency = $this->currencyService->getBaseCurrency();

            $prices = $this->handleProductPriceInCurrency($currency, $baseCurrency, $request->priceInCurrency, $request->oldPriceInCurrency);

            $dataToUpdate = [
                'is_active' => $request->isActive,
                'parent_product_id' => $request->parentProductId,
                'product_type_id' => $productType->id,
                'sku' => $request->sku,
                'name' => $request->name,
                'slug' => $request->slug,
                'price' => $prices['price'],
                'old_price' => $prices['old_price'],
                'old_price_in_currency' => $request->oldPriceInCurrency,
                'price_in_currency' => $request->priceInCurrency,
                'purchase_price_in_currency' => $request->purchasePriceInCurrency,
                'price_currency_id' => $request->currencyId,
                'availability_status_id' => $request->availabilityStatusId,
                'country_id' => $request->countryId,
                'meta_title' => $request->metaTitle,
                'meta_description' => $request->metaDescription,
                'meta_keywords' => $request->metaKeyWords,
                'length' => $request->length,
                'width' => $request->width,
                'height' => $request->height,
                'special_offers' => $request->specialOfferIds,
            ];

            if ($productType->has_color) {
                $dataToUpdate['main_color_id'] = $request->colorId;
            }

            if ($productType->has_brand || $productType->has_collection) {
                $dataToUpdate['brand_id'] = $request->brandId;
            }

            if ($productType->has_collection) {
                $dataToUpdate['collection_id'] = $request->collectionId;
            }

            //main image and pattern image can't be deleted but replaced

            $imagesToStore = [];
            $imagesToDelete = [];
            $previewImage = null;
            if ($request->mainImage) {
                $imagesToDelete[] = $product->main_image_path;
                $imagesToDelete[] = $product->preview_image_path;

                $mainImagePath = self::PRODUCT_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_main.jpg';
                $previewImagePath = self::PRODUCT_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_preview.jpg';

                $dataToUpdate['main_image_path'] = $mainImagePath;
                $dataToUpdate['preview_image_path'] = $previewImagePath;

                $imagesToStore[] = [
                    'image' => $request->mainImage,
                    'path' => $mainImagePath
                ];

                $previewImage['image'] = $request->mainImage;
                $previewImage['path'] = $previewImagePath;
            }

            if ($request->patternImage) {
                $imagesToDelete[] = $product->pattern_image_path;

                $patternImagePath = self::PRODUCT_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_pattern.jpg';

                $dataToUpdate['pattern_image_path'] = $patternImagePath;

                $imagesToStore[] = [
                    'image' => $request->patternImage,
                    'path' => $patternImagePath
                ];
            }

            $galleryImages = $product->gallery_images;

            if ($request->galleryImage1) {
                if (isset($galleryImages['image_1'])) {
                    $imagesToDelete[] = $galleryImages['image_1'];
                }

                $galleryImage1Path = self::PRODUCT_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_gallery_image_1.jpg';

                $galleryImages['image_1'] = $galleryImage1Path;

                $imagesToStore[] = [
                    'image' => $request->galleryImage1,
                    'path' => $galleryImage1Path
                ];
            }

            if ($request->galleryImage2) {
                if (isset($galleryImages['image_2'])) {
                    $imagesToDelete[] = $galleryImages['image_2'];
                }

                $galleryImage2Path = self::PRODUCT_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_gallery_image_2.jpg';

                $galleryImages['image_2'] = $galleryImage2Path;

                $imagesToStore[] = [
                    'image' => $request->galleryImage2,
                    'path' => $galleryImage2Path
                ];
            }

            if ($request->galleryImage3) {
                if (isset($galleryImages['image_3'])) {
                    $imagesToDelete[] = $galleryImages['image_3'];
                }

                $galleryImage3Path = self::PRODUCT_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_gallery_image_3.jpg';

                $galleryImages['image_3'] = $galleryImage3Path;

                $imagesToStore[] = [
                    'image' => $request->galleryImage3,
                    'path' => $galleryImage3Path
                ];
            }

            if ($request->galleryImage4) {
                if (isset($galleryImages['image_4'])) {
                    $imagesToDelete[] = $galleryImages['image_4'];
                }

                $galleryImage4Path = self::PRODUCT_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_gallery_image_4.jpg';

                $galleryImages['image_4'] = $galleryImage4Path;

                $imagesToStore[] = [
                    'image' => $request->galleryImage4,
                    'path' => $galleryImage4Path
                ];
            }

            if ($request->galleryImage5) {
                if (isset($galleryImages['image_5'])) {
                    $imagesToDelete[] = $galleryImages['image_5'];
                }

                $galleryImage5Path = self::PRODUCT_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_gallery_image_5.jpg';

                $galleryImages['image_5'] = $galleryImage5Path;

                $imagesToStore[] = [
                    'image' => $request->galleryImage5,
                    'path' => $galleryImage5Path
                ];
            }

            if ($request->galleryImage1Deleted && isset($galleryImages['image_1'])) {
                $imagesToDelete[] = $galleryImages['image_1'];
                unset($galleryImages['image_1']);
            }

            if ($request->galleryImage2Deleted && isset($galleryImages['image_2'])) {
                $imagesToDelete[] = $galleryImages['image_2'];
                unset($galleryImages['image_2']);
            }

            if ($request->galleryImage3Deleted && isset($galleryImages['image_3'])) {
                $imagesToDelete[] = $galleryImages['image_3'];
                unset($galleryImages['image_3']);
            }

            if ($request->galleryImage4Deleted && isset($galleryImages['image_4'])) {
                $imagesToDelete[] = $galleryImages['image_4'];
                unset($galleryImages['image_4']);
            }

            if ($request->galleryImage5Deleted && isset($galleryImages['image_5'])) {
                $imagesToDelete[] = $galleryImages['image_5'];
                unset($galleryImages['image_5']);
            }

            $dataToUpdate['gallery_images'] = $galleryImages;

            if ($request->customFields && count($productType->fields)) {
                $dataToUpdate['custom_fields'] = $this->prepareCustomFieldsToSync($request->customFields);
            }

            $product->update($dataToUpdate);

            //all colors
            if ($productType->has_color) {
                $product->colors()->sync($request->allColorIds);
            }

            //all categories
            if ($productType->has_category) {
                $product->categories()->sync($request->categoryIds);
            }

            //store images
            foreach ($imagesToStore as $imageToStore) {
                $this->storeProductImage($imageToStore['path'], $imageToStore['image']);
            }

            if ($previewImage) {
                $this->storePreviewImage($previewImage['path'], $previewImage['image']);
            }


            //delete images
            foreach ($imagesToDelete as $imageToDelete) {
                $this->deleteProductImage($imageToDelete);
            }

            return ServiceActionResult::make(true, trans('admin.product_edit_success'));
        });
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
            if (count($product->children)) {
                $newParent = $product->children->first();

                $newParent->update([
                    'parent_product_id' => null,
                ]);

                $otherChildren = $product->children->where('id', '!=', $newParent->id)->pluck('id');

                Product::whereIn('id', $otherChildren)->update([
                    'parent_product_id' => $newParent->id,
                ]);
            }

            $imagesToDelete = [];
            if ($product->main_image_path) {
                $imagesToDelete[] = $product->main_image_path;
            }

            if (isset($product->pattern_image_path)) {
                $imagesToDelete[] = $product->pattern_image_path;
            }

            if (isset($product->gallery_images['image_1'])) {
                $imagesToDelete[] = $product->gallery_images['image_1'];
            }

            if (isset($product->gallery_images['image_2'])) {
                $imagesToDelete[] = $product->gallery_images['image_2'];
            }

            if (isset($product->gallery_images['image_3'])) {
                $imagesToDelete[] = $product->gallery_images['image_3'];
            }

            if (isset($product->gallery_images['image_4'])) {
                $imagesToDelete[] = $product->gallery_images['image_4'];
            }

            if (isset($product->gallery_images['image_5'])) {
                $imagesToDelete[] = $product->gallery_images['image_5'];
            }

            $product->colors()->sync([]);
            $product->categories()->sync([]);

            $product->delete();

            foreach ($imagesToDelete as $imageToDelete) {
                $this->deleteProductImage($imageToDelete);
            }

            return ServiceActionResult::make(true, trans('admin.product_delete_success'));
        });

    }

    public function getProductsBySameCollection(Product $product): Collection
    {
        $collectionId = $product->collection_id;
        $productsToExclude = [];

        if ($product->parent_product_id) {
            $productsToExclude[] = $product->parent_product_id;
            $productsToExclude = array_merge($product->parent->children->pluck('id')->toArray(), $productsToExclude);
        } else {
            $productsToExclude = $product->children->pluck('id')->toArray();
        }

        $query = Product::where('collection_id', $collectionId)
            ->where('product_type_id', $product->product_type_id)
            ->whereNot('id', $product->id)
            ->whereNotIn('id', $productsToExclude)
            ->whereNull('parent_product_id')
            ->limit(6);

            $query = $this->filterService->handleSortByPopularity($query);

        return $query->get();
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

    public function storePreviewImage(string $path, UploadedFile $image): void
    {
        $image = Image::make($image);

        $squareSize = $image->width();

        if ($image->height() < $squareSize) {
            $squareSize = $image->height();
        }

        $image = $image->crop($squareSize, $squareSize)
            ->resize(320, 320)
            ->encode('jpg', 90);

        Storage::disk(config('app.images_disk_default'))
            ->put($path, $image);
    }

    public function storeProductImage(string $path, UploadedFile $image): void
    {
        $image = Image::make($image)->encode('jpg', 85);

        Storage::disk(config('app.images_disk_default'))->put($path, $image);
    }

    public function deleteProductImage(string $imagePath): void
    {
        if (Storage::disk(config('app.images_disk_default'))->exists($imagePath)) {
            Storage::disk(config('app.images_disk_default'))->delete($imagePath);
        }
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
}
