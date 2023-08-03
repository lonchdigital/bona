<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Services\Country\CountryService;
use App\Services\Country\DTO\EditCountryDTO;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

class CountriesSeeder extends Seeder
{

    public function __construct(
        public CountryService $countryService,
    ) { }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $basePath = base_path() . '/resources/seed/countries';

        $countries = [
            [
                'name' => [
                    'uk' => 'Австралія',
                    'ru' => 'Австралия',
                ],
                'code' => 'AU',
                'fake_image_name' => 'AU.svg',
            ],
            [
                'name' => [
                    'uk' => 'Бельгія',
                    'ru' => 'Бельгия',
                ],
                'code' => 'BE',
                'fake_image_name' => 'BE.svg',
            ],
            [
                'name' => [
                    'uk' => 'Канада',
                    'ru' => 'Канада',
                ],
                'code' => 'CA',
                'fake_image_name' => 'CA.svg',
            ],
            [
                'name' => [
                    'uk' => 'Швейцарія',
                    'ru' => 'Швейцария',
                ],
                'code' => 'CH',
                'fake_image_name' => 'CH.svg',
            ],
            [
                'name' => [
                    'uk' => 'Китай',
                    'ru' => 'Китай',
                ],
                'code' => 'CN',
                'fake_image_name' => 'CN.svg',
            ],
            [
                'name' => [
                    'uk' => 'Чехія',
                    'ru' => 'Чехия',
                ],
                'code' => 'CZ',
                'fake_image_name' => 'CZ.svg',
            ],
            [
                'name' => [
                    'uk' => 'Німеччина',
                    'ru' => 'Германія',
                ],
                'code' => 'DE',
                'fake_image_name' => 'DE.svg',
            ],
            [
                'name' => [
                    'uk' => 'Данія',
                    'ru' => 'Дания',
                ],
                'code' => 'DK',
                'fake_image_name' => 'DK.svg',
            ],
            [
                'name' => [
                    'uk' => 'Іспанія',
                    'ru' => 'Испания',
                ],
                'code' => 'ES',
                'fake_image_name' => 'ES.svg',
            ],
            [
                'name' => [
                    'uk' => 'Фінляндія',
                    'ru' => 'Финлядния',
                ],
                'code' => 'FI',
                'fake_image_name' => 'FI.svg',
            ],
            [
                'name' => [
                    'uk' => 'Франція',
                    'ru' => 'Франция',
                ],
                'code' => 'FR',
                'fake_image_name' => 'FR.svg',
            ],
            [
                'name' => [
                    'uk' => 'Велика Британія',
                    'ru' => 'Великобритания',
                ],
                'code' => 'GB',
                'fake_image_name' => 'GB.svg',
            ],
            [
                'name' => [
                    'uk' => 'Італія',
                    'ru' => 'Италия',
                ],
                'code' => 'IT',
                'fake_image_name' => 'IT.svg',
            ],
            [
                'name' => [
                    'uk' => 'Японія',
                    'ru' => 'Япония',
                ],
                'code' => 'JP',
                'fake_image_name' => 'JP.svg',
            ],
            [
                'name' => [
                    'uk' => 'Південна Корея',
                    'ru' => 'Южная Корея',
                ],
                'code' => 'KR',
                'fake_image_name' => 'KR.svg',
            ],
            [
                'name' => [
                    'uk' => 'Нідерладни',
                    'ru' => 'Нидерланды',
                ],
                'code' => 'NL',
                'fake_image_name' => 'NL.svg',
            ],
            [
                'name' => [
                    'uk' => 'Польща',
                    'ru' => 'Польша',
                ],
                'code' => 'PL',
                'fake_image_name' => 'PL.svg',
            ],
            [
                'name' => [
                    'uk' => 'Португалія',
                    'ru' => 'Португалия',
                ],
                'code' => 'PT',
                'fake_image_name' => 'PT.svg',
            ],
            [
                'name' => [
                    'uk' => 'Румунія',
                    'ru' => 'Румуния',
                ],
                'code' => 'RO',
                'fake_image_name' => 'RO.svg',
            ],
            [
                'name' => [
                    'uk' => 'Росія',
                    'ru' => 'Россия',
                ],
                'code' => 'RU',
                'fake_image_name' => 'RU.svg',
            ],
            [
                'name' => [
                    'uk' => 'Швеція',
                    'ru' => 'Швеция',
                ],
                'code' => 'SE',
                'fake_image_name' => 'SE.svg',
            ],
            [
                'name' => [
                    'uk' => 'Тайланд',
                    'ru' => 'Тайланд',
                ],
                'code' => 'TH',
                'fake_image_name' => 'TH.svg',
            ],
            [
                'name' => [
                    'uk' => 'Турція',
                    'ru' => 'Турция',
                ],
                'code' => 'TR',
                'fake_image_name' => 'TR.svg',
            ],
            [
                'name' => [
                    'uk' => 'США',
                    'ru' => 'США',
                ],
                'code' => 'US',
                'fake_image_name' => 'US.svg',
            ],
            [
                'name' => [
                    'uk' => 'Південна Африка',
                    'ru' => 'Южная Африка',
                ],
                'code' => 'ZA',
                'fake_image_name' => 'ZA.svg',
            ],
        ];

        $creator = User::where('role_id', Role::ADMIN_ROLE_ID)->first();

        if (!$creator) {
            throw new \Exception('User with admin roles is not exists!');
        }

        foreach ($countries as $country) {
            $this->countryService->createCountry($creator, new EditCountryDTO(
                ['uk' => $country['name']['uk'], 'ru' => $country['name']['ru']],
                $country['code'],
                UploadedFile::fake()->createWithContent($country['fake_image_name'], file_get_contents($basePath . '/' . $country['fake_image_name']))
            ));
        }
    }
}
