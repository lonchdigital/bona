<?php

namespace App\Services\Product;

use App\Models\ProductGalleries;
use App\Services\Base\BaseService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use RuntimeException;

class ProductMediaService extends BaseService
{
    public const PRODUCT_IMAGES_FOLDER = 'product-images';

    public function getGallery(int $productId): Collection
    {
        return ProductGalleries::where('product_id', $productId)->get();
    }

    public function syncGallery(int $productId, ?array $galleryImages, ?array $galleryColorIds): void
    {
        $existing = ProductGalleries::where('product_id', $productId)->get();
        $requestedIds = [];
        $imagesToDelete = [];

        foreach ($galleryImages ?? [] as $key => $galleryImage) {
            $data = [
                'product_id' => $productId,
                'color_id' => $galleryColorIds[$key]['color_id'] ?? null,
            ];

            if (isset($galleryImage['image'])) {
                $imageName = sha1((string) microtime(true)).'_'.Str::random(10);
                $this->storeProductImage($imageName, $galleryImage['image'], 'webp');
                $this->storeProductImage($imageName, $galleryImage['image'], 'jpg');
                $data['image_path'] = $this->storagePath().'/'.$imageName.'.webp';
            }

            $id = isset($galleryImage['id']) && $galleryImage['id'] !== ''
                ? (int) $galleryImage['id']
                : null;

            if ($id) {
                $record = $existing->firstWhere('id', $id);
                if (! $record) {
                    throw new RuntimeException("Incorrect product gallery image id: {$id}");
                }

                if (isset($galleryImage['image'])) {
                    $imagesToDelete[] = $record->image_path;
                }

                $record->update($data);
                $requestedIds[] = $id;
            } else {
                ProductGalleries::create($data);
            }
        }

        $imagesToRemove = $existing->whereNotIn('id', $requestedIds);
        foreach ($imagesToRemove as $imageToRemove) {
            $imagesToDelete[] = $imageToRemove->image_path;
            $imageToRemove->delete();
        }

        foreach (array_unique(array_filter($imagesToDelete)) as $imagePath) {
            $this->deleteImage($imagePath);
        }
    }

    public function delete(?string $path): void
    {
        $this->deleteImage($path);
    }

    public function storePreviewImage(string $path, UploadedFile $image, string $format = 'webp', int $quality = 70): void
    {
        $this->ensureStorageDirectoryExists();

        $encodedImage = Image::make($image);
        $imageWidth = (int) round($encodedImage->width() / 2);
        $imageHeight = (int) round($encodedImage->height() / 2);

        if ($imageWidth > 400) {
            $encodedImage = $encodedImage->fit($imageWidth, $imageHeight);
        }

        $encodedImage = $encodedImage->encode($format, $quality);
        Storage::disk(config('app.images_disk_default'))
            ->put($this->storagePath().'/'.$path.'.'.$format, $encodedImage);
    }

    public function storeProductImage(string $path, UploadedFile $image, string $format = 'webp', int $quality = 70): void
    {
        $this->ensureStorageDirectoryExists();

        $encodedImage = Image::make($image)->encode($format, $quality);
        Storage::disk(config('app.images_disk_default'))
            ->put($this->storagePath().'/'.$path.'.'.$format, $encodedImage);
    }

    private function ensureStorageDirectoryExists(): void
    {
        $disk = Storage::disk(config('app.images_disk_default'));
        if (! $disk->exists($this->storagePath())) {
            $disk->makeDirectory($this->storagePath());
        }
    }

    private function storagePath(): string
    {
        return self::PRODUCT_IMAGES_FOLDER.'/'.date('m.Y');
    }
}
