<?php

namespace Database\Seeders;

use App\DataClasses\ProductFieldTypeOptionsDataClass;
use App\DataClasses\NumericFieldFilerTypesDataClass;
use App\Models\ProductField;
use App\Models\ProductFieldFilterOption;
use App\Models\ProductFieldOption;
use App\Models\Role;
use App\Models\User;
use App\Services\Admin\ProductField\ProductFieldService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ProductFieldsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $basePath = base_path() . '/resources/seed/product-field-options';

        $creator = User::where('role_id', Role::ADMIN_ROLE_ID)->first();

        if (!$creator) {
            throw new \Exception('User with admin roles is not exists!');
        }

        $productFields = [
            [
                'id' => 1,
                'field_name' => [
                    'uk' => 'Тематика',
                    'ru' => 'Тематика',
                ],
                'slug' => 'tip-malnku',
                'field_type_id' => ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION,
                'is_multiselectable' => false,
                'as_image' => false,
                'is_mandatory' => true,
            ],
            [
                'id' => 2,
                'field_name' => [
                    'uk' => 'Тип матеріалу',
                    'ru' => 'Тип материала',
                ],
                'slug' => 'typ-materialu',
                'field_type_id' => ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION,
                'is_multiselectable' => false,
                'as_image' => false,
                'is_mandatory' => true,
            ],
            [
                'id' => 3,
                'field_name' => [
                    'uk' => 'Рапорт',
                    'ru' => 'Раппорт',
                ],
                'slug' => 'raport',
                'field_size_name' => [
                    'uk' => 'см.',
                    'ru' => 'см.',
                ],
                'field_type_id' => ProductFieldTypeOptionsDataClass::FIELD_TYPE_SIZE,
                'is_multiselectable' => false,
                'as_image' => false,
                'is_mandatory' => true,
                'numeric_field_filter_type_id' => NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_FROM_TO_INPUTS_TYPE,
            ],
            [
                'id' => 4,
                'field_name' => [
                    'uk' => 'Тип',
                    'ru' => 'Тип',
                ],
                'slug' => 'typ',
                'field_type_id' => ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION,
                'is_multiselectable' => false,
                'as_image' => false,
                'is_mandatory' => true,
            ],
            [
                'id' => 5,
                'field_name' => [
                    'uk' => 'Догляд',
                    'ru' => 'Уход',
                ],
                'slug' => 'doglyad',
                'field_type_id' => ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION,
                'is_multiselectable' => true,
                'as_image' => true,
                'is_mandatory' => true,
            ],
        ];

        foreach ($productFields as $productField) {
            $productField['creator_id'] = $creator->id;
            ProductField::create($productField);
        }

        $productFieldOptions = [
            [
                'id' => 1,
                'name' => [
                    'uk' => 'Геометрія',
                    'ru' => 'Геометрия',
                ],
                'slug' => 'geometr',
                'product_field_id' => 1,
            ],
            [
                'id' => 2,
                'name' => [
                    'uk' => 'Тварини',
                    'ru' => 'Животные',
                ],
                'slug' => 'tvarini',
                'product_field_id' => 1,
            ],
            [
                'id' => 3,
                'name' => [
                    'uk' => 'Однотипні',
                    'ru' => 'Однотоные',
                ],
                'slug' => 'odnotipni',
                'product_field_id' => 1,
            ],
            [
                'id' => 4,
                'name' => [
                    'uk' => 'Цегла',
                    'ru' => 'Кирпич',
                ],
                'slug' => 'cegla',
                'product_field_id' => 1,
            ],
            [
                'id' => 5,
                'name' => [
                    'uk' => 'Мрамор',
                    'ru' => 'Мрамор',
                ],
                'slug' => 'mramor',
                'product_field_id' => 1,
            ],
            [
                'id' => 6,
                'name' => [
                    'uk' => 'Природа',
                    'ru' => 'Природа',
                ],
                'slug' => 'priroda',
                'product_field_id' => 1,
            ],
            [
                'id' => 7,
                'name' => [
                    'uk' => 'Візерунки',
                    'ru' => 'Узоры',
                ],
                'slug' => 'vzerunki',
                'product_field_id' => 1,
            ],
            [
                'id' => 8,
                'name' => [
                    'uk' => 'Місто',
                    'ru' => 'Город',
                ],
                'slug' => 'misto',
                'product_field_id' => 1,
            ],
            [
                'id' => 9,
                'name' => [
                    'uk' => 'Квіти',
                    'ru' => 'Цветы',
                ],
                'slug' => 'kviti',
                'product_field_id' => 1,
            ],
            [
                'id' => 10,
                'name' => [
                    'uk' => 'Дерева',
                    'ru' => 'Деревья',
                ],
                'slug' => 'dereva',
                'product_field_id' => 1,
            ],
            [
                'id' => 11,
                'name' => [
                    'uk' => 'Схід',
                    'ru' => 'Восток',
                ],
                'slug' => 'shid',
                'product_field_id' => 1,
            ],
            [
                'id' => 12,
                'name' => [
                    'uk' => 'Акварель',
                    'ru' => 'Акварель',
                ],
                'slug' => 'akvarel',
                'product_field_id' => 1,
            ],
            [
                'id' => 13,
                'name' => [
                    'uk' => 'Флористика',
                    'ru' => 'Флористика',
                ],
                'slug' => 'floristika',
                'product_field_id' => 1,
            ],
            [
                'id' => 14,
                'name' => [
                    'uk' => 'Класика',
                    'ru' => 'Классика',
                ],
                'slug' => 'klasika',
                'product_field_id' => 1,
            ],
            [
                'id' => 15,
                'name' => [
                    'uk' => 'Горох',
                    'ru' => 'Горох',
                ],
                'slug' => 'goroh',
                'product_field_id' => 1,
            ],
            [
                'id' => 16,
                'name' => [
                    'uk' => 'Дитячі',
                    'ru' => 'Детские',
                ],
                'slug' => 'ditci',
                'product_field_id' => 1,
            ],
            [
                'id' => 17,
                'name' => [
                    'uk' => 'Паперові',
                    'ru' => 'Бумажные',
                ],
                'slug' => 'paperovi',
                'product_field_id' => 2,
            ],
            [
                'id' => 18,
                'name' => [
                    'uk' => 'Вінілові',
                    'ru' => 'Виниловые',
                ],
                'slug' => 'vinilovi',
                'product_field_id' => 2,
            ],
            [
                'id' => 19,
                'name' => [
                    'uk' => 'Флізелінові',
                    'ru' => 'Флизелиновые',
                ],
                'slug' => 'flizelinovi',
                'product_field_id' => 2,
            ],
            [
                'id' => 20,
                'name' => [
                    'uk' => 'Текстильні',
                    'ru' => 'Текстильные',
                ],
                'slug' => 'tekstilni',
                'product_field_id' => 2,
            ],
            [
                'id' => 21,
                'name' => [
                    'uk' => 'Під фарбування',
                    'ru' => 'Под покраску',
                ],
                'slug' => 'pid-farbuvannia',
                'product_field_id' => 2,
            ],
            [
                'id' => 22,
                'name' => [
                    'uk' => 'Натуральні',
                    'ru' => 'Натуральные',
                ],
                'slug' => 'naturalni',
                'product_field_id' => 2,
            ],
            [
                'id' => 23,
                'name' => [
                    'uk' => 'Рулонні',
                    'ru' => 'Релонные',
                ],
                'slug' => 'rulonni',
                'product_field_id' => 4,
            ],
            [
                'id' => 24,
                'name' => [
                    'uk' => 'Пано / Фотошпалери',
                    'ru' => 'Панно / Фотообои',
                ],
                'slug' => 'pano-fotospalery',
                'product_field_id' => 4,
            ],
            [
                'id' => 25,
                'name' => [
                    'uk' => 'Бордюри',
                    'ru' => 'Бордюры',
                ],
                'slug' => 'bordry',
                'product_field_id' => 4,
            ],
            [
                'id' => 26,
                'name' => [
                    'uk' => 'Погонними метрами',
                    'ru' => 'Погонными метрами',
                ],
                'slug' => 'pogonnymy-metramy',
                'product_field_id' => 4,
            ],
            [
                'id' => 27,
                'name' => [
                    'uk' => 'Наклейки',
                    'ru' => 'Наклейки',
                ],
                'slug' => 'nakleky',
                'product_field_id' => 4,
            ],


            [
                'id' => 28,
                'name' => [
                    'uk' => 'Знімаються після змочування',
                    'ru' => 'Снимаются после смачивания',
                ],
                'slug' => 'znimatsya-pislya-zmocuvannya',
                'product_field_id' => 5,
            ],
            [
                'id' => 29,
                'name' => [
                    'uk' => 'Знімаються шарами',
                    'ru' => 'Снимаются слоями',
                ],
                'slug' => 'znimatsya-saramy',
                'product_field_id' => 5,
            ],
            [
                'id' => 30,
                'name' => [
                    'uk' => 'Знімаються сухими',
                    'ru' => 'Снимаются сухими',
                ],
                'slug' => 'znimatsya-suhymy',
                'product_field_id' => 5,
            ],
            [
                'id' => 31,
                'name' => [
                    'uk' => 'Світлостійкі шпалери',
                    'ru' => 'Светостойкие обои',
                ],
                'slug' => 'svitlostiki-spalery',
                'product_field_id' => 5,
            ],
            [
                'id' => 32,
                'name' => [
                    'uk' => 'Зносостійкі шпалери',
                    'ru' => 'Износостойкие обои',
                ],
                'slug' => 'znosostiki-spalery',
                'product_field_id' => 5,
            ],
            [
                'id' => 33,
                'name' => [
                    'uk' => 'Особливо стійкі шпалери',
                    'ru' => 'Особо стойкие обои',
                ],
                'slug' => 'znosostiki-spalery',
                'product_field_id' => 5,
            ],
            [
                'id' => 34,
                'name' => [
                    'uk' => 'Особливо стійкі шпалери',
                    'ru' => 'Особо стойкие обои',
                ],
                'slug' => 'osoblyvo-stiki-spalery',
                'product_field_id' => 5,
            ],
            [
                'id' => 35,
                'name' => [
                    'uk' => 'Шпалери, що миються',
                    'ru' => 'Моющиеся обои',
                ],
                'slug' => 'spalery-o-mytsya',
                'product_field_id' => 5,
            ],
            [
                'id' => 36,
                'name' => [
                    'uk' => 'Вологостійкі шпалери',
                    'ru' => 'Влагостойкие обои',
                ],
                'slug' => 'vologostiki-spalery',
                'product_field_id' => 5,
            ],
            [
                'id' => 37,
                'name' => [
                    'uk' => 'Вологостійкі шпалери',
                    'ru' => 'Влагостойкие обои',
                ],
                'slug' => 'vologostiki-spalery',
                'product_field_id' => 5,
            ],
            [
                'id' => 38,
                'name' => [
                    'uk' => 'Нанесення клею на шпалери',
                    'ru' => 'Нанесение клея на обои',
                ],
                'slug' => 'vologostiki-spalery',
                'product_field_id' => 5,
            ],
            [
                'id' => 39,
                'name' => [
                    'uk' => 'Нанесення клею на шпалери',
                    'ru' => 'Нанесение клея на обои',
                ],
                'slug' => 'nanesennya-kle-na-spalery',
                'product_field_id' => 5,
            ],
            [
                'id' => 40,
                'name' => [
                    'uk' => 'Нанесення клею на стіну',
                    'ru' => 'Нанесение клея на стену',
                ],
                'slug' => 'nanesennya-kle-na-stinu',
                'product_field_id' => 5,
            ],
            [
                'id' => 41,
                'name' => [
                    'uk' => 'Розворот на 180 °',
                    'ru' => 'Разворот на 180°',
                ],
                'slug' => 'rozvorot-na-180',
                'product_field_id' => 5,
            ],
            [
                'id' => 42,
                'name' => [
                    'uk' => 'Ступінчасте поєднання',
                    'ru' => 'Ступенчатое совмещение',
                ],
                'slug' => 'stupincaste-podnannya',
                'product_field_id' => 5,
            ],
            [
                'id' => 43,
                'name' => [
                    'uk' => 'Симетричне розташування малюнка',
                    'ru' => 'Симметричное расположение рисунка',
                ],
                'slug' => 'symetrycne-roztasuvannya-malnka',
                'product_field_id' => 5,
            ],
            [
                'id' => 44,
                'name' => [
                    'uk' => 'Довільна поклейка',
                    'ru' => 'Произвольная поклейка',
                ],
                'slug' => 'dovilna-pokleka',
                'product_field_id' => 5,
            ],
        ];

        foreach ($productFieldOptions as $productFieldOption) {
            if ($productFieldOption['product_field_id'] == 5) {
                $newImagePath = ProductFieldService::OPTION_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '.jpg';
                $image = Image::make($basePath . '/' . $productFieldOption['slug'] . '.png')
                    ->resize(150, 150)
                    ->encode('jpg', 100);

                Storage::disk(config('app.images_disk_default'))->put($newImagePath, $image);

                $productFieldOption['image_path'] = $newImagePath;

                ProductFieldOption::create($productFieldOption);

            } else {
                ProductFieldOption::create($productFieldOption);
            }


        }
    }
}
