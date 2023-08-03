<?php

namespace App\Services\Collection;

use App\Models\Collection as ProductCollection;
use App\Models\Product;
use App\Models\User;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\Collection\DTO\EditCollectionDTO;
use App\Services\Collection\DTO\FilterCollectionDTO;
use App\Services\Collection\DTO\SearchCollectionDTO;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;


class CollectionService extends BaseService
{
    public const COLLECTION_IMAGES_FOLDER = 'collection-slide-images';

    public function getCollections(): Collection
    {
        return ProductCollection::with(['brand'])->get();
    }

    public function getCollectionsByBrandId(int $brandId): Collection
    {
        $query = ProductCollection::where('brand_id', $brandId);

        $query = $query->withCount(['products' => function($query) {
            $query->whereNull('parent_product_id');
        }]);

        return $query->get();
    }

    public function searchCollectionsByBrandId(int $brandId, FilterCollectionDTO $request): Collection
    {
        $query = ProductCollection::where('brand_id', $brandId);

        if ($request->search) {
            $query->where(function ($query) use($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            });
        }

        return $query->get();
    }

    public function searchCollection(SearchCollectionDTO $request): Collection
    {
        $query = ProductCollection::select(['id', 'name'])->limit(10);

        if ($request->query) {
            $query->where(function ($query) use($request) {
                $query->where('name', 'like', '%' . $request->query . '%');
            });
        }

        return $query->get();
    }

    public function getCollectionsPaginated(FilterCollectionDTO $request): LengthAwarePaginator
    {
        $query = ProductCollection::with('creator');

        if ($request->search) {
            $query->where(function (Builder $query) use($request) {
                $query->where('name', 'LIKE', '%' . $request->search . '%');
            });
        }

        if ($request->searchBrandIds) {
            $query->where(function (Builder $query) use($request) {
                $query->whereIn('brand_id', $request->searchBrandIds);
            });
        }

        return $query->paginate(config('domain.items_per_page'));
    }

    public function createCollection(User $creator, EditCollectionDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($request, $creator) {
            $preview1Path = self::COLLECTION_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . 'preview_1.jpg';
            $preview2Path = self::COLLECTION_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . 'preview_2.jpg';
            $preview3Path = self::COLLECTION_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . 'preview_3.jpg';
            $preview4Path = self::COLLECTION_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . 'preview_4.jpg';

            $this->storeCollectionImage($preview1Path, $request->preview1);
            $this->storeCollectionImage($preview2Path, $request->preview2);
            $this->storeCollectionImage($preview3Path, $request->preview3);
            $this->storeCollectionImage($preview4Path, $request->preview4);

            $collection = ProductCollection::create([
                'name' => $request->name,
                'slug' => $request->slug,
                'brand_id' => $request->brandId,
                'creator_id' => $creator->id,
                'preview_image_1' => $preview1Path,
                'preview_image_2' => $preview2Path,
                'preview_image_3' => $preview3Path,
                'preview_image_4' => $preview4Path,
            ]);

            $slidesToCreate = [];
            foreach ($request->slides as $slide) {
                $image1path = self::COLLECTION_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_1.jpg';
                $image2path = self::COLLECTION_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_2.jpg';

                $this->storeCollectionImage($image1path, $slide['image_1']);
                $this->storeCollectionImage($image2path, $slide['image_2']);

                $slidesToCreate[] = [
                    'image_1_path' => $image1path,
                    'image_2_path' => $image2path,
                ];
            }

            $collection->slides()->createMany($slidesToCreate);

            return ServiceActionResult::make(true, trans('admin.collection_add_success'));
        });
    }

    public function editCollection(ProductCollection $collection, EditCollectionDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($collection, $request) {
            //array of links
            $imagesToDelete = [];

            $dataToUpdate = [
                'name' => $request->name,
                'slug' => $request->slug,
                'brand_id' => $request->brandId,
            ];

            if ($request->preview1) {
                $imagesToDelete[] = $collection->preview_image_1;
                $preview1Path = self::COLLECTION_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . 'preview_1.jpg';
                $this->storeCollectionImage($preview1Path, $request->preview1);
                $dataToUpdate['preview_image_1'] = $preview1Path;
            }

            if ($request->preview2) {
                $imagesToDelete[] = $collection->preview_image_2;
                $preview2Path = self::COLLECTION_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . 'preview_2.jpg';
                $this->storeCollectionImage($preview2Path, $request->preview2);
                $dataToUpdate['preview_image_2'] = $preview2Path;
            }

            if ($request->preview3) {
                $imagesToDelete[] = $collection->preview_image_3;
                $preview3Path = self::COLLECTION_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . 'preview_3.jpg';
                $this->storeCollectionImage($preview3Path, $request->preview3);
                $dataToUpdate['preview_image_3'] = $preview3Path;
            }

            if ($request->preview4) {
                $imagesToDelete[] = $collection->preview_image_4;
                $preview4Path = self::COLLECTION_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . 'preview_4.jpg';
                $this->storeCollectionImage($preview4Path, $request->preview4);
                $dataToUpdate['preview_image_4'] = $preview4Path;
            }

            $collection->update($dataToUpdate);

            $existingSlides = $collection->slides;

            //assoc array
            $slidesToCreate = [];
            //array of ids
            $slidesToUpdate = [];

            foreach ($request->slides as $slide) {
                if (isset($slide['id']) && $slide['id']) {
                    //slide to update
                    $existingSlide = $existingSlides->where('id', $slide['id'])->first();

                    //make sure that slide exists
                    if ($existingSlide) {
                        if (isset($slide['image_1'])) {
                            $image1path = self::COLLECTION_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_1.jpg';
                            $this->storeCollectionImage($image1path, $slide['image_1']);
                            $imagesToDelete[] = $existingSlide->image_1_path;
                            $existingSlide->image_1_path = $image1path;
                        }

                        if (isset($slide['image_2'])) {
                            $image2path = self::COLLECTION_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_2.jpg';
                            $this->storeCollectionImage($image2path, $slide['image_2']);
                            $imagesToDelete[] = $existingSlide->image_2_path;
                            $existingSlide->image_2_path = $image2path;
                        }

                        $slidesToUpdate[] = $slide['id'];
                    }

                } else {
                    //slide to create
                    $image1path = self::COLLECTION_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_1.jpg';
                    $this->storeCollectionImage($image1path, $slide['image_1']);
                    $image2path = self::COLLECTION_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '_2.jpg';
                    $this->storeCollectionImage($image2path, $slide['image_2']);

                    $slidesToCreate[] = [
                        'image_1_path' => $image1path,
                        'image_2_path' => $image2path,
                    ];
                }
            }

            //update slides
            $collection->slides()->saveMany($existingSlides);

            if (count($slidesToCreate)) {
                $collection->slides()->createMany($slidesToCreate);
            }

            $slidesToDelete = $existingSlides->whereNotIn('id', $slidesToUpdate);

            foreach ($slidesToDelete as $slideToDelete) {
                $imagesToDelete[] = $slideToDelete->image_1_path;
                $imagesToDelete[] = $slideToDelete->image_2_path;
            }

            $collection->slides()->whereIn('id', $slidesToDelete->pluck('id'))->delete();

            foreach ($imagesToDelete as $imageToDelete) {
                if (Storage::disk(config('app.images_disk_default'))->exists($imageToDelete)) {
                    Storage::disk(config('app.images_disk_default'))->delete($imageToDelete);
                }
            }

            return ServiceActionResult::make(true, trans('admin.collection_edit_success'));
        });
    }

    public function deleteCollection(ProductCollection $collection): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($collection) {
            if (Product::where('collection_id', $collection->id)->exists()) {
                return ServiceActionResult::make(true, trans('admin.collection_in_use'));
            }

            //array of image paths
            $imagesToDelete = [];

            foreach ($collection->slides as $collectionSlide) {
                $imagesToDelete[] = $collectionSlide->image_1_path;
                $imagesToDelete[] = $collectionSlide->image_2_path;
            }

            $collection->slides()->delete();

            $collection->delete();

            foreach ($imagesToDelete as $imageToDelete) {
                if (Storage::disk(config('app.images_disk_default'))->exists($imageToDelete)) {
                    Storage::disk(config('app.images_disk_default'))->delete($imageToDelete);
                }
            }

            return ServiceActionResult::make(true, trans('admin.collection_delete_success'));
        });
    }

    private function storeCollectionImage(string $path, $image): void
    {
        $image = Image::make($image)
            ->resize(2000, 2000)
            ->encode('jpg', 100);

        Storage::disk(config('app.images_disk_default'))->put($path, $image);
    }
}
