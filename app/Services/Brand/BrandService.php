<?php

namespace App\Services\Brand;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\User;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\Brand\DTO\EditBrandDTO;
use App\Services\Brand\DTO\SearchBrandDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;


class BrandService extends BaseService
{
    const BRAND_IMAGES_FOLDER = 'brand-images';

    public function getBrands(): Collection
    {
        return Brand::get();
    }

    public function getBrandsPaginated(): LengthAwarePaginator
    {
        return Brand::with('creator')->paginate(config('domain.items_per_page'));
    }

    public function getDiscoverBrands(Brand $brand): Collection
    {
        return Brand::where('id', '!=', $brand->id)->orderByRaw('RAND()')->limit(4)->get();
    }

    public function getBrandsByFirstLetter(?string $letter = null, string $availableFirstLetter): Collection
    {
        $query = Brand::query();

        if (!$letter) {
            $letter = $availableFirstLetter;
        }

        if ($letter === 'all' || $letter === null) {
            return $query->get();
        } else {
            return $query->where('name', 'like', '%"' . $letter . '%')->get();
        }
    }

    public function searchBrandsByName(SearchBrandDTO $request): Collection
    {
        $query = Brand::query();

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        return $query->get();
    }

    public function sortBrandsByFirstLetterByProductType(Collection $brands): Collection
    {
        $letters = collect();

        foreach ($brands->sortBy('name') as $brand) {
            $firstLetter = mb_substr($brand->name, 0, 1);
            if(!$letters->has($firstLetter)) {
                $letters->put($firstLetter, collect([$brand]));
            } else {
                $letters[$firstLetter]->push($brand);
            }
        }

        return $letters;
    }

    public function getAvailableBrandsByProductType(ProductType $productType): Collection
    {
        //implement with cache
        return Brand::get();
    }

    public function createBrand(User $creator, EditBrandDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($request, $creator) {

            $dataToUpdate = [
                'meta_title' => $request->metaTitle,
                'meta_description' => $request->metaDescription,
                'meta_keywords' => $request->metaKeyWords,
                'creator_id' => $creator->id,
                'name' => $request->name,
                'slug' => $request->slug,
                'description' => $request->description,
            ];

            if( !is_null($request->logo) ) {
                $logoPath = self::BRAND_IMAGES_FOLDER . '/' . sha1(time()) . '_' . Str::random(10);

                $this->storeImage($logoPath, $request->logo, 'webp');
                $this->storeImage($logoPath, $request->logo, 'png');

                $dataToUpdate['logo_image_path'] = $logoPath . '.webp';
            }

            Brand::create($dataToUpdate);

            return ServiceActionResult::make(true, trans('admin.brand_create_success'));
        });
    }

    public function editBrand(Brand $brand, EditBrandDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($brand, $request) {
            $fieldsToUpdate = [
                'meta_title' => $request->metaTitle,
                'meta_description' => $request->metaDescription,
                'meta_keywords' => $request->metaKeyWords,
                'name' => $request->name,
                'slug' => $request->slug,
                'description' => $request->description,
            ];
            $imagesToDelete = [];

            if ($request->logo) {
                $imagesToDelete[] = $brand->logo_image_path;
                $logoNewPath = self::BRAND_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10);

                $this->storeImage($logoNewPath, $request->logo, 'webp');
                $this->storeImage($logoNewPath, $request->logo, 'png');

                $fieldsToUpdate['logo_image_path'] = $logoNewPath . '.webp';
            }

            $brand->update($fieldsToUpdate);

            foreach ($imagesToDelete as $imageToDelete) {
                $this->deleteImage($imageToDelete);
            }

            return ServiceActionResult::make(true, trans('admin.brand_edit_success'));
        });
    }

    public function deleteBrand(Brand $brand): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($brand) {
            if (Product::where('brand_id', $brand->id)->exists()) {
                return ServiceActionResult::make(false, trans('admin.brand_in_use'));
            } else {
                if( !is_null($brand->logo_image_path) ) {
                    $this->deleteImage($brand->logo_image_path);
                }

                $brand->delete();
                return ServiceActionResult::make(true, trans('admin.brand_delete_success'));
            }
        });
    }

}
