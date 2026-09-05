<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartProducts;
use App\Models\Category;
use App\Models\ServicesPageSections;
use App\Support\Commerce\ProductBundle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
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

    public function test_adding_the_same_line_twice_adds_the_requested_quantity(): void
    {
        $product = $this->makeProduct();

        $this->addToCart($product->slug, 2)->assertOk();
        $this->addToCart($product->slug, 3)->assertOk();

        $this->assertSame(5, (int) Cart::first()->products->first()->pivot->count);
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

    public function test_a_configured_cart_line_can_be_changed_and_removed_without_exposing_raw_attributes(): void
    {
        $product = $this->makeProduct();

        $this->addToCart($product->slug)->assertOk();

        $cart = Cart::firstOrFail();
        $cart->products()->updateExistingPivot($product->id, [
            'attributes' => json_encode([
                'color_id' => 148,
                'color_name' => 'Айворі',
                'product_attribute_12' => json_encode([
                    'id' => 55,
                    'name' => json_encode(['uk' => 'Приховані завіси']),
                ]),
            ]),
        ]);

        $configuration = [
            'color_name' => '148',
            'product_attribute_12' => '55',
        ];

        $this->keepCookies($this->postJson(
            route('store.cart.change-product-count', ['productSlug' => $product->slug]),
            ['product_count' => 3, 'product_attributes' => $configuration]
        ))->assertOk();

        $this->assertSame(3, (int) $cart->fresh()->products->first()->pivot->count);

        $this->keepCookies($this->postJson(
            route('store.cart.delete-product', ['productSlug' => $product->slug]),
            ['product_attributes' => $configuration]
        ))->assertOk();

        $this->assertCount(0, $cart->fresh()->products);
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

    public function test_a_door_configuration_stays_grouped_and_each_line_can_be_changed_independently(): void
    {
        $frame = $this->makeProduct(['name' => ['uk' => 'Короб телескопічний', 'ru' => 'Короб телескопический']]);
        $trim = $this->makeProduct(['name' => ['uk' => 'Лиштва', 'ru' => 'Наличник']]);
        $door = $this->makeProduct([
            'name' => ['uk' => 'ArtPort New York', 'ru' => 'ArtPort New York'],
            'sub_products' => json_encode([$frame->id, $trim->id]),
        ]);
        $category = Category::create([
            'creator_id' => $this->author()->id,
            'product_type_id' => $frame->product_type_id,
            'name' => ['uk' => 'Короб', 'ru' => 'Короб'],
            'slug' => 'cart-bundle-frame',
            'image_path' => 'test/frame.webp',
        ]);
        $frame->categories()->attach($category->id);
        $trim->categories()->attach($category->id);
        $bundleKey = (string) Str::uuid();

        $this->keepCookies($this->postJson(
            route('store.cart.add-product', ['productSlug' => $door->slug]),
            [
                'product_count' => 1,
                'bundle_key' => $bundleKey,
                'product_attributes' => [
                    'color_id' => null,
                    'product_attribute_12' => json_encode([
                        'id' => 55,
                        'name' => ['uk' => 'Праве', 'ru' => 'Правое'],
                    ]),
                ],
            ]
        ))->assertOk();

        foreach ([$frame, $trim] as $component) {
            $this->keepCookies($this->postJson(
                route('store.cart.add-sub-product', ['productSlug' => $component->slug]),
                ['product_count' => 1, 'bundle_key' => $bundleKey]
            ))->assertOk();
        }

        $lines = CartProducts::query()->where('bundle_key', $bundleKey)->orderBy('id')->get();
        $this->assertCount(3, $lines);
        $this->assertSame(ProductBundle::ROLE_PARENT, $lines->first()->bundle_role);
        $this->assertSame(ProductBundle::ROLE_ITEM, $lines->get(1)->bundle_role);

        $response = $this->keepCookies($this->getJson(route('store.cart.products-with-summary')))->assertOk();
        $response->assertJsonFragment([
            'key' => $bundleKey,
            'role' => ProductBundle::ROLE_ITEM,
            'category' => 'Короб',
        ]);
        $response->assertJsonFragment(['line_id' => $lines->get(1)->id]);

        $this->keepCookies($this->postJson(
            route('store.cart.change-product-count', ['productSlug' => $frame->slug]),
            ['product_count' => 2, 'cart_line_id' => $lines->get(1)->id]
        ))->assertOk();

        $this->assertSame(2, (int) $lines->get(1)->fresh()->count);
        $this->assertSame(1, (int) $lines->get(2)->fresh()->count);

        $this->keepCookies($this->postJson(
            route('store.cart.delete-product', ['productSlug' => $trim->slug]),
            ['cart_line_id' => $lines->get(2)->id]
        ))->assertOk();
        $this->assertDatabaseMissing('cart_products', ['id' => $lines->get(2)->id]);
        $this->assertDatabaseHas('cart_products', ['id' => $lines->first()->id]);

        $this->keepCookies($this->postJson(
            route('store.cart.delete-product', ['productSlug' => $door->slug]),
            ['cart_line_id' => $lines->first()->id]
        ))->assertOk();
        $this->assertDatabaseMissing('cart_products', ['bundle_key' => $bundleKey]);
    }

    public function test_a_configured_cart_saved_before_bundle_metadata_is_upgraded_when_it_is_opened(): void
    {
        $frame = $this->makeProduct(['name' => ['uk' => 'Короб телескопічний', 'ru' => 'Короб телескопический']]);
        $trim = $this->makeProduct(['name' => ['uk' => 'Добір 200 мм', 'ru' => 'Добор 200 мм']]);
        $door = $this->makeProduct([
            'name' => ['uk' => 'ArtPort New York', 'ru' => 'ArtPort New York'],
            'sub_products' => json_encode([$frame->id, $trim->id]),
        ]);
        $category = Category::create([
            'creator_id' => $this->author()->id,
            'product_type_id' => $frame->product_type_id,
            'name' => ['uk' => 'Комплектація', 'ru' => 'Комплектация'],
            'slug' => 'legacy-cart-bundle-components',
            'image_path' => 'test/component.webp',
        ]);
        $frame->categories()->attach($category->id);
        $trim->categories()->attach($category->id);

        $this->addToCart($door->slug, 3)->assertOk();
        $cart = Cart::firstOrFail();
        foreach ([$frame, $trim] as $component) {
            CartProducts::create([
                'cart_id' => $cart->id,
                'product_id' => $component->id,
                'count' => 1,
                'price' => $component->price,
                'attributes_price' => 0,
            ]);
        }

        $response = $this->keepCookies($this->getJson(route('store.cart.products-with-summary')))
            ->assertOk();
        $lines = CartProducts::query()->where('cart_id', $cart->id)->orderBy('id')->get();
        $bundleKey = $lines->first()->bundle_key;

        $this->assertNotNull($bundleKey);
        $this->assertSame(ProductBundle::ROLE_PARENT, $lines->first()->bundle_role);
        $this->assertSame([$bundleKey], $lines->skip(1)->pluck('bundle_key')->unique()->values()->all());
        $this->assertSame(
            [ProductBundle::ROLE_ITEM],
            $lines->skip(1)->pluck('bundle_role')->unique()->values()->all(),
        );
        $response->assertJsonFragment([
            'key' => $bundleKey,
            'role' => ProductBundle::ROLE_ITEM,
            'category' => 'Комплектація',
        ]);

        $this->keepCookies($this->getJson(route('store.cart.products-with-summary')))->assertOk();
        $this->assertSame(
            $bundleKey,
            CartProducts::query()->where('cart_id', $cart->id)->oldest('id')->value('bundle_key'),
            'Повторне відкриття кошика не повинно створювати новий комплект.',
        );
    }

    public function test_legacy_rows_are_not_grouped_across_an_unrelated_cart_item(): void
    {
        $frame = $this->makeProduct(['name' => ['uk' => 'Короб', 'ru' => 'Короб']]);
        $unrelated = $this->makeProduct(['name' => ['uk' => 'Ручка', 'ru' => 'Ручка']]);
        $door = $this->makeProduct(['sub_products' => json_encode([$frame->id])]);

        $this->addToCart($door->slug)->assertOk();
        $cart = Cart::firstOrFail();
        foreach ([$unrelated, $frame] as $product) {
            CartProducts::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'count' => 1,
                'price' => $product->price,
                'attributes_price' => 0,
            ]);
        }

        $this->keepCookies($this->getJson(route('store.cart.products-with-summary')))->assertOk();

        $this->assertSame(0, CartProducts::query()->where('cart_id', $cart->id)->whereNotNull('bundle_key')->count());
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
            ->assertSee('data-lead-modal-open="dialog-call-measurer"', false)
            ->assertSee('id="dialog-call-measurer"', false)
            ->assertSee('Замір прорізів');

        $cartScript = file_get_contents(resource_path('js/store/common/cart.js'));
        $this->assertStringContainsString('initCartItemInteractions();', $cartScript);
        $this->assertStringContainsString('data-committed-value=', $cartScript);
        $this->assertStringContainsString('data-attribute-key=', $cartScript);
        $this->assertStringNotContainsString('bona-cart-attribute-meta attribute-key', $cartScript);

        $this->assertSame(0, Cart::count(), 'Перегляд сторінки кошика не має створювати порожній кошик.');
    }

    public function test_the_wish_list_page_does_not_create_a_cart(): void
    {
        $this->seedCurrency();

        $this->get(route('store.wishlist.private.page'))->assertOk();

        $this->assertSame(0, Cart::count());
    }
}
