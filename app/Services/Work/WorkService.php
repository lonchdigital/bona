<?php

namespace App\Services\Work;

use App\Models\Work;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\Work\DTO\EditWorkDTO;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use function config;
use function trans;

// remove

class WorkService extends BaseService
{
    public const WORK_IMAGES_FOLDER = 'work-images';

    public function getWorks(): Collection
    {
        return Work::get();
    }

    public function getWorksWithCreatorPaginated(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Work::with('creator')->paginate(config('domain.items_per_page'));
    }

    public function getWorksPaginated()
    {
        return Work::paginate(config('domain.works_per_page'));
    }

    public function createWork(EditWorkDTO $request): ServiceActionResult
    {
        $creator = $this->getAuthUser();

        return $this->coverWithDBTransaction(function () use($request, $creator) {

            $workData = [
                'creator_id' => $creator->id,
                'name' => $request->name,
                'slug' => $request->slug,
                'meta_title' => $request->metaTitle,
                'meta_description' => $request->metaDescription,
                'meta_keywords' => $request->metaKeyWords,
            ];

            $imagePath = self::WORK_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '.jpg';
            $workData['image_path'] = $imagePath;
            $this->storeImage($imagePath, $request->mainImage);

            Work::create($workData);

            return ServiceActionResult::make(true, trans('admin.work_create_success'));
        });
    }

    public function updateWork(Work $work, EditWorkDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($work, $request) {

            $workData = [
                'name' => $request->name,
                'slug' => $request->slug,
                'meta_title' => $request->metaTitle,
                'meta_description' => $request->metaDescription,
                'meta_keywords' => $request->metaKeyWords,
            ];

            if ($request->mainImage) {
                $imagePath = self::WORK_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '.jpg';
                $workData['image_path'] = $imagePath;
                $this->storeImage($imagePath, $request->mainImage);
                $this->deleteImage($work->image_path);
            }

            $work->update($workData);

            return ServiceActionResult::make(true, trans('admin.work_edit_success'));
        });
    }

    public function deleteWork(Work $work): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($work) {

            $this->deleteImage($work->image_path);
            $work->delete();

            return ServiceActionResult::make(true, 'Need a check !');
        });
    }

}
