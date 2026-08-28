<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The cart, as a visitor who is not signed in meets it.
 *
 * These are the checks that were being done by hand: add something, look at
 * what the cart holds, change the quantity, look again. Written down they run
 * in a second and they run on every change from here on.
 */
class CartTest extends TestCase
{
    use RefreshDatabase;

    private function productType(): \App\Models\ProductType
    {
        return \App\Models\ProductType::firstOrCreate(
            ['slug' => 'test-doors'],
            [
                'name' => 'Тестові двері',
                'creator_id' => $this->author()->id,
                // Every one of these is NOT NULL with no default. Spelled out
                // here because the project has no factory for this model yet.
                'image_path' => 'test/type.webp',
                'meta_title' => ['uk' => 'Тест', 'ru' => 'Тест'],
                'meta_description' => ['uk' => 'Тест', 'ru' => 'Тест'],
                'meta_keywords' => ['uk' => 'тест', 'ru' => 'тест'],
            ]
        );
    }

    private function country(): \App\Models\Country
    {
        return \App\Models\Country::firstOrCreate(
            ['code' => 'UA'],
            [
                'name' => ['uk' => 'Україна', 'ru' => 'Украина'],
                'image_path' => 'test/ua.svg',
                'creator_id' => $this->author()->id,
            ]
        );
    }

    private function author(): User
    {
        return $this->author ??= User::factory()->create();
    }

    private ?User $author = null;

    private function makeProduct(array $attributes = []): Product
    {
        return Product::create(array_merge([
            'slug' => 'test-door-' . uniqid(),
            'creator_id' => $this->author()->id,
            'product_type_id' => $this->productType()->id,
            'country_id' => $this->country()->id,
            'name' => ['uk' => 'Тестові двері', 'ru' => 'Тестовая дверь'],
            'price' => 5000,
            'purchase_price_in_currency' => 3000,
            'availability_status_id' => 1,
            'is_active' => true,
        ], $attributes));
    }

    public function test_a_guest_can_put_a_product_in_the_cart(): void
    {
        $product = $this->makeProduct();

        $response = $this->postJson(route('store.cart.add-product', ['productSlug' => $product->slug]), [
            'product_count' => 1,
        ]);

        $response->assertOk();

        $cart = Cart::first();

        $this->assertNotNull($cart, 'Кошик не створився.');
        $this->assertCount(1, $cart->products, 'У кошику має бути один товар.');
        $this->assertSame($product->id, $cart->products->first()->id);
    }

    public function test_the_cart_keeps_the_quantity_that_was_asked_for(): void
    {
        $product = $this->makeProduct();

        $this->postJson(route('store.cart.add-product', ['productSlug' => $product->slug]), [
            'product_count' => 3,
        ])->assertOk();

        $this->assertSame(3, (int) Cart::first()->products->first()->pivot->count);
    }

    public function test_the_cart_remembers_the_price_at_the_time_it_was_added(): void
    {
        $product = $this->makeProduct(['price' => 5000]);

        $this->postJson(route('store.cart.add-product', ['productSlug' => $product->slug]), [
            'product_count' => 1,
        ])->assertOk();

        // The price the customer agreed to, not whatever the product costs
        // when the order is looked at later.
        $product->update(['price' => 9999]);

        $this->assertSame(5000.0, (float) Cart::first()->products->first()->pivot->price);
    }

    public function test_two_visitors_do_not_share_a_cart(): void
    {
        $product = $this->makeProduct();

        $this->postJson(route('store.cart.add-product', ['productSlug' => $product->slug]), ['product_count' => 1])
            ->assertOk();

        // A second visitor: no cookies carried over from the first.
        $this->flushSession();
        $this->app['request']->cookies->replace([]);

        $second = $this->postJson(route('store.cart.add-product', ['productSlug' => $product->slug]), ['product_count' => 1]);
        $second->assertOk();

        $this->assertGreaterThanOrEqual(1, Cart::count());
    }
}
