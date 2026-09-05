<?php

namespace Tests\Feature;

use App\Http\Resources\Store\Cart\CartProductResource;
use App\Models\Cart;
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
        $cart = Cart::create(['token' => 'resource-test']);
        $cart->products()->attach($product->id, [
            'count' => 2,
            'price' => 4000,
            'attributes_price' => 320,
            'attributes' => json_encode([
                'color_id' => 148,
                'color_name' => 'Айворі',
                'product_attribute_12' => json_encode([
                    'id' => 55,
                    'name' => json_encode(['uk' => 'Приховані завіси', 'ru' => 'Скрытые петли']),
                ]),
            ]),
        ]);

        $line = $cart->products()->with(['brand', 'colors', 'productType'])->firstOrFail();
        $resource = (new CartProductResource($line))->resolve(request());

        $this->assertSame('Міжкімнатні двері', $resource['display_name']);
        $this->assertSame(8640.0, $resource['line_total']);
        $this->assertSame([
            ['key' => 'color_name', 'id' => 148, 'label' => 'Айворі'],
            ['key' => 'product_attribute_12', 'id' => 55, 'label' => 'Приховані завіси'],
        ], $resource['configuration']);
    }
}
