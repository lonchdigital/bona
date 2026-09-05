<?php

namespace Tests\Feature;

use App\Http\Resources\Store\Cart\CartProductResource;
use App\Models\Cart;
use App\Models\Color;
use App\Models\ProductAttribute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class CartProductResourceTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_cart_configuration_exposes_labels_without_printing_internal_keys_or_ids(): void
    {
        app()->setLocale('uk');
        $product = $this->makeProduct([
            'name' => ['uk' => 'Міжкімнатні двері', 'ru' => 'Межкомнатные двери'],
            'price' => 4000,
        ]);
        $color = Color::create([
            'creator_id' => $this->author()->id,
            'name' => ['uk' => 'Айворі', 'ru' => 'Айвори'],
            'slug' => 'ivory-resource-test',
            'display_as_image' => false,
            'hex' => '#e8dfcf',
        ]);
        $attribute = ProductAttribute::create([
            'attribute_name' => ['uk' => 'Відкривання', 'ru' => 'Открывание'],
            'slug' => 'opening-resource-test',
        ]);
        $product->colors()->attach($color->id, ['price' => 0]);
        $product->productType->attributes()->attach($attribute->id);
        $cart = Cart::create(['token' => 'resource-test']);
        $cart->products()->attach($product->id, [
            'count' => 2,
            'price' => 4000,
            'attributes_price' => 320,
            'attributes' => json_encode([
                'color_id' => $color->id,
                'color_name' => 'Айворі',
                'product_attribute_'.$attribute->id => json_encode([
                    'id' => 55,
                    'name' => json_encode(['uk' => 'Приховані завіси', 'ru' => 'Скрытые петли']),
                ]),
            ]),
        ]);

        $line = $cart->products()->with(['brand', 'colors', 'productType.attributes'])->firstOrFail();
        $resource = (new CartProductResource($line))->resolve(request());

        $this->assertSame('Міжкімнатні двері', $resource['display_name']);
        $this->assertSame(8640.0, $resource['line_total']);
        $this->assertSame((int) $line->pivot->id, $resource['line_id']);
        $this->assertSame([
            ['key' => 'color_name', 'id' => $color->id, 'name' => 'Колір', 'label' => 'Айворі', 'swatch' => '#e8dfcf'],
            ['key' => 'product_attribute_'.$attribute->id, 'id' => 55, 'name' => 'Відкривання', 'label' => 'Приховані завіси', 'swatch' => null],
        ], $resource['configuration']);
    }
}
