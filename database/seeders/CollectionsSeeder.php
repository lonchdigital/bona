<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Role;
use App\Models\User;
use App\Services\Collection\CollectionService;
use App\Services\Collection\DTO\EditCollectionDTO;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

class CollectionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(CollectionService $collectionService): void
    {
        $fakeCollectionSlideImagePath = base_path() . '/resources/seed/collections/fake-collection-slide-image.jpg';

        $creator = User::where('role_id', Role::ADMIN_ROLE_ID)->first();

        if (!$creator) {
            throw new \Exception('User with admin roles is not exists!');
        }

        $brands = Brand::get();

        foreach ($brands as $brand) {
            $brandNames = $brand->getTranslations('name');

            for($i = 1; $i < rand(2, 3); $i++) {
                $collectionService->createCollection(
                    $creator,
                    new EditCollectionDTO(
                        [
                            'uk' => $brandNames['uk'] . ' collection ' . $i,
                            'ru' => $brandNames['ru'] . ' collection ' . $i,
                        ],
                        \Str::slug($brandNames['uk'] . ' collection ' . $i),
                        $brand->id,
                        [
                            [
                                'image_1' => UploadedFile::fake()->createWithContent('fake-collection-slide-image.jpg', file_get_contents($fakeCollectionSlideImagePath)),
                                'image_2' => UploadedFile::fake()->createWithContent('fake-collection-slide-image.jpg', file_get_contents($fakeCollectionSlideImagePath)),
                            ],
                        ],
                        UploadedFile::fake()->createWithContent('fake-collection-slide-image.jpg', file_get_contents($fakeCollectionSlideImagePath)),
                        UploadedFile::fake()->createWithContent('fake-collection-slide-image.jpg', file_get_contents($fakeCollectionSlideImagePath)),
                        UploadedFile::fake()->createWithContent('fake-collection-slide-image.jpg', file_get_contents($fakeCollectionSlideImagePath)),
                        UploadedFile::fake()->createWithContent('fake-collection-slide-image.jpg', file_get_contents($fakeCollectionSlideImagePath)),
                    )
                );
            }


        }
    }
}
