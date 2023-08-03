<?php

namespace Database\Seeders;

use App\Http\Requests\Admin\Category\CategoryEditRequest;
use App\Models\ProductType;
use App\Models\Role;
use App\Models\User;
use App\Services\ProductCategory\CategoryService;
use App\Services\ProductCategory\DTO\CreateCategoryDTO;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(CategoryService $categoryService): void
    {
        $fakeImagePath = base_path() . '/resources/seed/categories/fake-category-image.jpeg';

        $productType = ProductType::first();

        if (!$productType) {
            throw new \Exception('Default product type is not exists!');
        }

        $creator = User::where('role_id', Role::ADMIN_ROLE_ID)->first();

        if (!$creator) {
            throw new \Exception('User with admin roles is not exists!');
        }


        $categories = [
            [
                'name' => [
                    'uk' => 'Для дому',
                    'ru' => 'Для дома',
                ],
                'slug' => 'dlya-domu',
            ],
            [
                'name' => [
                    'uk' => 'Для спальні',
                    'ru' => 'Для спальни',
                ],
                'slug' => 'dlya-spalni',
            ],
            [
                'name' => [
                    'uk' => 'Для ванної',
                    'ru' => 'Для ванной',
                ],
                'slug' => 'dlya-vannoi',
            ],
            [
                'name' => [
                    'uk' => 'Для коридору',
                    'ru' => 'Для коридорп',
                ],
                'slug' => 'dlya-korydoru',
            ],
            [
                'name' => [
                    'uk' => 'Для прихожої',
                    'ru' => 'Для прихожей',
                ],
                'slug' => 'dlya-pryhozoi',
            ],
            [
                'name' => [
                    'uk' => 'Для вітальні',
                    'ru' => 'Для гостинной',
                ],
                'slug' => 'dlya-vitalni',
            ],
            [
                'name' => [
                    'uk' => 'Для кухні',
                    'ru' => 'Для кухни',
                ],
                'slug' => 'dlya-kuhni',
            ],
        ];

        foreach ($categories as $category) {
            $dto = new CreateCategoryDTO(
                $category['name'],
                $category['slug'],
                UploadedFile::fake()->createWithContent('fake-category-image.jpeg', file_get_contents($fakeImagePath)),
            );

            $categoryService->createCategory($productType, $creator, $dto);
        }
    }
}
