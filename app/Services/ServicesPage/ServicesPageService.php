<?php

namespace App\Services\ServicesPage;


use App\Models\ServicesConfig;
use App\Models\ServicesPageSections;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\ServicesPage\DTO\ServicesPageEditDTO;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Collection;

class ServicesPageService extends BaseService
{
    const SERVICES_PAGE_IMAGES_FOLDER = 'services-page-images';

    public function editServicesPage(ServicesPageEditDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($request) {

            $ServicesConfig = $this->getServicesConfig();
            $dataToUpdate = [
                'meta_title' => $request->metaTitle,
                'meta_description' => $request->metaDescription,
                'meta_keywords' => $request->metaKeyWords,
                'meta_tags' => $request->metaTags,
            ];

            if ($ServicesConfig) {
                $ServicesConfig->update($dataToUpdate);
            } else {
                ServicesConfig::create($dataToUpdate);
            }


            $this->syncSections($request->sections);

             return ServiceActionResult::make(true, trans('admin.services_edit_success'));
        });
    }


    public function getServicesPageSections(): Collection
    {
        return ServicesPageSections::get();
    }

    private function syncSections(?array $sections): void
    {
        $imagesToDelete = [];

        $existingSections = ServicesPageSections::get();
        if ($sections) {
            foreach ($sections as $section) {
                $dataToUpdate = [
                    'title' => $section['title'],
                    'description' => $section['description'],
                    'button_text' => $section['button_text'],
                    'button_url' => $section['button_url']
                ];

                if (isset($section['image'])) {
                    $sectionImagePath = self::SERVICES_PAGE_IMAGES_FOLDER . '/' . sha1(time()) . '_' . Str::random(10);

                    $this->storeImage($sectionImagePath, $section['image'], 'webp');
                    $this->storeImage($sectionImagePath, $section['image'], 'jpg');

                    $dataToUpdate['section_image_path'] = $sectionImagePath . '.webp';
                }


                if (isset($section['id']) && $section['id']) {
                    $existingSlide = $existingSections->where('id', $section['id'])->first();
                    if (!$existingSlide) {
                        throw new \Exception('Incorrect slide id: ' . $section['id']);
                    }

                    if (isset($section['image'])) {
                        $imagesToDelete[] = $existingSlide->section_image_path;
                    }

                    $existingSlide->update($dataToUpdate);
                } else {
                    ServicesPageSections::create($dataToUpdate);
                }
            }
        }

        $existingSectionsInRequest = $sections ? array_filter(array_column($sections, 'id'), function ($item) {
            return $item !== null;
        }): [];

        $sectionsToDelete = $existingSections->whereNotIn('id', $existingSectionsInRequest);

        foreach ($sectionsToDelete as $sectionToDelete) {
            $imagesToDelete[] = $sectionToDelete->section_image_path;
            $sectionToDelete->delete();
        }

        foreach ($imagesToDelete as $imageToDelete) {
            if(!is_null($imageToDelete)) {
                $this->deleteImage($imageToDelete);
            }
        }

    }

    public function getServicesConfig(): ?ServicesConfig {
        return ServicesConfig::first();
    }
}
