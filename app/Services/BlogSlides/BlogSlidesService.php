<?php

namespace App\Services\BlogSlides;

use App\Models\BlogSlide;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\BlogSlides\DTO\BlogSlidesEditDTO;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class BlogSlidesService extends BaseService
{
    const BLOG_SLIDES_IMAGES_FOLDER = 'blog-slides-images';

    public function getBlogSlides()
    {
        return BlogSlide::with(['collection'])->get();
    }

    public function blogSlidesEdit(BlogSlidesEditDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($request) {
            $imagesToDelete = [];
            $existingSlides = BlogSlide::get();

            if ($request->slides) {
                foreach ($request->slides as $slide) {
                    if (isset($slide['id'])) {
                        $existingSlide = $existingSlides->where('id', $slide['id'])->first();

                        if (!$existingSlide) {
                            throw new \Exception('Can\'t find the block slide with such id: ' . $slide['id']);
                        }

                        $dataToUpdate = [
                            'collection_id' => $slide['collection_id'],
                            'description' => $slide['description'],
                        ];

                        if (isset($slide['image_1'])) {
                            $image1Path = self::BLOG_SLIDES_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '.jpg';
                            $this->storeSlideImage($image1Path, $slide['image_1']);
                            $dataToUpdate['slide_image_1_path'] = $image1Path;
                            $imagesToDelete[] = $existingSlide->slide_image_1_path;
                        }

                        if (isset($slide['image_2'])) {
                            $image2Path = self::BLOG_SLIDES_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '.jpg';
                            $this->storeSlideImage($image2Path, $slide['image_1']);
                            $dataToUpdate['slide_image_2_path'] = $image2Path;
                            $imagesToDelete[] = $existingSlide->slide_image_2_path;
                        }

                        $existingSlide->update($dataToUpdate);
                    } else {
                        $image1Path = self::BLOG_SLIDES_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '.jpg';
                        $this->storeSlideImage($image1Path, $slide['image_1']);

                        $image2Path = self::BLOG_SLIDES_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '.jpg';
                        $this->storeSlideImage($image2Path, $slide['image_2']);

                        BlogSlide::create([
                            'collection_id' => $slide['collection_id'],
                            'description' => $slide['description'],
                            'slide_image_1_path' => $image1Path,
                            'slide_image_2_path' => $image2Path,
                        ]);
                    }
                }
            }


            $existingSlidesInRequest = $request->slides ? array_filter(array_column($request->slides, 'id'), function ($item) {
                return $item !== null;
            }): [];

            $slidesToDelete = $existingSlides->whereNotIn('id', $existingSlidesInRequest);

            foreach ($slidesToDelete as $slideToDelete) {
                $imagesToDelete[] = $slideToDelete->slide_image_1_path;
                $imagesToDelete[] = $slideToDelete->slide_image_2_path;
                $slideToDelete->delete();
            }

            foreach ($imagesToDelete as $imageToDelete) {
                $this->deleteSlideImage($imageToDelete);
            }

            return ServiceActionResult::make(true, trans('admin.blog_slides_edit_success'));
        });
    }

    private function storeSlideImage(string $path, UploadedFile $image): void
    {
        $image = Image::make($image)->encode('jpg', 100);

        Storage::disk(config('app.images_disk_default'))->put($path, $image);
    }

    private function deleteSlideImage(string $path): void
    {
        if (Storage::disk(config('app.images_disk_default'))->exists($path)) {
            Storage::disk(config('app.images_disk_default'))->delete($path);
        }
    }
}
