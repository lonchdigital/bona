<?php

namespace App\Services\DeliveryPage;

use App\Models\DeliveryConfig;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\DeliveryPage\DTO\DeliveryPageEditDTO;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class DeliveryPageService extends BaseService
{
    const DELIVERY_PAGE_IMAGES_FOLDER = 'delivery-page-images';

    public function editDeliveryPage(DeliveryPageEditDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($request) {

            $existingConfig = DeliveryConfig::first();

            $dataToUpdate = [
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

                $newImagePath = self::DELIVERY_PAGE_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '.jpg';
                $dataToUpdate['image'] = $newImagePath;

                $deliveryImage['image'] = $request->image;
                $deliveryImage['path'] = $newImagePath;
            }

            if( !is_null( $deliveryImage ) ) {
                $this->storeImage($deliveryImage['path'], $deliveryImage['image']);
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
                DeliveryConfig::create($dataToUpdate);
            }


             return ServiceActionResult::make(true, trans('admin.delivery_edit_success'));
        });
    }


    public function getDeliveryConfig(): ?DeliveryConfig
    {
        return DeliveryConfig::first();
    }
}
