<?php

namespace Tests\Feature;

use App\Models\ServicesPageSections;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class StorefrontSearchTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_it_returns_grouped_limited_products_and_services(): void
    {
        foreach (range(1, 4) as $number) {
            $this->makeProduct([
                'name' => ['uk' => 'Тест двері '.$number, 'ru' => 'Тест дверь '.$number],
                'sku' => 'SEARCH-'.$number,
            ]);
        }

        $this->makeProduct([
            'name' => ['uk' => 'Тест приховані двері', 'ru' => 'Тест скрытая дверь'],
            'sku' => 'SEARCH-HIDDEN',
            'is_active' => false,
        ]);

        foreach (range(1, 3) as $number) {
            ServicesPageSections::create([
                'title' => ['uk' => 'Тест послуга '.$number, 'ru' => 'Тест услуга '.$number],
                'description' => ['uk' => 'Опис послуги', 'ru' => 'Описание услуги'],
                'button_text' => ['uk' => 'Замовити', 'ru' => 'Заказать'],
                'button_url' => '#',
                'section_image_path' => 'test/service-'.$number.'.webp',
            ]);
        }

        $response = $this->postJson(route('store.product.search'), ['query' => 'Тест']);

        $response
            ->assertOk()
            ->assertJsonCount(3, 'data.products')
            ->assertJsonCount(2, 'data.services')
            ->assertJsonMissing(['sku' => 'SEARCH-HIDDEN'])
            ->assertJsonPath('data.services.0.link', '/services#service-1');
    }

    public function test_it_rejects_queries_shorter_than_three_characters(): void
    {
        $this->postJson(route('store.product.search'), ['query' => 'те'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('query');
    }
}
