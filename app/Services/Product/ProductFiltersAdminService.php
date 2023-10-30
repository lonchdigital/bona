<?php

namespace App\Services\Product;

use App\DataClasses\ProductImportFilterImagesStatusesDataClass;
use App\Services\Base\BaseService;
use App\Services\Product\DTO\FilterProductAdminDTO;
use App\Services\Product\DTO\ProductImportFilterDTO;
use Illuminate\Database\Eloquent\Builder;

class ProductFiltersAdminService extends BaseService
{
    public function handleProductFilters(FilterProductAdminDTO $request, Builder $query): Builder
    {
        if ($request->search) {
            $query->where(function (Builder $query) use($request) {
                return $query->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('sku', 'like', '%' . $request->search . '%')
                    ->orWhereHas('children', function (Builder $query) use($request) {
                        return $query->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('sku', 'like', '%' . $request->search . '%');
                    });
            });
        }

        if ($request->brandId) {
            $query->where(function (Builder $query) use($request) {
                return $query->where('brand_id', $request->brandId)
                    ->orWhereHas('children', function (Builder $query) use($request) {
                        return $query->where('brand_id', $request->brandId);
                    });
            });
        }

        if ($request->colorId) {
            $query->where(function (Builder $query) use($request) {
                return $query->where('main_color_id', $request->colorId)
                    ->orWhereHas('children', function (Builder $query) use($request) {
                        return $query->where('main_color_id', $request->colorId);
                    });
            });
        }

        if ($request->collectionId) {
            $query->where(function (Builder $query) use($request) {
                return $query->where('collection_id', $request->collectionId)
                    ->orWhereHas('children', function (Builder $query) use($request) {
                        return $query->where('collection_id', $request->collectionId);
                    });
            });
        }

        if ($request->countryId) {
            $query->where(function (Builder $query) use($request) {
                return $query->where('country_id', $request->countryId)
                    ->orWhereHas('children', function (Builder $query) use($request) {
                        return $query->where('country_id', $request->countryId);
                    });
            });
        }

        if ($request->categoryId) {
            $query->whereHas('categories', function (Builder $query) use ($request) {
                return $query->where('category_id', $request->categoryId);
            });
        }

        return $query;
    }

    public function handleImportedProductsFilters(Builder $query,  ProductImportFilterDTO $request): Builder
    {
        $query = $this->handleProductFilters($request, $query);

        if ($request->mainImageStatusId === ProductImportFilterImagesStatusesDataClass::STATUS_EXISTS) {
            $query->where(function (Builder $query) use($request) {
                $query->whereNotNull('main_image_path');
            });
        } elseif ($request->mainImageStatusId === ProductImportFilterImagesStatusesDataClass::STATUS_NOT_EXISTS) {
            $query->where(function (Builder $query) use($request) {
                $query->whereNull('main_image_path');
            });
        }

        if ($request->patternImageStatusId === ProductImportFilterImagesStatusesDataClass::STATUS_EXISTS) {
            $query->where(function (Builder $query) use($request) {
                $query->whereNotNull('pattern_image_path');
            });
        } elseif ($request->patternImageStatusId === ProductImportFilterImagesStatusesDataClass::STATUS_NOT_EXISTS) {
            $query->where(function (Builder $query) use($request) {
                $query->whereNull('pattern_image_path');
            });
        }

        if ($request->galleryImage1StatusId === ProductImportFilterImagesStatusesDataClass::STATUS_EXISTS) {
            $query->where(function (Builder $query) use($request) {
                $query->whereNotNull('gallery_images->image_1');
            });
        } elseif ($request->galleryImage1StatusId === ProductImportFilterImagesStatusesDataClass::STATUS_NOT_EXISTS) {
            $query->where(function (Builder $query) use($request) {
                $query->whereNull('gallery_images->image_1');
            });
        }

        if ($request->galleryImage2StatusId === ProductImportFilterImagesStatusesDataClass::STATUS_EXISTS) {
            $query->where(function (Builder $query) use($request) {
                $query->whereNotNull('gallery_images->image_2');
            });
        } elseif ($request->galleryImage2StatusId === ProductImportFilterImagesStatusesDataClass::STATUS_NOT_EXISTS) {
            $query->where(function (Builder $query) use($request) {
                $query->whereNull('gallery_images->image_2');
            });
        }

        if ($request->galleryImage3StatusId === ProductImportFilterImagesStatusesDataClass::STATUS_EXISTS) {
            $query->where(function (Builder $query) use($request) {
                $query->whereNotNull('gallery_images->image_3');
            });
        } elseif ($request->galleryImage3StatusId === ProductImportFilterImagesStatusesDataClass::STATUS_NOT_EXISTS) {
            $query->where(function (Builder $query) use($request) {
                $query->whereNull('gallery_images->image_3');
            });
        }

        if ($request->galleryImage4StatusId === ProductImportFilterImagesStatusesDataClass::STATUS_EXISTS) {
            $query->where(function (Builder $query) use($request) {
                $query->whereNotNull('gallery_images->image_4');
            });
        } elseif ($request->galleryImage4StatusId === ProductImportFilterImagesStatusesDataClass::STATUS_NOT_EXISTS) {
            $query->where(function (Builder $query) use($request) {
                $query->whereNull('gallery_images->image_4');
            });
        }

        if ($request->galleryImage5StatusId === ProductImportFilterImagesStatusesDataClass::STATUS_EXISTS) {
            $query->where(function (Builder $query) use($request) {
                $query->whereNotNull('gallery_images->image_5');
            });
        } elseif ($request->galleryImage5StatusId === ProductImportFilterImagesStatusesDataClass::STATUS_NOT_EXISTS) {
            $query->where(function (Builder $query) use($request) {
                $query->whereNull('gallery_images->image_5');
            });
        }

        if (!($request->showNew && $request->showExisting)) {
            if ($request->showNew) {
                $query->where(function (Builder $query) {
                     $query->whereRaw('not exists (select sku from products where sku = imported_products.sku)');
                });
            }

            if ($request->showExisting) {
                $query->where(function (Builder $query) {
                    $query->whereRaw('exists (select sku from products where sku = imported_products.sku)');
                });
            }
        }

        return $query;
    }
}
