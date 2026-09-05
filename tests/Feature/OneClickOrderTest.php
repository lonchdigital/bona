<?php

namespace Tests\Feature;

use App\DataClasses\OrderPaymentStatusesDataClass;
use App\DataClasses\OrderStatusesDataClass;
use App\Mail\AdminNotificationEmail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Support\MakesShopData;
use Tests\TestCase;

/**
 * Buying in one click: a name, a number, and the product the page is about.
 */
class OneClickOrderTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    private function buy(string $slug, array $overrides = [])
    {
        return $this->postJson(
            route('store.product.one-click-order', ['productSlug' => $slug]),
            array_merge([
                'name' => 'Олег',
                'phone' => '+38(067)111-22-33',
                'agree' => 1,
            ], $overrides)
        );
    }

    public function test_an_order_is_booked_for_the_product_on_the_page(): void
    {
        $product = $this->makeProduct(['price' => 7300]);

        $this->buy($product->slug)->assertOk();

        $order = Order::first();

        $this->assertNotNull($order, 'Замовлення не створилось.');
        $this->assertSame(OrderStatusesDataClass::STATUS_ONE_CLICK, (int) $order->status_id);
        $this->assertSame(OrderPaymentStatusesDataClass::STATUS_UNPAID, (int) $order->payment_status_id);
        $this->assertCount(1, $order->products);
        $this->assertSame($product->id, $order->products->first()->id);
        $this->assertSame(7300.0, (float) $order->products->first()->pivot->price);
    }

    public function test_the_customer_is_recorded_with_their_name_and_number(): void
    {
        $product = $this->makeProduct();

        $this->buy($product->slug, ['name' => 'Марія', 'phone' => '+38(050)999-88-77'])->assertOk();

        $customer = Order::first()->user;

        $this->assertSame('Марія', $customer->first_name);
        $this->assertSame('+38(050)999-88-77', $customer->phone);
    }

    public function test_the_shop_is_emailed_after_a_one_click_order_is_recorded(): void
    {
        Mail::fake();
        config()->set('domain.admin_notification_emails', 'orders@example.com,owner@example.com');
        $product = $this->makeProduct();

        $this->buy($product->slug)->assertOk();

        Mail::assertQueued(AdminNotificationEmail::class, 2);
        Mail::assertQueued(AdminNotificationEmail::class, fn (AdminNotificationEmail $mail) => $mail->hasTo('orders@example.com'));
        Mail::assertQueued(AdminNotificationEmail::class, fn (AdminNotificationEmail $mail) => $mail->hasTo('owner@example.com'));
    }

    public function test_a_second_order_from_the_same_number_reuses_the_customer(): void
    {
        $first = $this->makeProduct();
        $second = $this->makeProduct();

        $this->buy($first->slug, ['phone' => '+38(050)111-22-33'])->assertOk();
        $this->buy($second->slug, ['phone' => '+38(050)111-22-33'])->assertOk();

        $this->assertSame(2, Order::count());
        $this->assertSame(
            1,
            User::where('email', 'like', 'one-click-%')->count(),
            'Той самий номер не має плодити нових клієнтів.'
        );
    }

    public function test_an_anonymous_one_click_order_cannot_be_attached_to_a_real_account_by_phone(): void
    {
        $realUser = User::factory()->create(['phone' => '+38(050)111-22-33']);
        $product = $this->makeProduct();

        $this->buy($product->slug, ['phone' => '+38(050)111-22-33'])->assertOk();

        $this->assertNotSame($realUser->id, Order::first()->user_id);
        $this->assertSame(1, User::where('email', 'one-click-380501112233@bona-doors.com.ua')->count());
    }

    public function test_a_half_typed_number_is_refused(): void
    {
        $product = $this->makeProduct();

        // What the input mask leaves behind when the number is unfinished.
        $this->buy($product->slug, ['phone' => '+38(067)953-4_-__'])->assertStatus(422);

        $this->assertSame(0, Order::count());
    }

    public function test_an_order_without_a_name_is_refused(): void
    {
        $product = $this->makeProduct();

        $this->buy($product->slug, ['name' => ''])->assertStatus(422);

        $this->assertSame(0, Order::count());
    }

    public function test_an_order_without_consent_is_refused(): void
    {
        $product = $this->makeProduct();

        $this->buy($product->slug, ['agree' => 0])->assertStatus(422);

        $this->assertSame(0, Order::count());
    }

    public function test_an_unknown_product_cannot_be_bought(): void
    {
        $this->buy('there-is-no-such-door')->assertNotFound();

        $this->assertSame(0, Order::count());
    }
}
