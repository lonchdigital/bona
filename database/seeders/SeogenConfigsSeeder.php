<?php

namespace Database\Seeders;

use App\Models\SeoGenConfig;
use Illuminate\Database\Seeder;

class SeogenConfigsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $seogenConfigs = [
            [
                'page_type' => 'PRODUCT_CATEGORY',
                'product_type_id' => 1,
                'html_title_tag' => [
                    'uk' => '[category_name]',
                    'ru' => '[category_name]',
                ],
                'html_h1_tag' => [
                    'uk' => '[category_name]',
                    'ru' => '[category_name]',
                ],
                'meta_title_tag' => [
                    'uk' => '[category_name]',
                    'ru' => '[category_name]',
                ],
                'meta_description_tag' => [
                    'uk' => '[category_name]',
                    'ru' => '[category_name]',
                ],
                'meta_keywords_tag' => [
                    'uk' => '[category_name]',
                    'ru' => '[category_name]',
                ],
            ],
            [
                'page_type' => 'PRODUCT',
                'product_type_id' => 1,
                'html_title_tag' => [
                    'uk' => '[product_name]',
                    'ru' => '[product_name]',
                ],
                'html_h1_tag' => [
                    'uk' => '[product_name]',
                    'ru' => '[product_name]',
                ],
                'meta_title_tag' => [
                    'uk' => '[product_name]',
                    'ru' => '[product_name]',
                ],
                'meta_description_tag' => [
                    'uk' => '[product_name]',
                    'ru' => '[product_name]',
                ],
                'meta_keywords_tag' => [
                    'uk' => '[product_name]',
                    'ru' => '[product_name]',
                ],
            ],
            [
                'page_type' => 'BRAND',
                'product_type_id' => 1,
                'html_title_tag' => [
                    'uk' => '[brand_name]',
                    'ru' => '[brand_name]',
                ],
                'html_h1_tag' => [
                    'uk' => '[brand_name]',
                    'ru' => '[brand_name]',
                ],
                'meta_title_tag' => [
                    'uk' => '[brand_name]',
                    'ru' => '[brand_name]',
                ],
                'meta_description_tag' => [
                    'uk' => '[brand_name]',
                    'ru' => '[brand_name]',
                ],
                'meta_keywords_tag' => [
                    'uk' => '[brand_name]',
                    'ru' => '[brand_name]',
                ],
            ],
        ];

        foreach ($seogenConfigs as $seogenConfig) {
            SeoGenConfig::create($seogenConfig);
        }
    }
}
