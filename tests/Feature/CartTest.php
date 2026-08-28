<?php

namespace Tests\Feature;

use App\Models\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesShopData;
use Tests\TestCase;

/**
 * The cart, as a visitor who is not signed in meets it.
 *
 * These are the checks that used to be done by hand: add something, look at
 * what the cart holds, change the quantity, look again. Written down they run
 * in a second, and they run on every change from here on.
 */
class CartTest extends TestCase
{
    use RefreshDatabase;
    use MakesShopData;

    private function addToCart(string $slug, int $count = 1)
    {
        return $this->keepCookies($this->postJson(
            route('store.cart.add-product', ['productSlug' => $slug]),
            ['product_count' => $count]
        ));
    }

    public function test_a_guest_can_put_a_product_in_the_cart(): void
    {
        $product = $this->makeProduct();

        $this->addToCart($product->slug)->assertOk();

        $cart = Cart::first();

        $this->assertNotNull($cart, 'Кошик не створився.');
        $this->assertCount(1, $cart->products);
        $this->assertSame($product->id, $cart->products->first()->id);
    }

    public function test_the_cart_keeps_the_quantity_that_was_asked_for(): void
    {
        $product = $this->makeProduct();

        $this->addToCart($product->slug, 3)->assertOk();

        $this->assertSame(3, (int) Cart::first()->products->first()->pivot->count);
    }

    public function test_the_cart_remembers_the_price_at_the_time_it_was_added(): void
    {
        $product = $this->makeProduct(['price' => 5000]);

        $this->addToCart($product->slug)->assertOk();

        // The price the customer agreed to, not whatever the product costs
        // when the order is looked at later.
        $product->update(['price' => 9999]);

        $this->assertSame(5000.0, (float) Cart::first()->products->first()->pivot->price);
    }

    public function test_the_quantity_can_be_changed(): void
    {
        $product = $this->makeProduct();

        $this->addToCart($product->slug, 1)->assertOk();

        $this->keepCookies($this->postJson(
            route('store.cart.change-product-count', ['productSlug' => $product->slug]),
            ['product_count' => 4]
        ))->assertOk();

        $this->assertSame(4, (int) Cart::first()->products->first()->pivot->count);
    }

    public function test_a_product_can_be_taken_out_of_the_cart(): void
    {
        $product = $this->makeProduct();

        $this->addToCart($product->slug)->assertOk();
        $this->assertCount(1, Cart::first()->products);

        $this->keepCookies(
            $this->postJson(route('store.cart.delete-product', ['productSlug' => $product->slug]))
        )->assertOk();

        $this->assertCount(0, Cart::first()->fresh()->products);
    }

    public function test_two_visitors_do_not_share_a_cart(): void
    {
        $product = $this->makeProduct();

        $this->addToCart($product->slug)->assertOk();
        $firstCartId = Cart::first()->id;

        $this->asNewVisitor()->addToCart($product->slug)->assertOk();

        $this->assertSame(
            2,
            Cart::count(),
            'Другий відвідувач мав отримати власний кошик, а не чужий.'
        );
        $this->assertNotSame($firstCartId, Cart::orderByDesc('id')->first()->id);
    }

    public function test_the_cart_refuses_a_quantity_below_one(): void
    {
        $product = $this->makeProduct();

        $this->addToCart($product->slug, 0)->assertStatus(422);

        $this->assertSame(0, Cart::count(), 'Кошик не мав створюватись від недійсного запиту.');
    }

    public function test_an_unknown_product_cannot_be_added(): void
    {
        $this->addToCart('there-is-no-such-door')->assertNotFound();

        $this->assertSame(0, Cart::count());
    }
}
