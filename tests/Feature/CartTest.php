<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\ServicesPageSections;
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
    use MakesShopData;
    use RefreshDatabase;

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

    public function test_looking_at_the_cart_summary_does_not_create_one(): void
    {
        // The header asks for this on every page load. It used to answer by
        // creating a cart, which is how the table came to hold a hundred and
        // eighty thousand of them with six in use.
        $this->keepCookies($this->getJson(route('store.cart.products-with-summary')))->assertOk();

        $this->assertSame(0, Cart::count(), 'Перегляд підсумку не має створювати кошик.');
    }

    public function test_cart_page_uses_the_reference_layout_and_admin_managed_services(): void
    {
        ServicesPageSections::create([
            'slug' => 'qa-measurement',
            'title' => ['uk' => 'Замір прорізів', 'ru' => 'Замер проемов'],
            'description' => ['uk' => 'Перевіримо розміри до виробництва.', 'ru' => 'Проверим размеры до производства.'],
            'button_text' => ['uk' => 'Додати послугу', 'ru' => 'Добавить услугу'],
            'button_url' => '/services/qa-measurement',
            'section_image_path' => 'assets/images/services-measurement.webp',
            'sort_order' => 0,
        ]);

        $this->get(route('store.cart.page'))
            ->assertOk()
            ->assertSee('data-cart-page', false)
            ->assertSee('bona-order-summary', false)
            ->assertSee('bona-cart-services', false)
            ->assertSee('Замір прорізів');

        $this->assertSame(0, Cart::count(), 'Перегляд сторінки кошика не має створювати порожній кошик.');
    }

    public function test_the_wish_list_page_does_not_create_a_cart(): void
    {
        $this->seedCurrency();

        $this->get(route('store.wishlist.private.page'))->assertOk();

        $this->assertSame(0, Cart::count());
    }
}
