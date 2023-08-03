<?php

namespace App\Services\Product;

use App\Jobs\RegenerateSitemapJob;
use App\Jobs\UpdateCountOfProductsByCategoryJob;
use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\User;
use App\Services\Currency\CurrencyService;
use App\Services\Product\DTO\ProductImportFilterDTO;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use App\Models\ImportedProduct;
use Illuminate\Http\UploadedFile;
use App\Services\Base\BaseService;
use Illuminate\Support\Facades\Storage;
use App\Services\Base\ServiceActionResult;
use App\DataClasses\ImportedProductImageTypesDataClass;
use App\Services\Product\DTO\RemoveProductImportImageDTO;
use App\Services\Product\DTO\UploadImportedProductImageDTO;
use Laravel\Telescope\Telescope;

class ProductImportService extends BaseService
{
    public function __construct(
        private readonly ProductService             $productService,
        private readonly CurrencyService            $currencyService,
        private readonly ProductFiltersAdminService $productFiltersAdminService,
    ) { }

    public function importedProductsExists(ProductType $productType): bool
    {
        return ImportedProduct::where('product_type_id', $productType->id)->exists();
    }

    public function getImportedProductsByProductTypePaginated(ProductType $productType, ProductImportFilterDTO $request): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $paginatedListQuery = ImportedProduct::where('product_type_id', $productType->id);

        $paginatedListQuery = $this->productFiltersAdminService->handleImportedProductsFilters($paginatedListQuery, $request);

        $paginatedList = $paginatedListQuery->paginate(100);

        $existingProducts = Product::whereIn('sku', $paginatedList->getCollection()->pluck('sku'))->get();

        $paginatedList->getCollection()->map(function ($importedProduct) use ($existingProducts) {
            $importedProduct->is_existing_product = $existingProducts->contains('sku', $importedProduct->sku);

            $importedProduct->children->map(function ($importedProductChild) {
                return $importedProductChild->is_existing_product = Product::where('sku', $importedProductChild->sku)->exists();
            });

            return $importedProduct;
        });

        return $paginatedList;
    }

    public function uploadProductImportImage(ImportedProduct $importedProduct, UploadImportedProductImageDTO $request): ServiceActionResult
    {
        return $this->coverWithTryCatch(function () use($importedProduct, $request) {
            switch ($request->typeId) {
                case ImportedProductImageTypesDataClass::TYPE_MAIN_IMAGE:

                    $mainImagePath = ProductService::PRODUCT_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_main.jpg';
                    $this->productService->storeProductImage($mainImagePath, $request->image);

                    $previewImagePath = ProductService::PRODUCT_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_preview.jpg';
                    $this->productService->storePreviewImage($previewImagePath, $request->image);

                    $importedProduct->update([
                        'main_image_path' => $mainImagePath,
                        'preview_image_path' => $previewImagePath,
                    ]);


                    break;
                case ImportedProductImageTypesDataClass::TYPE_PATTERN_IMAGE:
                    $patternImagePath = ProductService::PRODUCT_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_pattern.jpg';
                    $this->productService->storeProductImage($patternImagePath, $request->image);
                    $importedProduct->update([
                        'pattern_image_path' => $patternImagePath,
                    ]);
                    break;
                case ImportedProductImageTypesDataClass::TYPE_GALLERY_IMAGE_1:
                    $galleryImage1Path = ProductService::PRODUCT_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_gallery_image_1.jpg';
                    $this->productService->storeProductImage($galleryImage1Path, $request->image);

                    $galleryImages = $importedProduct->gallery_images;
                    $galleryImages['image_1'] = $galleryImage1Path;

                    $importedProduct->update([
                        'gallery_images' => $galleryImages,
                    ]);
                    break;
                case ImportedProductImageTypesDataClass::TYPE_GALLERY_IMAGE_2:
                    $galleryImage2Path = ProductService::PRODUCT_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_gallery_image_2.jpg';
                    $this->productService->storeProductImage($galleryImage2Path, $request->image);

                    $galleryImages = $importedProduct->gallery_images;
                    $galleryImages['image_2'] = $galleryImage2Path;

                    $importedProduct->update([
                        'gallery_images' => $galleryImages,
                    ]);

                    break;
                case ImportedProductImageTypesDataClass::TYPE_GALLERY_IMAGE_3:
                    $galleryImage3Path = ProductService::PRODUCT_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_gallery_image_3.jpg';
                    $this->productService->storeProductImage($galleryImage3Path, $request->image);

                    $galleryImages = $importedProduct->gallery_images;
                    $galleryImages['image_3'] = $galleryImage3Path;

                    $importedProduct->update([
                        'gallery_images' => $galleryImages,
                    ]);

                    break;
                case ImportedProductImageTypesDataClass::TYPE_GALLERY_IMAGE_4:
                    $galleryImage4Path = ProductService::PRODUCT_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_gallery_image_4.jpg';
                    $this->productService->storeProductImage($galleryImage4Path, $request->image);

                    $galleryImages = $importedProduct->gallery_images;
                    $galleryImages['image_4'] = $galleryImage4Path;

                    $importedProduct->update([
                        'gallery_images' => $galleryImages,
                    ]);

                    break;
                case ImportedProductImageTypesDataClass::TYPE_GALLERY_IMAGE_5:
                    $galleryImage5Path = ProductService::PRODUCT_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_gallery_image_5.jpg';
                    $this->productService->storeProductImage($galleryImage5Path, $request->image);

                    $galleryImages = $importedProduct->gallery_images;
                    $galleryImages['image_5'] = $galleryImage5Path;

                    $importedProduct->update([
                        'gallery_images' => $galleryImages,
                    ]);

                    break;
            }

            return ServiceActionResult::make(true, trans('admin.products_import_image_upload_success'));

        });
    }

    public function removeProductImportImage(ImportedProduct $importedProduct, RemoveProductImportImageDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($importedProduct, $request) {

            switch ($request->typeId) {
                case ImportedProductImageTypesDataClass::TYPE_MAIN_IMAGE:
                    $this->productService->deleteProductImage($importedProduct->preview_image_path);
                    $this->productService->deleteProductImage($importedProduct->main_image_path);
                    $importedProduct->update(['main_image_path' => null, 'preview_image_path' => null]);
                    break;
                case ImportedProductImageTypesDataClass::TYPE_PATTERN_IMAGE:
                    $this->productService->deleteProductImage($importedProduct->pattern_image_path);
                    $importedProduct->update(['pattern_image_path' => null]);
                    break;
                case ImportedProductImageTypesDataClass::TYPE_GALLERY_IMAGE_1:
                    $this->productService->deleteProductImage($importedProduct->gallery_images['image_1']);
                    $galleryImages = $importedProduct->gallery_images;
                    unset($galleryImages['image_1']);
                    $importedProduct->update(['gallery_images' => $galleryImages]);
                    break;
                case ImportedProductImageTypesDataClass::TYPE_GALLERY_IMAGE_2:
                    $this->productService->deleteProductImage($importedProduct->gallery_images['image_2']);
                    $galleryImages = $importedProduct->gallery_images;
                    unset($galleryImages['image_2']);
                    $importedProduct->update(['gallery_images' => $galleryImages]);
                    break;
                case ImportedProductImageTypesDataClass::TYPE_GALLERY_IMAGE_3:
                    $this->productService->deleteProductImage($importedProduct->gallery_images['image_3']);
                    $galleryImages = $importedProduct->gallery_images;
                    unset($galleryImages['image_3']);
                    $importedProduct->update(['gallery_images' => $galleryImages]);
                    break;
                case ImportedProductImageTypesDataClass::TYPE_GALLERY_IMAGE_4:
                    $this->productService->deleteProductImage($importedProduct->gallery_images['image_4']);
                    $galleryImages = $importedProduct->gallery_images;
                    unset($galleryImages['image_4']);
                    $importedProduct->update(['gallery_images' => $galleryImages]);
                    break;
                case ImportedProductImageTypesDataClass::TYPE_GALLERY_IMAGE_5:
                    $this->productService->deleteProductImage($importedProduct->gallery_images['image_5']);
                    $galleryImages = $importedProduct->gallery_images;
                    unset($galleryImages['image_5']);
                    $importedProduct->update(['gallery_images' => $galleryImages]);
                    break;
            }

            return ServiceActionResult::make(true, trans('admin.products_import_image_remove_success'));
        });
    }

    public function deleteImportedProducts(ProductType $productType, bool $deleteImages): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($productType, $deleteImages) {
            ImportedProduct::where('product_type_id', $productType->id)->chunk(100, function ($chunk) use($deleteImages) {
                if ($deleteImages) {
                    foreach ($chunk as $importedProduct) {
                        if ($importedProduct->main_image_path) {
                            $this->productService->deleteProductImage($importedProduct->main_image_path);
                        }

                        if ($importedProduct->preview_image_path) {
                            $this->productService->deleteProductImage($importedProduct->preview_image_path);
                        }

                        if ($importedProduct->pattern_image_path) {
                            $this->productService->deleteProductImage($importedProduct->pattern_image_path);
                        }

                        if ($importedProduct->gallery_images) {
                            foreach ($importedProduct->gallery_images as $galleryImagePath) {
                                $this->productService->deleteProductImage($galleryImagePath);
                            }
                        }
                    }
                }

                \DB::table('imported_product_categories')->whereIn('imported_product_id', $chunk->pluck('id'))->delete();
                \DB::table('imported_product_colors')->whereIn('imported_product_id', $chunk->pluck('id'))->delete();
            });

            ImportedProduct::where('product_type_id', $productType->id)->whereNotNull('parent_product_id')->delete();
            ImportedProduct::where('product_type_id', $productType->id)->whereNull('parent_product_id')->delete();

            return ServiceActionResult::make(true, trans('admin.products_import_list_clean_success'));
        });
    }

    public function saveImportedProducts(ProductType $productType, User $creator): ServiceActionResult
    {
        if (!$this->validateNewProductImages($productType)) {
            return ServiceActionResult::make(false, trans('admin.products_import_new_products_images_required'));
        }

        $baseCurrency = $this->currencyService->getBaseCurrency();

        return $this->coverWithDBTransaction(function () use($productType, $creator, $baseCurrency) {
            Telescope::stopRecording();
            ImportedProduct::with(['colors', 'categories', 'currency'])
                ->where('product_type_id', $productType->id)
                ->whereNull('parent_product_id')
                ->chunk(100, function ($chunk) use($creator, $baseCurrency) {

                    $existingProducts = Product::with(['colors', 'categories', 'currency'])->whereIn('sku', $chunk->pluck('sku'))->get();

                    foreach ($chunk as $importedProduct) {
                        $existingProduct = $existingProducts->where('sku', $importedProduct->sku)->first();

                        if ($existingProduct) {
                            \DB::table('product_categories')->whereIn('product_id', $existingProducts->pluck('id'))->delete();
                            \DB::table('product_colors')->whereIn('product_id', $existingProducts->pluck('id'))->delete();
                            $this->handleExistingProductUpdate($existingProduct, $importedProduct, $baseCurrency);
                        } else {
                            $this->handleNewProductCreate($importedProduct, $creator, $baseCurrency);
                        }
                    }
                });

            $this->deleteImportedProducts($productType, false);
            Telescope::startRecording();

            UpdateCountOfProductsByCategoryJob::dispatchAfterResponse();
            RegenerateSitemapJob::dispatchAfterResponse();

            return ServiceActionResult::make(true, trans('admin.products_import_save_success'));
        });
    }

    public function deleteImportedProduct(ImportedProduct $importedProduct): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($importedProduct) {
            if (count($importedProduct->children)) {
                return ServiceActionResult::make(false, trans('admin.cant_delete_imported_product_have_child_products'));
            }

            $imagesToDelete = [];
            if ($importedProduct->main_image_path) {
                $imagesToDelete[] = $importedProduct->main_image_path;
            }

            if (isset($importedProduct->pattern_image_path)) {
                $imagesToDelete[] = $importedProduct->pattern_image_path;
            }

            if (isset($importedProduct->gallery_images['image_1'])) {
                $imagesToDelete[] = $importedProduct->gallery_images['image_1'];
            }

            if (isset($importedProduct->gallery_images['image_2'])) {
                $imagesToDelete[] = $importedProduct->gallery_images['image_2'];
            }

            if (isset($importedProduct->gallery_images['image_3'])) {
                $imagesToDelete[] = $importedProduct->gallery_images['image_3'];
            }

            if (isset($importedProduct->gallery_images['image_4'])) {
                $imagesToDelete[] = $importedProduct->gallery_images['image_4'];
            }

            if (isset($importedProduct->gallery_images['image_5'])) {
                $imagesToDelete[] = $importedProduct->gallery_images['image_5'];
            }

            $importedProduct->colors()->sync([]);
            $importedProduct->categories()->sync([]);

            $importedProduct->delete();

            foreach ($imagesToDelete as $imageToDelete) {
                $this->productService->deleteProductImage($imageToDelete);
            }

            return ServiceActionResult::make(true, trans('admin.products_import_delete_success'));
        });
    }

    private function validateNewProductImages(ProductType $productType): bool
    {
        $result = true;
        ImportedProduct::where('product_type_id', $productType->id)->chunk(100, function (Collection $chunk) use(&$result) {
            if ($result) {
                $existingProducts = Product::with(['colors', 'categories'])->whereIn('sku', $chunk->pluck('sku'))->get();

                foreach ($chunk as $importedProduct) {
                    if (!$existingProducts->contains('sku', $importedProduct->sku) &&
                        !$importedProduct->main_image_path && !$importedProduct->pattern_image_path) {
                        $result = false;
                    }

                    if (!$result) {
                        break;
                    }
                }
            }
        });

        return $result;
    }

    private function handleExistingProductUpdate(Product $existingProduct, ImportedProduct $importedProduct, Currency $baseCurrency): void
    {
        $parentProduct = $this->updateExistingProduct($existingProduct, $importedProduct, $baseCurrency);
        foreach ($parentProduct->children as $childProduct) {
            $importedChildProduct = $importedProduct->children->where('sku', $childProduct->sku)->first();
            if ($importedChildProduct) {
                $this->updateExistingProduct($childProduct, $importedChildProduct, $baseCurrency);
            }
        }
    }

    private function updateExistingProduct(Product $existingProduct, ImportedProduct $importedProduct, Currency $baseCurrency): Product
    {
        $prices = $this->productService->handleProductPriceInCurrency($importedProduct->currency, $baseCurrency, $importedProduct->price_in_currency, $importedProduct->old_price_in_currency);

        $dataToUpdate = [
            'name' => $importedProduct->getTranslations('name'),
            'slug' => $importedProduct->slug,
            'price' => $prices['price'],
            'old_price' => $prices['old_price'],
            'price_in_currency' => $importedProduct->price_in_currency,
            'purchase_price_in_currency' => $importedProduct->purchase_price_in_currency,
            'price_currency_id' => $importedProduct->price_currency_id,
            'availability_status_id' => $importedProduct->availability_status_id,
            'special_offers' => $importedProduct->special_offers,
            'main_color_id' => $importedProduct->main_color_id,
            'brand_id' => $importedProduct->brand_id,
            'collection_id' => $importedProduct->collection_id,
            'country_id' => $importedProduct->country_id,
            'meta_title' => $importedProduct->getTranslations('meta_title'),
            'meta_description' => $importedProduct->getTranslations('meta_description'),
            'meta_keywords' => $importedProduct->getTranslations('meta_keywords'),
            'length' => $importedProduct->length,
            'width' => $importedProduct->width,
            'height' => $importedProduct->height,
            'custom_fields' => $importedProduct->custom_fields,
        ];

        //handle images
        if ($importedProduct->main_image_path) {
            $dataToUpdate['main_image_path'] = $importedProduct->main_image_path;
            $this->productService->deleteProductImage($existingProduct->main_image_path);
            $this->productService->deleteProductImage($existingProduct->preview_image_path);
        }

        if ($importedProduct->pattern_image_path) {
            $this->productService->deleteProductImage($existingProduct->pattern_image_path);
            $dataToUpdate['pattern_image_path'] = $importedProduct->pattern_image_path;
        }

        $galleryImages = $existingProduct->gallery_images;
        if ($importedProduct->gallery_image_1_url) {
            if (isset($existingProduct->gallery_images['image_1'])) {
                $this->productService->deleteProductImage($existingProduct->gallery_images['image_1']);
            }

            $galleryImages['image_1'] = $importedProduct->gallery_images['image_1'];
            $dataToUpdate['gallery_images'] = $galleryImages;
        }

        if ($importedProduct->gallery_image_2_url && isset($existingProduct->gallery_images['image_2'])) {
            if (isset($existingProduct->gallery_images['image_2'])) {
                $this->productService->deleteProductImage($existingProduct->gallery_images['image_2']);
            }

            $galleryImages['image_2'] = $importedProduct->gallery_images['image_2'];
            $dataToUpdate['gallery_images'] = $galleryImages;
        }

        if ($importedProduct->gallery_image_3_url && isset($existingProduct->gallery_images['image_3'])) {
            if (isset($existingProduct->gallery_images['image_3'])) {
                $this->productService->deleteProductImage($existingProduct->gallery_images['image_3']);
            }

            $galleryImages['image_3'] = $importedProduct->gallery_images['image_3'];
            $dataToUpdate['gallery_images'] = $galleryImages;
        }

        if ($importedProduct->gallery_image_4_url && isset($existingProduct->gallery_images['image_4'])) {
            if (isset($existingProduct->gallery_images['image_4'])) {
                $this->productService->deleteProductImage($existingProduct->gallery_images['image_4']);
            }

            $galleryImages['image_4'] = $importedProduct->gallery_images['image_4'];
            $dataToUpdate['gallery_images'] = $galleryImages;
        }

        if ($importedProduct->gallery_image_5_url && isset($existingProduct->gallery_images['image_5'])) {
            if (isset($existingProduct->gallery_images['image_5'])) {
                $this->productService->deleteProductImage($existingProduct->gallery_images['image_5']);
            }

            $galleryImages['image_5'] = $importedProduct->gallery_images['image_5'];
            $dataToUpdate['gallery_images'] = $galleryImages;
        }

        $existingProduct->update($dataToUpdate);
        $existingProduct->colors()->attach($importedProduct->colors->pluck('id'));
        $existingProduct->categories()->attach($importedProduct->categories->pluck('id'));

        return $existingProduct;
    }

    private function handleNewProductCreate(ImportedProduct $importedProduct, User $creator, Currency $baseCurrency): void
    {
        $parentProduct = $this->createNewProduct($importedProduct, $creator, $baseCurrency);
        foreach ($importedProduct->children as $childProduct) {
            $this->createNewProduct($childProduct, $creator, $baseCurrency, $parentProduct);
        }
    }

    private function createNewProduct(ImportedProduct $importedProduct, User $creator, Currency $baseCurrency, ?Product $parentProduct = null): Product
    {
        $prices = $this->productService->handleProductPriceInCurrency($importedProduct->currency, $baseCurrency, $importedProduct->price_in_currency, $importedProduct->old_price_in_currency);

        $data = [
            'is_active' => true,
            'product_type_id' => $importedProduct->product_type_id,
            'sku' => $importedProduct->sku,
            'creator_id' => $creator->id,
            'name' => $importedProduct->getTranslations('name'),
            'slug' => $importedProduct->slug,
            'price' => $prices['price'],
            'old_price' => $prices['old_price'],
            'price_in_currency' => $importedProduct->price_in_currency,
            'purchase_price_in_currency' => $importedProduct->purchase_price_in_currency,
            'price_currency_id' => $importedProduct->price_currency_id,
            'availability_status_id' => $importedProduct->availability_status_id,
            'special_offers' => $importedProduct->special_offers,
            'main_color_id' => $importedProduct->main_color_id,
            'brand_id' => $importedProduct->brand_id,
            'collection_id' => $importedProduct->collection_id,
            'country_id' => $importedProduct->country_id,
            'meta_title' => $importedProduct->getTranslations('meta_title'),
            'meta_description' => $importedProduct->getTranslations('meta_description'),
            'meta_keywords' => $importedProduct->getTranslations('meta_keywords'),
            'length' => $importedProduct->length,
            'width' => $importedProduct->width,
            'height' => $importedProduct->height,
            'custom_fields' => $importedProduct->custom_fields,
            'main_image_path' => $importedProduct->main_image_path,
            'pattern_image_path' => $importedProduct->pattern_image_path,
            'gallery_images' => $importedProduct->gallery_images ?? [],
            'preview_image_path' => $importedProduct->preview_image_path,
        ];

        if ($parentProduct) {
            $data['parent_product_id'] = $parentProduct->id;
        }

        return Product::withoutEvents(function () use ($data, $importedProduct){
            $product = Product::create($data);
            $product->colors()->attach($importedProduct->colors->pluck('id'));
            $product->categories()->attach($importedProduct->categories->pluck('id'));

            return $product;
        });
    }
}
