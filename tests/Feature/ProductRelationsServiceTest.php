<?php

namespace Tests\Feature;

use App\Models\ProductAttribute;
use App\Models\ProductAttributeOptions;
use App\Services\Product\ProductRelationsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class ProductRelationsServiceTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_attribute_options_are_created_updated_and_removed_without_stopping_the_request(): void
    {
        $product = $this->makeProduct();
        $attribute = ProductAttribute::create([
            'attribute_name' => ['uk' => 'Розмір', 'ru' => 'Размер'],
            'slug' => 'size',
        ]);
        $existing = ProductAttributeOptions::create([
            'product_id' => $product->id,
            'product_attribute_id' => $attribute->id,
            'name' => ['uk' => 'Старий', 'ru' => 'Старый'],
            'price' => 10,
        ]);

        app(ProductRelationsService::class)->syncAttributes($product->id, [
            $attribute->id => [
                [
                    'id' => $existing->id,
                    'name' => ['uk' => 'Оновлений', 'ru' => 'Обновлённый'],
                    'price' => 12.50,
                ],
                [
                    'name' => ['uk' => 'Новий', 'ru' => 'Новый'],
                    'price' => 20,
                ],
            ],
        ]);

        $this->assertDatabaseCount('product_attribute_options', 2);
        $this->assertSame('Оновлений', $existing->fresh()->getTranslation('name', 'uk'));
        $this->assertDatabaseHas('product_attribute_options', [
            'product_id' => $product->id,
            'product_attribute_id' => $attribute->id,
            'price' => 20,
        ]);

        app(ProductRelationsService::class)->syncAttributes($product->id, [
            $attribute->id => [[
                'id' => $existing->id,
                'name' => ['uk' => 'Залишився', 'ru' => 'Остался'],
                'price' => 15,
            ]],
        ]);

        $this->assertDatabaseCount('product_attribute_options', 1);
        $this->assertDatabaseHas('product_attribute_options', ['id' => $existing->id, 'price' => 15]);
    }

    public function test_an_attribute_option_from_another_product_cannot_be_modified(): void
    {
        $product = $this->makeProduct();
        $otherProduct = $this->makeProduct();
        $attribute = ProductAttribute::create([
            'attribute_name' => ['uk' => 'Колір', 'ru' => 'Цвет'],
            'slug' => 'colour',
        ]);
        $foreignOption = ProductAttributeOptions::create([
            'product_id' => $otherProduct->id,
            'product_attribute_id' => $attribute->id,
            'name' => ['uk' => 'Чужий', 'ru' => 'Чужой'],
            'price' => 5,
        ]);

        $this->expectException(RuntimeException::class);

        app(ProductRelationsService::class)->syncAttributes($product->id, [
            $attribute->id => [[
                'id' => $foreignOption->id,
                'name' => ['uk' => 'Зламаний', 'ru' => 'Сломанный'],
                'price' => 999,
            ]],
        ]);
    }
}
