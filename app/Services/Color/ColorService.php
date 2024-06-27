<?php

namespace App\Services\Color;

use App\Models\Color;
use App\Models\Product;
use App\Models\ProductType;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\Color\DTO\EditColorDTO;
use App\Services\Color\DTO\FilterColorAdminDTO;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ColorService extends BaseService
{
    const COLOR_IMAGES_FOLDER = 'color-images';

    public function __construct(
        private readonly ColorFiltersAdminService $filtersAdminService,
    ) { }

    public function getColors(): Collection
    {
        return Color::get();
    }

    public function getColorsPaginated(FilterColorAdminDTO $request): LengthAwarePaginator
    {
        $query = Color::query();
        $query->with([
            'creator',
        ]);
        $query = $this->filtersAdminService->handleColorFilters($request, $query);

        return $query->paginate(config('domain.items_per_page'));
    }

    public function getAvailableColorsByProductType($productType): Collection
    {
        $typeId = $productType->id;

        return Color::whereHas('products', function ($query) use ($typeId) {
            $query->whereHas('productType', function ($query) use ($typeId) {
                $query->where('id', $typeId);
            });
        })->get();
    }

    public function getAllColors(): Collection
    {
        return Color::all();
    }

    public function createColor(EditColorDTO $request): ServiceActionResult
    {
        $creator = $this->getAuthUser();

        return $this->coverWithTryCatch(function () use($request, $creator) {

            $dataToUpdate = [
                'creator_id' => $creator->id,
                'name' => $request->name,
                'slug' => $request->slug,
                'display_as_image' => $request->displayAsImage,
                'hex' => $request->hex,
            ];

            $colorImage = null;
            if( !is_null($request->mainImage) ) {
                $newImagePath = self::COLOR_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10);
                $colorImage['image'] = $request->mainImage;

                $this->storeImage($newImagePath, $colorImage['image'], 'webp');
                $this->storeImage($newImagePath, $colorImage['image'], 'jpg');

                $dataToUpdate['main_image'] = $newImagePath . '.webp';
            }

            Color::create($dataToUpdate);

            return ServiceActionResult::make(true, trans('admin.color_create_success'));
        });
    }

    public function editColor(Color $color, EditColorDTO $request): ServiceActionResult
    {
        return $this->coverWithTryCatch(function () use ($color, $request) {

            $dataToUpdate = [
                'name' => $request->name,
                'slug' => $request->slug,
                'display_as_image' => $request->displayAsImage,
                'hex' => $request->hex,
            ];

            $imageToDelete = null;
            if( !is_null($request->mainImage) ) {
                $imageToDelete = $color->main_image;

                $newImagePath = self::COLOR_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10);
                $colorImage['image'] = $request->mainImage;

                $this->storeImage($newImagePath, $colorImage['image'], 'webp');
                $this->storeImage($newImagePath, $colorImage['image'], 'jpg');

                $dataToUpdate['main_image'] = $newImagePath . '.webp';
            }

            if(!is_null($imageToDelete)) {
                $this->deleteImage($imageToDelete);
            }

            $color->update($dataToUpdate);

            return ServiceActionResult::make(true, trans('admin.color_edit_success'));
        });
    }

    public function deleteColor(Color $color): ServiceActionResult
    {
        return $this->coverWithTryCatch(function () use($color) {
            $productWithColorExists = Product::where('main_color_id', $color->id)
                ->orWhereHas('colors', function (Builder $query) use($color) {
                    $query->where('color_id', $color->id);
                })->exists();

            if ($productWithColorExists) {
                return ServiceActionResult::make(false, trans('admin.color_in_use'));
            }

            if(!is_null($color->main_image)) {
                $this->deleteImage($color->main_image);
            }

            $color->delete();

            return ServiceActionResult::make(true, trans('admin.color_delete_success'));
        });
    }
}
