<?php

namespace App\Services\AboutUsPage;

use App\Models\AboutUsConfig;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\AboutUsPage\DTO\AboutUsPageEditDTO;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class AboutUsPageService extends BaseService
{
    const DELIVERY_PAGE_IMAGES_FOLDER = 'about-us-page-images';

    public function editAboutUsPage(AboutUsPageEditDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($request) {

            $existingConfig = AboutUsConfig::first();
            $dataToUpdate = [
                'meta_title' => $request->metaTitle,
                'meta_description' => $request->metaDescription,
                'meta_keywords' => $request->metaKeyWords,
                'meta_tags' => $request->metaTags,
                'title' => $request->title,
                'description' => $request->description,
                'button_text' => $request->buttonText,
                'button_url' => $request->buttonUrl,
                'iframe' => $request->iframe,
            ];

            $imagesToDelete = [];
            $deliveryImage = null;
            if( !is_null($request->image) ) {
                $imagesToDelete[] = $existingConfig->image;

                $newImagePath = self::DELIVERY_PAGE_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10);
                $dataToUpdate['image'] = $newImagePath . '.webp';

                $deliveryImage['image'] = $request->image;
                $deliveryImage['path'] = $newImagePath;
            }

            if( !is_null( $deliveryImage ) ) {
                $this->storeImage($deliveryImage['path'], $deliveryImage['image'], 'webp');
                $this->storeImage($deliveryImage['path'], $deliveryImage['image'], 'jpg');
            }

            foreach ($imagesToDelete as $imageToDelete) {
                if(!is_null($imageToDelete)) {
                    $this->deleteImage($imageToDelete);
                }
            }

            if( $request->imageDeleted ) {
                $this->deleteImage($existingConfig->image);
                $dataToUpdate['image'] = null;
            }

            if( !is_null($existingConfig)){
                $existingConfig->update($dataToUpdate);
            } else {
                AboutUsConfig::create($dataToUpdate);
            }

             return ServiceActionResult::make(true, trans('admin.about_us_edit_success'));
        });
    }


    public function getAboutUsConfig(): ?AboutUsConfig
    {
        return AboutUsConfig::first();
    }
}
