<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Services\HomePage\HomePageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class HomePageCatalogCardsTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_it_resolves_product_types_and_nested_categories_in_the_selected_order(): void
    {
        $doors = $this->productType([
            'slug' => 'interior-doors',
            'name' => ['uk' => 'Міжкімнатні двері', 'ru' => 'Межкомнатные двери'],
        ]);
        $accessories = $this->productType([
            'slug' => 'accessories',
            'name' => ['uk' => 'Аксесуари', 'ru' => 'Аксессуары'],
            'image_path' => 'test/accessories.webp',
        ]);
        $handles = Category::query()->create([
            'creator_id' => $this->author()->id,
            'product_type_id' => $accessories->id,
            'name' => ['uk' => 'Дверні ручки', 'ru' => 'Дверные ручки'],
            'slug' => 'door-handles',
            'image_path' => null,
        ]);

        $cards = app(HomePageService::class)->getHomePageCatalogCards([
            (string) $doors->id,
            'category:'.$handles->id,
        ]);

        $this->assertSame(['Міжкімнатні двері', 'Дверні ручки'], $cards->pluck('name')->all());
        $this->assertSame('/product-category/interior-doors', $cards[0]['url']);
        $this->assertSame('/product-category/accessories/category/door-handles', $cards[1]['url']);
        $this->assertSame('/storage/test/accessories.webp', $cards[1]['image_url']);
    }

    public function test_admin_options_include_nested_categories_with_composite_ids(): void
    {
        $accessories = $this->productType([
            'slug' => 'accessories',
            'name' => ['uk' => 'Аксесуари', 'ru' => 'Аксессуары'],
        ]);
        $handles = Category::query()->create([
            'creator_id' => $this->author()->id,
            'product_type_id' => $accessories->id,
            'name' => ['uk' => 'Дверні ручки', 'ru' => 'Дверные ручки'],
            'slug' => 'door-handles',
            'image_path' => null,
        ]);

        $option = app(HomePageService::class)
            ->getHomePageCatalogOptions()
            ->firstWhere('id', 'category:'.$handles->id);

        $this->assertNotNull($option);
        $this->assertSame('Дверні ручки — Аксесуари', $option['name']['uk']);
    }
}
