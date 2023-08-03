<?php

namespace App\Services\BlogCategory;

use App\Models\BlogCategory;
use App\Models\User;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\BlogCategory\DTO\EditBlogCategoryDTO;
use Illuminate\Support\Collection;

class BlogCategoryService extends BaseService
{
    public function getBlogCategoriesListPaginated()
    {
        return BlogCategory::paginate(config('domain.items_per_page'));
    }

    public function getBlogCategoriesCollection(): Collection
    {
        return BlogCategory::get();
    }

    public function createBlogCategory(User $creator, EditBlogCategoryDTO $request): ServiceActionResult
    {
        return $this->coverWithTryCatch(function () use ($creator, $request) {
            BlogCategory::create([
                'creator_id' => $creator->id,
                'name' => $request->name,
                'slug' => $request->slug,
            ]);


            return ServiceActionResult::make(true, trans('admin.blog_category_create_success'));
        });
    }

    public function editBlogCategory(BlogCategory $blogCategory, EditBlogCategoryDTO $request): ServiceActionResult
    {
        return $this->coverWithTryCatch(function () use ($blogCategory, $request) {
            $blogCategory->update([
                'name' => $request->name,
                'slug' => $request->slug,
            ]);


            return ServiceActionResult::make(true, trans('admin.blog_category_edit_success'));
        });
    }

    public function deleteBlogCategory(BlogCategory $blogCategory): ServiceActionResult
    {
        return ServiceActionResult::make(false, 'NOT IMPLEMENTED');
    }
}
