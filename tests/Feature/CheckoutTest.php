<?php

namespace Tests\Feature;

use App\DataClasses\DeliveryTypesDataClass;
use App\DataClasses\OrderStatusesDataClass;
use App\DataClasses\PaymentTypesDataClass;
use App\DataClasses\RecipientTypesDataClass;
use App\Models\Cart;
use App\Models\Order;
use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesShopData;
use Tests\TestCase;

/**
 * Turning a cart into an order.
 *
 * The one path where a mistake costs money directly, so it is checked from the
 * outside: post what the checkout form posts, then look at what was written
 * down.
 */
class CheckoutTest extends TestCase
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

    /**
     * The simplest order the form can place: collected in person, paid in
     * cash, received by the person who ordered it.
     */
    private function checkoutPayload(array $overrides = []): array
    {
        return array_merge([
            'delivery_type_id' => DeliveryTypesDataClass::PICK_UP_DELIVERY,
            'payment_type_id' => PaymentTypesDataClass::CASH_PAYMENT,
            'recipient_type_id' => RecipientTypesDataClass::RECIPIENT_USER,
            'agreement' => 1,
            'first_name' => 'Олег',
            'last_name' => 'Петренко',
            'phone' => '+38(067)111-22-33',
            'email' => 'oleh@example.com',
        ], $overrides);
    }

    private function confirm(array $overrides = [])
    {
        return $this->keepCookies(
            $this->post(route('store.checkout.confirm'), $this->checkoutPayload($overrides))
        );
    }

    public function test_a_guest_can_place_an_order(): void
    {
        $this->seedCurrency();
        $product = $this->makeProduct(['price' => 4200]);

        $this->addToCart($product->slug, 2)->assertOk();
        $this->confirm();

        $order = Order::first();

        $this->assertNotNull($order, 'Замовлення не створилось.');
        $this->assertSame(OrderStatusesDataClass::STATUS_NEW, (int) $order->status_id);
        $this->assertCount(1, $order->products);
        $this->assertSame(2, (int) $order->products->first()->pivot->count);
        $this->assertSame(4200.0, (float) $order->products->first()->pivot->price);
    }

    public function test_an_order_by_a_guest_gets_an_account_carrying_their_details(): void
    {
        $this->seedCurrency();
        $product = $this->makeProduct();

        $this->addToCart($product->slug)->assertOk();
        $this->confirm(['email' => 'newcomer@example.com', 'first_name' => 'Іван']);

        $user = User::where('email', 'newcomer@example.com')->first();

        $this->assertNotNull($user, 'Для гостя мав створитись акаунт.');
        $this->assertSame('Іван', $user->first_name);
        $this->assertSame($user->id, (int) Order::first()->user_id);
    }

    public function test_placing_an_order_empties_the_cart(): void
    {
        $this->seedCurrency();
        $product = $this->makeProduct();

        $this->addToCart($product->slug)->assertOk();
        $this->assertSame(1, Cart::count());

        $this->confirm();

        $this->assertSame(0, Cart::count(), 'Кошик мав зникнути разом з оформленням.');
    }

    public function test_a_promo_code_is_consumed_atomically_when_the_order_is_created(): void
    {
        $this->seedCurrency();
        $product = $this->makeProduct();
        $promoCode = PromoCode::create(['code' => 'ONE-TIME', 'discount' => 15]);

        $this->addToCart($product->slug)->assertOk();
        Cart::first()->update(['promo_code_id' => $promoCode->id]);
        $this->confirm()->assertSessionHasNoErrors();

        $this->assertTrue((bool) $promoCode->fresh()->is_used);
        $this->assertSame($promoCode->id, (int) Order::first()->promo_code_id);
    }

    public function test_an_order_is_refused_without_agreement(): void
    {
        $this->seedCurrency();
        $product = $this->makeProduct();

        $this->addToCart($product->slug)->assertOk();
        $this->confirm(['agreement' => 0])->assertSessionHasErrors('agreement');

        $this->assertSame(0, Order::count());
    }

    public function test_an_order_is_refused_when_the_cart_is_empty(): void
    {
        $this->seedCurrency();

        $this->confirm()->assertSessionHasErrors('cart');

        $this->assertSame(0, Order::count());
        $this->assertSame(0, Cart::count());
    }

    public function test_a_guest_cannot_overwrite_an_existing_accounts_details_during_checkout(): void
    {
        $this->seedCurrency();
        $existingUser = User::factory()->create([
            'email' => 'oleh@example.com',
            'first_name' => 'Справжнє імʼя',
            'phone' => '+38(050)000-00-00',
        ]);
        $product = $this->makeProduct();

        $this->addToCart($product->slug)->assertOk();
        $this->confirm([
            'first_name' => 'Зловмисник',
            'phone' => '+38(067)111-22-33',
        ])->assertSessionHasErrors('email');

        $this->assertSame('Справжнє імʼя', $existingUser->fresh()->first_name);
        $this->assertSame('+38(050)000-00-00', $existingUser->fresh()->phone);
        $this->assertSame(0, Order::count());
    }

    public function test_an_order_is_refused_with_an_unusable_phone(): void
    {
        $this->seedCurrency();
        $product = $this->makeProduct();

        $this->addToCart($product->slug)->assertOk();
        $this->confirm(['phone' => '123'])->assertSessionHasErrors('phone');

        $this->assertSame(0, Order::count());
    }

    public function test_the_order_keeps_the_price_from_the_cart_not_the_product(): void
    {
        $this->seedCurrency();
        $product = $this->makeProduct(['price' => 3000]);

        $this->addToCart($product->slug)->assertOk();

        // Somebody re-prices the door between the cart and the order.
        $product->update(['price' => 8000]);

        $this->confirm();

        $this->assertSame(
            3000.0,
            (float) Order::first()->products->first()->pivot->price,
            'Замовлення має зберегти ціну, про яку домовлялись у кошику.'
        );
    }

    public function test_an_instalment_period_the_shop_does_not_offer_is_refused(): void
    {
        $this->seedCurrency();
        $product = $this->makeProduct();

        $this->addToCart($product->slug)->assertOk();

        // The form offers up to six; the bank's API would take twenty five.
        $this->confirm([
            'payment_type_id' => PaymentTypesDataClass::CARD_PAYMENT_PAYPART,
            'payment_period' => 25,
        ])->assertSessionHasErrors('payment_period');

        $this->assertSame(0, Order::count());
    }

    public function test_an_instalment_period_the_shop_offers_is_accepted(): void
    {
        $this->seedCurrency();
        $product = $this->makeProduct();

        $this->addToCart($product->slug)->assertOk();

        $this->confirm([
            'payment_type_id' => PaymentTypesDataClass::CARD_PAYMENT_PAYPART,
            'payment_period' => 3,
        ])->assertSessionHasNoErrors();
    }
}
