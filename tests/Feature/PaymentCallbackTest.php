<?php

namespace Tests\Feature;

use App\DataClasses\OrderPaymentStatusesDataClass;
use App\DataClasses\OrderStatusesDataClass;
use App\DataClasses\RecipientTypesDataClass;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesShopData;
use Tests\TestCase;

/**
 * What the banks tell us after the customer has decided.
 *
 * These endpoints are open to the internet and they move an order to paid, so
 * the question is not only "does the right message work" but "does the wrong
 * one get turned away".
 */
class PaymentCallbackTest extends TestCase
{
    use RefreshDatabase;
    use MakesShopData;

    private function makeOrder(array $attributes = []): Order
    {
        $product = $this->makeProduct();

        $order = Order::create(array_merge([
            'status_id' => OrderStatusesDataClass::STATUS_NEW,
            'user_id' => $this->author()->id,
            'recipient_type_id' => RecipientTypesDataClass::RECIPIENT_USER,
            'payment_status_id' => OrderPaymentStatusesDataClass::STATUS_UNPAID,
        ], $attributes));

        $order->products()->attach($product->id, [
            'count' => 1,
            'price' => $product->price,
        ]);

        return $order;
    }

    public function test_a_forged_monobank_callback_cannot_mark_an_order_paid(): void
    {
        $order = $this->makeOrder(['mono_order_id' => 'mono-12345']);

        // No signature, no shared secret — just the order id, which anyone
        // who has seen one can guess the shape of.
        $this->postJson(route('store.checkout.partial.mono.bank.payment'), [
            'order_id' => 'mono-12345',
            'state' => 'ACTIVE',
            'order_sub_state' => 'WAITING_FOR_STORE_CONFIRM',
        ]);

        $this->assertSame(
            OrderPaymentStatusesDataClass::STATUS_UNPAID,
            (int) $order->fresh()->payment_status_id,
            'Непідписаний запит не має позначати замовлення оплаченим.'
        );
    }

    public function test_a_monobank_callback_for_an_unknown_order_is_survived(): void
    {
        $response = $this->postJson(route('store.checkout.partial.mono.bank.payment'), [
            'order_id' => 'no-such-order',
            'state' => 'ACTIVE',
            'order_sub_state' => 'WAITING_FOR_STORE_CONFIRM',
        ]);

        $this->assertLessThan(
            500,
            $response->getStatusCode(),
            'Невідоме замовлення не має валити застосунок.'
        );
    }

    public function test_a_privatbank_callback_without_a_valid_signature_is_refused(): void
    {
        $order = $this->makeOrder();

        $this->postJson(route('store.checkout.partial.payment'), [
            'storeId' => 'store',
            'orderId' => (string) $order->id,
            'paymentState' => 'SUCCESS',
            'signature' => 'obviously-not-a-real-signature',
            'message' => 'ok',
        ])->assertStatus(422);

        $this->assertSame(
            OrderPaymentStatusesDataClass::STATUS_UNPAID,
            (int) $order->fresh()->payment_status_id
        );
    }

    public function test_a_privatbank_callback_with_an_unexpected_state_is_survived(): void
    {
        $order = $this->makeOrder();

        $response = $this->postJson(route('store.checkout.partial.payment'), [
            'storeId' => 'store',
            'orderId' => (string) $order->id,
            'paymentState' => 'SOMETHING_ELSE',
            'signature' => 'x',
            'message' => 'ok',
        ]);

        $this->assertLessThan(500, $response->getStatusCode());
    }
}
