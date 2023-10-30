<?php

namespace App\Services\ProductCategory;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\User;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\ProductCategory\DTO\CreateCategoryDTO;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class CategoryService extends BaseService
{
    public const CATEGORY_IMAGES_FOLDER = 'category-images';

    public function getProductCategories(ProductType $productType): Collection
    {
        return Category::where('product_type_id', $productType->id)->get();
    }
    public function getProductCategoryPaginated(ProductType $productType): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Category::where('product_type_id', $productType->id)->with('creator')->paginate(config('domain.items_per_page'));
    }

    public function createCategory(ProductType $productType, User $creator, CreateCategoryDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($request, $creator, $productType) {
            $path = self::CATEGORY_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '.jpg';

            $image = Image::make($request->image)
                ->resize(60, 60)
                ->encode('jpg', 85);

            Storage::disk(config('app.images_disk_default'))->put($path, $image);

            Category::create([
                'creator_id' => $creator->id,
                'product_type_id' => $productType->id,
                'name' => $request->name,
                'slug' => $request->slug,
                'image_path' => $path,
            ]);

            return ServiceActionResult::make(true, trans('admin.product_category_create_success'));
        });
    }

    public function editCategory(Category $productCategory, CreateCategoryDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($request, $productCategory) {

            if ($request->image) {
                $newImagePath = self::CATEGORY_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '.jpg';
                $oldImagePath = $productCategory->image_path;

                $image = Image::make($request->image)->encode('jpg', 100);

                Storage::disk(config('app.images_disk_default'))->put($newImagePath, $image);

                $productCategory->update([
                    'name' => $request->name,
                    'slug' => $request->slug,
                    'image_path' => $newImagePath,
                ]);

                //delete image as a last step
                if (Storage::disk(config('app.images_disk_default'))->exists($oldImagePath)) {
                    Storage::disk(config('app.images_disk_default'))->delete($oldImagePath);
                }
            } else {
                $productCategory->update([
                    'name' => $request->name,
                ]);
            }

            return ServiceActionResult::make(true, trans('admin.product_category_edit_success'));
        });
    }

    public function deleteCategory(Category $productCategory): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function() use($productCategory) {
            if (Product::whereHas('categories', function (Builder $query) use($productCategory) {
                $query->where('category_id', $productCategory->id);
            })->exists()) {
                return ServiceActionResult::make(false, trans('admin.product_category_in_use'));
            }

            $productCategory->delete();

            if (Storage::disk(config('app.images_disk_default'))->exists($productCategory->image_path)) {
                Storage::disk(config('app.images_disk_default'))->delete($productCategory->image_path);
            }

            return ServiceActionResult::make(true, trans('admin.product_category_delete_success'));
        });
    }

    public function updateCountOfProductsByCategory(Collection $categories): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($categories) {
            foreach ($categories as $category) {
                if (!$category instanceof Category) {
                    throw new \Exception('CategoryService@updateCountOfProductsByCategory: invalid entry. Category expected.');
                }

                $countOfProducts = Product::whereHas('categories', function (Builder $query) use($category) {
                    return $query->where('category_id', $category->id);
                })
                    ->whereNull('parent_product_id')
                    ->count();

                $category->update([
                    'count_of_products' => $countOfProducts
                ]);
            }

            return ServiceActionResult::make(true, 'Count of products by category successfully updated');
        });
    }
}
