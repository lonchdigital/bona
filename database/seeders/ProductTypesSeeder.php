<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\ProductType;
use Illuminate\Database\Seeder;
use App\DataClasses\ProductSizeTypesDataClass;
use App\DataClasses\NumericFieldFilerTypesDataClass;
use App\DataClasses\ProductFilterFullPositionOptionsDataClass;


class ProductTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $creator = User::where('role_id', Role::ADMIN_ROLE_ID)->first();

        if (!$creator) {
            throw new \Exception('User with admin roles is not exists!');
        }

        $productType = ProductType::create([
            'creator_id' => $creator->id,
            'name' => [
                'uk' => 'Шпалери',
                'ru' => 'Обои',
            ],
            'meta_title' => [
                'uk' => 'Шпалери',
                'ru' => 'Обои',
            ],
            'meta_description' => [
                'uk' => 'Купити шпалери! Ціна від 51.00 грн. В наявності 9555 шт.',
                'ru' => 'Купить обои! Цена от 51.00 грн. В наличии 955 шт.',
            ],
            'meta_keywords' => [
                'uk' => 'Шпалери в Києві',
                'ru' => 'Обои в Киеве',
            ],
            'slug' => config('domain.wallpaper_product_type_slug'),
            'has_brand' => true,
            'has_color' => true,
            'has_collection' => true,
            'has_category' => true,
            'has_size' => true,
            'has_length' => true,
            'filter_by_length' => false,
            'has_width' => true,
            'filter_by_width' => true,
            'product_size_width_filter_type_id' => NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_OPTIONS_TYPE,
            'product_size_width_show_on_main_filter' => true,
            'product_size_width_filter_full_position_id' => ProductFilterFullPositionOptionsDataClass::FILTER_POSITION_MIDDLE,
            'product_size_width_filter_name' => [
                'uk' => 'За шириною рулону',
                'ru' => 'По ширине рулона',
            ],
            'has_height' => false,
            'filter_by_height' => false,
            'size_points' => [
                'uk' => 'см.',
                'ru' => 'см.',
            ],
        ]);

        $productType->fields()->sync([
            [
                'product_field_id' => 1,
                'show_as_filter' => true,
                'show_on_main_filters_list' => true,
                'filter_name' => [
                    'uk' => 'За типом малюнку',
                    'ru' => 'По типу рисунка',
                ],
                'filter_full_position_id' => ProductFilterFullPositionOptionsDataClass::FILTER_POSITION_LEFT,
            ],
            [
                'product_field_id' => 2,
                'show_as_filter' => true,
                'show_on_main_filters_list' => true,
                'filter_name' => [
                    'uk' => 'За типом основи',
                    'ru' => 'По типу основы',
                ],
                'filter_full_position_id' => ProductFilterFullPositionOptionsDataClass::FILTER_POSITION_MIDDLE,
            ],
            [
                'product_field_id' => 3,
                'show_as_filter' => false,
                'show_on_main_filters_list' => false,
            ],
            [
                'product_field_id' => 4,
                'show_as_filter' => false,
                'show_on_main_filters_list' => false,
            ],
            [
                'product_field_id' => 5,
                'show_as_filter' => false,
                'show_on_main_filters_list' => false,
            ],
        ]);

        $productType->sizeFilterOptions()->createMany([
            [
                'type' => ProductSizeTypesDataClass::WIDTH,
                'name' => [
                   'uk' => '0.47 - 0.53 м.',
                   'ru' => '0.47 - 0.53 м.',
                ],
                'slug' => '0_47-0_53',
                'from' => 0.47,
                'to' => 0.53,
            ],
            [
                'type' => ProductSizeTypesDataClass::WIDTH,
                'name' => [
                    'uk' => '0.62 - 0.75 м.',
                    'ru' => '0.62 - 0.75 м.',
                ],
                'slug' => '0_62-0_75',
                'from' => 0.62,
                'to' => 0.75,
            ],
            [
                'type' => ProductSizeTypesDataClass::WIDTH,
                'name' => [
                    'uk' => '0.8 - 1 м.',
                    'ru' => '0.8 - 1 м.',
                ],
                'slug' => '0_8-1',
                'from' => 0.8,
                'to' => 1,
            ],
            [
                'type' => ProductSizeTypesDataClass::WIDTH,
                'name' => [
                    'uk' => '1 - 1,37 м.',
                    'ru' => '1 - 1,37 м.',
                ],
                'slug' => '1-1_37',
                'from' => 1,
                'to' => 1.37,
            ],
        ]);
    }
}
