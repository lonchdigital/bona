<?php

namespace Tests\Feature;

use App\DataClasses\DeliveryTypesDataClass;
use App\DataClasses\OrderPaymentStatusesDataClass;
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

    public function test_the_reference_full_name_field_is_split_for_a_guest_order(): void
    {
        $this->seedCurrency();
        $product = $this->makeProduct();

        $this->addToCart($product->slug)->assertOk();
        $this->confirm([
            'full_name' => 'Олена Коваль',
            'first_name' => null,
            'last_name' => null,
            'email' => 'olena@example.com',
        ])->assertSessionHasNoErrors();

        $user = User::where('email', 'olena@example.com')->firstOrFail();

        $this->assertSame('Олена', $user->first_name);
        $this->assertSame('Коваль', $user->last_name);
    }

    public function test_a_signed_in_customer_sees_their_saved_details_and_can_order_without_retyping_them(): void
    {
        $this->seedCurrency();
        $user = User::factory()->create([
            'first_name' => 'Оксана',
            'last_name' => 'Гончар',
            'phone' => '+38(067)555-44-33',
            'email' => 'oksana@example.com',
        ]);
        $product = $this->makeProduct();

        $this->actingAs($user);
        $this->addToCart($product->slug)->assertOk();

        $this->get(route('store.checkout.page'))
            ->assertOk()
            ->assertSee('Оксана Гончар')
            ->assertSee('+38(067)555-44-33')
            ->assertDontSee('name="full_name"', false);

        $this->post(route('store.checkout.confirm'), [
            'delivery_type_id' => DeliveryTypesDataClass::PICK_UP_DELIVERY,
            'payment_type_id' => PaymentTypesDataClass::MANAGER_CONFIRMATION_PAYMENT,
            'recipient_type_id' => RecipientTypesDataClass::RECIPIENT_USER,
            'agreement' => 1,
        ])->assertRedirect();

        $this->assertSame($user->id, (int) Order::firstOrFail()->user_id);
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

    public function test_an_existing_email_gets_a_checkout_specific_sign_in_recovery(): void
    {
        $this->seedCurrency();
        $existingUser = User::factory()->create(['email' => 'registered@example.com']);
        $product = $this->makeProduct();

        $this->addToCart($product->slug)->assertOk();
        $response = $this->confirm(['email' => $existingUser->email]);

        $response->assertSessionHasErrors([
            'email' => trans('base.checkout_email_registered_error', [
                'email' => $existingUser->email,
            ]),
        ]);

        $this->get(route('store.checkout.page'))
            ->assertOk()
            ->assertSee(trans('base.checkout_registered_account_title'))
            ->assertSee(trans('base.checkout_registered_account_action'))
            ->assertSee('data-checkout-account-error', false)
            ->assertSee('data-auth-email="'.$existingUser->email.'"', false);
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

    public function test_an_invoice_order_is_recorded_as_unpaid_and_sent_to_the_confirmation_page(): void
    {
        $this->seedCurrency();
        $product = $this->makeProduct(['price' => 7600]);

        $this->addToCart($product->slug)->assertOk();
        $response = $this->confirm([
            'payment_type_id' => PaymentTypesDataClass::INVOICE_PAYMENT,
        ]);

        $order = Order::firstOrFail();

        $response->assertRedirect();
        $this->assertStringContainsString('/checkout/'.$order->id.'/thank', $response->headers->get('Location'));
        $this->assertSame(PaymentTypesDataClass::INVOICE_PAYMENT, (int) $order->payment_type_id);
        $this->assertSame(OrderPaymentStatusesDataClass::STATUS_UNPAID, (int) $order->payment_status_id);
    }

    public function test_manager_confirmation_is_the_checkout_default_and_stays_unpaid(): void
    {
        $this->seedCurrency();
        $product = $this->makeProduct();

        $this->addToCart($product->slug)->assertOk();

        $page = $this->get(route('store.checkout.page'));
        $page->assertOk();
        $this->assertSame(
            PaymentTypesDataClass::MANAGER_CONFIRMATION_PAYMENT,
            $page->viewData('checkoutPaymentType')
        );
        $page->assertSee('name="full_name"', false);
        $page->assertSee(trans('base.checkout_payment_manager_confirmation'));
        $page->assertSee(trans('base.checkout_payment_cash'));

        $this->confirm([
            'payment_type_id' => PaymentTypesDataClass::MANAGER_CONFIRMATION_PAYMENT,
        ])->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame(PaymentTypesDataClass::MANAGER_CONFIRMATION_PAYMENT, (int) $order->payment_type_id);
        $this->assertSame(OrderPaymentStatusesDataClass::STATUS_UNPAID, (int) $order->payment_status_id);
    }

    public function test_checkout_waits_for_the_customer_to_choose_delivery(): void
    {
        $this->seedCurrency();
        $product = $this->makeProduct(['price' => 5400]);

        $this->addToCart($product->slug)->assertOk();

        $page = $this->get(route('store.checkout.page'));

        $page->assertOk();
        $this->assertNull($page->viewData('checkoutDeliveryType'));
        $this->assertSame(0.0, (float) $page->viewData('initialSummary')['delivery']);
        $page->assertSee(trans('base.checkout_delivery_not_selected'));
        $page->assertSee('data-delivery-empty-label="'.trans('base.checkout_delivery_not_selected').'"', false);
        $page->assertSee('aria-controls="delivery-1"', false);
        $page->assertSee('aria-expanded="false"', false);
        $page->assertSee('class="bona-consent__box"', false);
        $this->assertMatchesRegularExpression('/id="delivery-1"[^>]*hidden/', $page->getContent());
        $this->assertMatchesRegularExpression('/id="delivery-2"[^>]*hidden/', $page->getContent());

        $this->getJson(route('store.cart.summary-with-delivery'))
            ->assertOk()
            ->assertJsonPath('data.delivery', 0)
            ->assertJsonPath('data.total', 5400);

        $script = file_get_contents(resource_path('js/store/pages/store.checkout.page.js'));
        $this->assertStringContainsString('input.checked = false;', $script);
        $this->assertStringContainsString('getSummaryByDeliveryTypeId(null', $script);
    }

    public function test_checkout_renders_interactive_installment_controls_for_both_banks(): void
    {
        $this->seedCurrency();
        $product = $this->makeProduct(['price' => 12000]);

        $this->addToCart($product->slug)->assertOk();

        $page = $this->get(route('store.checkout.page'));

        $page->assertOk();
        $page->assertSee('data-provider="mono"', false);
        $page->assertSee('data-provider="privat"', false);
        $page->assertSee('data-installment-decrease', false);
        $page->assertSee('data-installment-increase', false);
        $page->assertSee('data-installment-terms="mono"', false);
        $page->assertSee('data-installment-terms="privat"', false);
    }

    public function test_cash_on_receipt_remains_a_separate_payment_method(): void
    {
        $this->seedCurrency();
        $product = $this->makeProduct();

        $this->addToCart($product->slug)->assertOk();
        $this->confirm([
            'payment_type_id' => PaymentTypesDataClass::CASH_PAYMENT,
        ])->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame(PaymentTypesDataClass::CASH_PAYMENT, (int) $order->payment_type_id);
        $this->assertSame(OrderPaymentStatusesDataClass::STATUS_PAID_AS_RECEIVED, (int) $order->payment_status_id);
    }

    public function test_product_page_can_preselect_monobank_and_its_period_in_checkout(): void
    {
        $this->seedCurrency();
        config()->set('payment.monobank.periods', [3, 4, 5]);
        $product = $this->makeProduct();

        $this->addToCart($product->slug)->assertOk();

        $response = $this->get(route('store.checkout.page', [
            'payment_type_id' => PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK,
            'mono_payment_period' => 5,
        ]));

        $response->assertOk();
        $this->assertSame(PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK, $response->viewData('checkoutPaymentType'));
        $this->assertSame(5, $response->viewData('checkoutMonoPeriod'));
        $response->assertSee('class="selected-payment-type">'.trans('base.checkout_payment_paypart_mono_bank').'</span>', false);
    }

    public function test_product_page_can_preselect_privatbank_and_rejects_unoffered_periods(): void
    {
        $this->seedCurrency();
        config()->set('payment.privatbank.periods', [2, 3, 4, 5, 6]);
        $product = $this->makeProduct();

        $this->addToCart($product->slug)->assertOk();

        $response = $this->get(route('store.checkout.page', [
            'payment_type_id' => PaymentTypesDataClass::CARD_PAYMENT_PAYPART,
            'payment_period' => 25,
        ]));

        $response->assertOk();
        $this->assertSame(PaymentTypesDataClass::CARD_PAYMENT_PAYPART, $response->viewData('checkoutPaymentType'));
        $this->assertSame(3, $response->viewData('checkoutPrivatPeriod'));
        $response->assertSee('class="selected-payment-type">'.trans('base.checkout_payment_paypart').'</span>', false);
        $response->assertDontSee('<option value="2"', false);
    }
}
