<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Services\Brand\BrandService;
use App\Services\Brand\DTO\EditBrandDTO;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

class BrandsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(BrandService $brandService): void
    {
        $fakeBrandLogoImagePath = base_path() . '/resources/seed/brands/fake-brand-logo-image.png';
        $fakeBrandHeadImagePath = base_path() . '/resources/seed/brands/fake-brand-head-image.jpg';

        $creator = User::where('role_id', Role::ADMIN_ROLE_ID)->first();

        if (!$creator) {
            throw new \Exception('User with admin roles is not exists!');
        }

        $brands = [
            [
                'name' => [
                    'uk' => 'A.S. Creation',
                    'ru' => 'A.S. Creation',
                ],
                'slug' => 'a-s-creation',
            ],
            [
                'name' => [
                    'uk' => 'ASSORTI',
                    'ru' => 'ASSORTI',
                ],
                'slug' => 'assorti',
            ],
            [
                'name' => [
                    'uk' => 'AdaWall',
                    'ru' => 'AdaWall',
                ],
                'slug' => 'adawall',
            ],
            [
                'name' => [
                    'uk' => 'Ambassador',
                    'ru' => 'Ambassador',
                ],
                'slug' => 'ambassador',
            ],
            [
                'name' => [
                    'uk' => 'Amber',
                    'ru' => 'Amber',
                ],
                'slug' => 'amber',
            ],
            [
                'name' => [
                    'uk' => 'ArhiMED',
                    'ru' => 'ArhiMED',
                ],
                'slug' => 'arhimed',
            ],
            [
                'name' => [
                    'uk' => 'Bravo',
                    'ru' => 'Bravo',
                ],
                'slug' => 'bravo',
            ],
            [
                'name' => [
                    'uk' => 'Casadeco',
                    'ru' => 'Casadeco',
                ],
                'slug' => 'casadeco',
            ],
            [
                'name' => [
                    'uk' => 'Caselio',
                    'ru' => 'Caselio',
                ],
                'slug' => 'caselio',
            ],
            [
                'name' => [
                    'uk' => 'Cristiana Masi',
                    'ru' => 'Cristiana Masi',
                ],
                'slug' => 'cristiana-masi',
            ],
            [
                'name' => [
                    'uk' => 'DID',
                    'ru' => 'DID',
                ],
                'slug' => 'did',
            ],
            [
                'name' => [
                    'uk' => 'DINASTIA',
                    'ru' => 'DINASTIA',
                ],
                'slug' => 'dinastia',
            ],
            [
                'name' => [
                    'uk' => 'Decoprint',
                    'ru' => 'Decoprint',
                ],
                'slug' => 'decoprint',
            ],
            [
                'name' => [
                    'uk' => 'Emiliana Parati',
                    'ru' => 'Emiliana Parati',
                ],
                'slug' => 'emiliana-parati',
            ],
            [
                'name' => [
                    'uk' => 'Erismann',
                    'ru' => 'Erismann',
                ],
                'slug' => 'erismann',
            ],
            [
                'name' => [
                    'uk' => 'Grandeco',
                    'ru' => 'Grandeco',
                ],
                'slug' => 'grandeco',
            ],
            [
                'name' => [
                    'uk' => 'ICH',
                    'ru' => 'ICH',
                ],
                'slug' => 'ich',
            ],
            [
                'name' => [
                    'uk' => 'Khroma',
                    'ru' => 'Khroma',
                ],
                'slug' => 'khroma',
            ],
            [
                'name' => [
                    'uk' => 'LG Hausys',
                    'ru' => 'LG Hausys',
                ],
                'slug' => 'lg-hausys',
            ],
            [
                'name' => [
                    'uk' => 'Limonta',
                    'ru' => 'Limonta',
                ],
                'slug' => 'limonta',
            ],
            [
                'name' => [
                    'uk' => 'MEGAPOLIS',
                    'ru' => 'MEGAPOLIS',
                ],
                'slug' => 'megapolis',
            ],
            [
                'name' => [
                    'uk' => 'Marburg',
                    'ru' => 'Marburg',
                ],
                'slug' => 'marburg',
            ],
            [
                'name' => [
                    'uk' => 'P+S International',
                    'ru' => 'P+S International',
                ],
                'slug' => 'p-s-international',
            ],
            [
                'name' => [
                    'uk' => 'PUFAS',
                    'ru' => 'PUFAS',
                ],
                'slug' => 'pafus',
            ],
            [
                'name' => [
                    'uk' => 'Parato',
                    'ru' => 'Parato',
                ],
                'slug' => 'parato',
            ],
            [
                'name' => [
                    'uk' => 'RASCH',
                    'ru' => 'RASCH',
                ],
                'slug' => 'rasch',
            ],
            [
                'name' => [
                    'uk' => 'SUNRAY',
                    'ru' => 'SUNRAY',
                ],
                'slug' => 'sunray',
            ],
            [
                'name' => [
                    'uk' => 'Sintra',
                    'ru' => 'Sintra',
                ],
                'slug' => 'sintra',
            ],
            [
                'name' => [
                    'uk' => 'Sirpi',
                    'ru' => 'Sirpi',
                ],
                'slug' => 'sirpi',
            ],
            [
                'name' => [
                    'uk' => 'Status',
                    'ru' => 'Status',
                ],
                'slug' => 'status',
            ],
            [
                'name' => [
                    'uk' => 'Sticker Wall',
                    'ru' => 'Sticker Wall',
                ],
                'slug' => 'sticker-wall',
            ],
            [
                'name' => [
                    'uk' => 'Texdecor',
                    'ru' => 'Texdecor',
                ],
                'slug' => 'texdecor',
            ],
            [
                'name' => [
                    'uk' => 'Toscana',
                    'ru' => 'Toscana',
                ],
                'slug' => 'toscana',
            ],
            [
                'name' => [
                    'uk' => 'Versailles',
                    'ru' => 'Versailles',
                ],
                'slug' => 'versailles',
            ],
            [
                'name' => [
                    'uk' => 'Vinil',
                    'ru' => 'Vinil',
                ],
                'slug' => 'vinil',
            ],
            [
                'name' => [
                    'uk' => 'York',
                    'ru' => 'York',
                ],
                'slug' => 'york',
            ],
            [
                'name' => [
                    'uk' => 'Zambaiti Parati',
                    'ru' => 'Zambaiti Parati',
                ],
                'slug' => 'zambaiti-parati',
            ],
            [
                'name' => [
                    'uk' => 'Артекс',
                    'ru' => 'Артекс',
                ],
                'slug' => 'arteks',
            ],
            [
                'name' => [
                    'uk' => 'Континент',
                    'ru' => 'Континент',
                ],
                'slug' => 'kontynent',
            ],
            [
                'name' => [
                    'uk' => 'Ланита',
                    'ru' => 'Ланита',
                ],
                'slug' => 'Lanita',
            ],
            [
                'name' => [
                    'uk' => 'Слов\'янські шпалери',
                    'ru' => 'Славянские обои',
                ],
                'slug' => 'slovyanski-spalery',
            ],
            [
                'name' => [
                    'uk' => 'Шарм',
                    'ru' => 'Шарм',
                ],
                'slug' => 'sarm',
            ],
        ];

        foreach ($brands as $brand) {
            $dto = new EditBrandDTO(
                $brand['name'],
                $brand['slug'],
                [
                    'uk' => 'Опис',
                    'ru' => 'Описание',
                ],
                UploadedFile::fake()->createWithContent('fake-brand-logo-image.png', file_get_contents($fakeBrandLogoImagePath)),
                UploadedFile::fake()->createWithContent('fake-brand-head-image.png', file_get_contents($fakeBrandHeadImagePath)),
                [
                    'uk' => 'Натхнення',
                    'ru' => 'Вдохновение',
                ],
                [
                    'uk' => 'Перегляньте нашу добірку фото та відео колекцій шпалер ' . $brand['name']['uk'] . '.',
                    'ru' => 'Просмотрите нашу подборку фото и видео коллекций обоев ' . $brand['name']['ru'] . '.',
                ],
                [
                    [
                        'image' => UploadedFile::fake()->createWithContent('fake-brand-head-image.png', file_get_contents($fakeBrandHeadImagePath)),
                    ],
                    [
                        'image' => UploadedFile::fake()->createWithContent('fake-brand-head-image.png', file_get_contents($fakeBrandHeadImagePath)),
                    ],
                    [
                        'image' => UploadedFile::fake()->createWithContent('fake-brand-head-image.png', file_get_contents($fakeBrandHeadImagePath)),
                    ],
                    [
                        'image' => UploadedFile::fake()->createWithContent('fake-brand-head-image.png', file_get_contents($fakeBrandHeadImagePath)),
                    ],
                    [
                        'image' => UploadedFile::fake()->createWithContent('fake-brand-head-image.png', file_get_contents($fakeBrandHeadImagePath)),
                    ],
                ]
            );

            $brandService->createBrand($creator, $dto);
        }
    }
}
