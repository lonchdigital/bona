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
                'creator_id' => $creator->id,
                'name' => $request->name,
                'slug' => $request->slug,
                'description' => $request->description,
            ];

            if( !is_null($request->logo) ) {
                $logoPath = self::BRAND_IMAGES_FOLDER . '/' . sha1(time()) . '_' . Str::random(10) . '.png';
                $this->storeImage($logoPath, $request->logo, 'png');
                $dataToUpdate['logo_image_path'] = $logoPath;
            }

            Brand::create($dataToUpdate);

            return ServiceActionResult::make(true, trans('admin.brand_create_success'));
        });
    }

    public function editBrand(Brand $brand, EditBrandDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($brand, $request) {
            $fieldsToUpdate = [
                'name' => $request->name,
                'slug' => $request->slug,
                'description' => $request->description,
            ];
            $imagesToDelete = [];

            if ($request->logo) {
                $imagesToDelete[] = $brand->logo_image_path;
                $logoNewPath = self::BRAND_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '.png';
                $this->storeImage($logoNewPath, $request->logo, 'png');
                $fieldsToUpdate['logo_image_path'] = $logoNewPath;
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

                $imagesToDelete = [];

                $brand->delete();

                if( !is_null($brand->logo_image_path) ) {

                    if (Storage::disk(config('app.images_disk_default'))->exists($brand->logo_image_path)) {
                        $imagesToDelete[] = $brand->logo_image_path;
                    }
                    foreach ($imagesToDelete as $imageToDelete) {
                        if (Storage::disk(config('app.images_disk_default'))->exists($imageToDelete)) {
                            Storage::disk(config('app.images_disk_default'))->delete($imageToDelete);
                        }
                    }

                }

                return ServiceActionResult::make(true, trans('admin.brand_delete_success'));
            }
        });
    }

    private function storeBrandSliderImage(string $path, $image): void
    {
        $image = Image::make($image)
            ->resize(2000, 2000)
            ->encode('jpg', 100);

        Storage::disk(config('app.images_disk_default'))->put($path, $image);
    }
}
