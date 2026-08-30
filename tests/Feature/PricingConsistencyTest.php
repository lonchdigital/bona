<?php

namespace Tests\Feature;

use App\DataClasses\DeliveryTypesDataClass;
use App\DataClasses\OrderPaymentStatusesDataClass;
use App\DataClasses\OrderStatusesDataClass;
use App\DataClasses\RecipientTypesDataClass;
use App\Models\Cart;
use App\Models\Order;
use App\Models\PromoCode;
use App\Services\Payment\PaymentMonoBankService;
use App\Services\Pricing\PricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class PricingConsistencyTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_cart_order_and_payment_total_share_one_cent_accurate_calculation(): void
    {
        config()->set('domain.delivery_price', 50);
        config()->set('domain.free_delivery_from_price', 10_000);

        $product = $this->makeProduct(['price' => 99.99]);
        $promoCode = PromoCode::create(['code' => 'TEN', 'discount' => 10]);
        $cart = Cart::create(['promo_code_id' => $promoCode->id, 'token' => 'pricing-test']);
        $cart->products()->attach($product->id, [
            'count' => 3,
            'price' => 99.99,
            'attributes_price' => 0.01,
        ]);

        $order = Order::create([
            'status_id' => OrderStatusesDataClass::STATUS_NEW,
            'user_id' => $this->author()->id,
            'promo_code_id' => $promoCode->id,
            'delivery_type_id' => DeliveryTypesDataClass::ADDRESS_DELIVERY,
            'recipient_type_id' => RecipientTypesDataClass::RECIPIENT_USER,
            'payment_status_id' => OrderPaymentStatusesDataClass::STATUS_UNPAID,
        ]);
        $order->products()->attach($product->id, [
            'count' => 3,
            'price' => 99.99,
            'attributes_price' => 0.01,
        ]);

        $pricing = app(PricingService::class);
        $cartTotals = $pricing->forCart($cart, DeliveryTypesDataClass::ADDRESS_DELIVERY);
        $orderTotals = $pricing->forOrder($order);

        $this->assertSame(300.0, $cartTotals['products']);
        $this->assertSame(30.0, $cartTotals['discount']);
        $this->assertSame(50.0, $cartTotals['delivery']);
        $this->assertSame(320.0, $cartTotals['total']);
        $this->assertSame($cartTotals['total_in_cents'], $orderTotals['total_in_cents']);
        $this->assertSame(32_000, $orderTotals['total_in_cents']);
    }

    public function test_free_delivery_threshold_uses_the_pre_discount_product_total(): void
    {
        config()->set('domain.delivery_price', 250);
        config()->set('domain.free_delivery_from_price', 1_000);

        $product = $this->makeProduct(['price' => 1_000]);
        $promoCode = PromoCode::create(['code' => 'HALF', 'discount' => 50]);
        $cart = Cart::create(['promo_code_id' => $promoCode->id, 'token' => 'free-delivery-test']);
        $cart->products()->attach($product->id, ['count' => 1, 'price' => 1_000]);

        $totals = app(PricingService::class)->forCart($cart, DeliveryTypesDataClass::ADDRESS_DELIVERY);

        $this->assertTrue($totals['has_free_delivery']);
        $this->assertSame(0.0, $totals['delivery']);
        $this->assertSame(500.0, $totals['total']);
    }

    public function test_monobank_payload_uses_the_same_decimal_total_as_the_order(): void
    {
        config()->set('domain.delivery_price', 50);
        config()->set('domain.free_delivery_from_price', 10_000);
        config()->set('payment.monobank', [
            'api_url' => 'https://u2.monobank.com.ua',
            'client_secret' => 'secret',
            'store_id' => 'store',
            'point_id' => 'point-1',
            'periods' => [3],
        ]);

        $product = $this->makeProduct(['price' => 99.99]);
        $promoCode = PromoCode::create(['code' => 'TEN-MONO', 'discount' => 10]);
        $order = Order::create([
            'status_id' => OrderStatusesDataClass::STATUS_NEW,
            'user_id' => $this->author()->id,
            'promo_code_id' => $promoCode->id,
            'delivery_type_id' => DeliveryTypesDataClass::ADDRESS_DELIVERY,
            'recipient_type_id' => RecipientTypesDataClass::RECIPIENT_USER,
            'payment_status_id' => OrderPaymentStatusesDataClass::STATUS_UNPAID,
        ]);
        $order->products()->attach($product->id, [
            'count' => 3,
            'price' => 99.99,
            'attributes_price' => 0.01,
        ]);

        $payload = app(PaymentMonoBankService::class)
            ->createOrderPayload($order, '+380501234567', 3);

        $this->assertSame(320.0, $payload['total_sum']);
        $this->assertSame(100.0, $payload['products'][0]['sum']);
        $this->assertSame([3], $payload['available_programs'][0]['available_parts_count']);
        $this->assertSame('point-1', $payload['invoice']['point_id']);
    }
}
