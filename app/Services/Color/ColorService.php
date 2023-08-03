<?php

namespace App\Services\Color;

use App\Models\Color;
use App\Models\Product;
use App\Models\ProductType;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\Color\DTO\EditColorDTO;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ColorService extends BaseService
{
    public function getColors(): Collection
    {
        return Color::get();
    }

    public function getParentColors(?int $excludeColorById = null): Collection
    {
        $query = Color::whereNull('parent_color_id');

        if ($excludeColorById) {
            $query->where('id', '!=', $excludeColorById);
        }

        return $query->get();
    }

    public function getColorsPaginated(): LengthAwarePaginator
    {
        return Color::with(['creator'])->paginate(config('domain.items_per_page'));
    }

    public function getAvailableColorsByProductType(ProductType $productType): Collection
    {
        //TODO: implement with cache
        return Color::get();
    }

    public function createColor(EditColorDTO $request): ServiceActionResult
    {
        $creator = $this->getAuthUser();

        return $this->coverWithTryCatch(function () use($request, $creator) {

            Color::create([
                'creator_id' => $creator->id,
                'name' => $request->name,
                'slug' => $request->slug,
                'hex' => $request->hex,
                'parent_color_id' => $request->parentColorId,
            ]);

            return ServiceActionResult::make(true, trans('admin.color_create_success'));
        });
    }

    public function editColor(Color $color, EditColorDTO $request): ServiceActionResult
    {
        return $this->coverWithTryCatch(function () use ($color, $request) {

            $color->update([
                'name' => $request->name,
                'slug' => $request->slug,
                'hex' => $request->hex,
                'parent_color_id' => $request->parentColorId,
            ]);

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

            $color->delete();

            return ServiceActionResult::make(true, trans('admin.color_delete_success'));
        });
    }
}
