<?php

namespace App\Services\Work;

use App\Models\Work;
use App\Models\WorkImage;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\Work\DTO\EditWorkDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

use function config;
use function trans;

class WorkService extends BaseService
{
    public const WORK_IMAGES_FOLDER = 'work-images';

    public function getWorks(): Collection
    {
        return Work::get();
    }

    public function getWorksWithCreatorPaginated(): LengthAwarePaginator
    {
        return Work::with('creator')->paginate(config('domain.items_per_page'));
    }

    public function getWorksPaginated()
    {
        return Work::published()
            ->with('images')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(config('domain.works_per_page'));
    }

    public function getOtherWorks(Work $work, int $count = 3)
    {
        return Work::published()
            ->whereKeyNot($work->getKey())
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit($count)
            ->get();
    }

    public function createWork(EditWorkDTO $request): ServiceActionResult
    {
        $creator = $this->getAuthUser();

        return $this->coverWithDBTransaction(function () use ($request, $creator) {

            $workData = $this->buildWorkData($request) + ['creator_id' => $creator->id];

            $imagePath = self::WORK_IMAGES_FOLDER.'/'.sha1(time()).'_'.Str::random(10).'.jpg';
            $workData['image_path'] = $imagePath;
            $this->storeImage($imagePath, $request->mainImage);

            $work = Work::create($workData);

            $this->syncImages($work, $request->images);

            return ServiceActionResult::make(true, trans('admin.work_create_success'));
        });
    }

    public function updateWork(Work $work, EditWorkDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($work, $request) {

            $workData = $this->buildWorkData($request);

            if ($request->mainImage) {
                $imagePath = self::WORK_IMAGES_FOLDER.'/'.sha1(time()).'_'.Str::random(10).'.jpg';
                $workData['image_path'] = $imagePath;
                $this->storeImage($imagePath, $request->mainImage);
                $this->deleteImage($work->image_path);
            }

            $work->update($workData);

            $this->syncImages($work, $request->images);

            return ServiceActionResult::make(true, trans('admin.work_edit_success'));
        });
    }

    private function buildWorkData(EditWorkDTO $request): array
    {
        return [
            'name' => $request->name,
            'slug' => $request->slug,
            'intro' => $request->intro,
            'description' => $request->description,
            'location' => $request->location,
            'doors_count' => $request->doorsCount,
            'duration' => $request->duration,
            'client_quote' => $request->clientQuote,
            'client_name' => $request->clientName,
            'service_title' => $request->serviceTitle,
            'service_description' => $request->serviceDescription,
            'price_from' => $request->priceFrom !== null && $request->priceFrom !== '' ? $request->priceFrom : null,
            'price_currency' => $request->priceCurrency ?: 'UAH',
            'price_note' => $request->priceNote,
            'is_published' => $request->isPublished,
            'meta_title' => $request->metaTitle,
            'meta_description' => $request->metaDescription,
            'meta_keywords' => $request->metaKeyWords,
        ];
    }

    /**
     * Rows missing from the request are removed, so the form is the single
     * source of truth for the gallery.
     */
    private function syncImages(Work $work, ?array $images): void
    {
        $existingImages = $work->images()->get();
        $imagesToDelete = [];
        $keptIds = [];

        foreach ($images ?? [] as $index => $image) {
            $imageModel = null;

            if (! empty($image['id'])) {
                $imageModel = $existingImages->firstWhere('id', (int) $image['id']);

                if (! $imageModel) {
                    throw new \Exception('Image '.$image['id'].' does not belong to this work');
                }
            }

            $data = [
                'caption' => $image['caption'] ?? null,
                'sort_order' => $index,
            ];

            $uploaded = $image['image'] ?? null;

            if ($uploaded instanceof UploadedFile) {
                $imagePath = self::WORK_IMAGES_FOLDER.'/'.sha1(microtime(true)).'_'.Str::random(10).'.jpg';
                $this->storeImage($imagePath, $uploaded);
                $data['image_path'] = $imagePath;

                if ($imageModel?->image_path) {
                    $imagesToDelete[] = $imageModel->image_path;
                }
            }

            if ($imageModel) {
                $imageModel->update($data);
                $keptIds[] = $imageModel->id;

                continue;
            }

            if (! isset($data['image_path'])) {
                continue;
            }

            $keptIds[] = WorkImage::create($data + ['work_id' => $work->id])->id;
        }

        foreach ($existingImages->whereNotIn('id', $keptIds) as $imageToDelete) {
            $imagesToDelete[] = $imageToDelete->image_path;
            $imageToDelete->delete();
        }

        foreach ($imagesToDelete as $imagePath) {
            $this->deleteImage($imagePath);
        }
    }

    public function deleteWork(Work $work): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($work) {

            $imagesToDelete = $work->images->pluck('image_path')->all();
            $imagesToDelete[] = $work->image_path;

            $work->images()->delete();
            $work->delete();

            foreach ($imagesToDelete as $imagePath) {
                $this->deleteImage($imagePath);
            }

            return ServiceActionResult::make(true, trans('admin.work_delete_success'));
        });
    }
}
