<?php

namespace Tests\Feature;

use App\DataClasses\OrderPaymentStatusesDataClass;
use App\DataClasses\OrderStatusesDataClass;
use App\DataClasses\PaymentTypesDataClass;
use App\DataClasses\RecipientTypesDataClass;
use App\Mail\OrderStatusEmail;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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
    use MakesShopData;
    use RefreshDatabase;

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

    private function liqPayCallbackPayload(Order $order, array $overrides = []): array
    {
        config()->set('liqpay.public_key', 'test-public-key');
        config()->set('liqpay.private_key', 'test-private-key');

        $payload = array_merge([
            'public_key' => 'test-public-key',
            'order_id' => (string) $order->id,
            'status' => 'success',
            'amount' => (string) $order->products->sum(
                fn ($product) => $product->pivot->price * $product->pivot->count
            ),
            'currency' => 'UAH',
        ], $overrides);

        $data = base64_encode(json_encode($payload));

        return [
            'data' => $data,
            'signature' => base64_encode(sha1('test-private-key'.$data.'test-private-key', true)),
        ];
    }

    public function test_a_forged_liqpay_callback_cannot_mark_an_order_paid(): void
    {
        config()->set('liqpay.private_key', 'test-private-key');
        $order = $this->makeOrder(['payment_type_id' => PaymentTypesDataClass::CARD_PAYMENT]);

        $this->post(route('payment.liqpay.callback'), [
            'data' => base64_encode(json_encode([
                'order_id' => $order->id,
                'status' => 'success',
                'amount' => $order->products->first()->pivot->price,
                'currency' => 'UAH',
            ])),
            'signature' => 'forged-signature',
        ])->assertForbidden();

        $this->assertSame(
            OrderPaymentStatusesDataClass::STATUS_UNPAID,
            (int) $order->fresh()->payment_status_id,
        );
    }

    public function test_a_valid_liqpay_callback_marks_an_order_paid(): void
    {
        $order = $this->makeOrder(['payment_type_id' => PaymentTypesDataClass::CARD_PAYMENT]);

        $this->post(route('payment.liqpay.callback'), $this->liqPayCallbackPayload($order))
            ->assertOk()
            ->assertSeeText('ok');

        $this->assertSame(
            OrderPaymentStatusesDataClass::STATUS_PAID,
            (int) $order->fresh()->payment_status_id,
        );
    }

    public function test_liqpay_callback_with_a_wrong_amount_is_refused(): void
    {
        $order = $this->makeOrder(['payment_type_id' => PaymentTypesDataClass::CARD_PAYMENT]);

        $this->post(route('payment.liqpay.callback'), $this->liqPayCallbackPayload($order, [
            'amount' => '0.01',
        ]))->assertUnprocessable();

        $this->assertSame(
            OrderPaymentStatusesDataClass::STATUS_UNPAID,
            (int) $order->fresh()->payment_status_id,
        );
    }

    public function test_a_repeated_liqpay_callback_is_idempotent(): void
    {
        Mail::fake();
        config()->set('domain.admin_notification_emails', 'admin@example.com');
        $order = $this->makeOrder(['payment_type_id' => PaymentTypesDataClass::CARD_PAYMENT]);
        $callback = $this->liqPayCallbackPayload($order);

        $this->post(route('payment.liqpay.callback'), $callback)->assertOk();
        $this->post(route('payment.liqpay.callback'), $callback)->assertOk();

        Mail::assertQueued(OrderStatusEmail::class, 1);
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

    public function test_a_signed_monobank_callback_is_accepted_and_cannot_be_downgraded(): void
    {
        Mail::fake();
        config()->set('payment.monobank', [
            'api_url' => 'https://u2.monobank.com.ua',
            'client_secret' => 'mono-secret',
            'store_id' => 'mono-store',
            'point_id' => 'point-1',
            'periods' => [3],
        ]);
        $order = $this->makeOrder([
            'payment_type_id' => PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK,
            'mono_order_id' => 'mono-signed',
        ]);

        $approved = [
            'order_id' => 'mono-signed',
            'state' => 'IN_PROCESS',
            'order_sub_state' => 'WAITING_FOR_STORE_CONFIRM',
        ];
        $approvedBody = json_encode($approved);
        $approvedSignature = base64_encode(hash_hmac('sha256', $approvedBody, 'mono-secret', true));

        $this->withHeader('signature', $approvedSignature)
            ->postJson(route('store.checkout.partial.mono.bank.payment'), $approved)
            ->assertOk();

        $this->assertSame(OrderPaymentStatusesDataClass::STATUS_PAID, (int) $order->fresh()->payment_status_id);

        $failed = [
            'order_id' => 'mono-signed',
            'state' => 'FAIL',
            'order_sub_state' => 'REJECTED_BY_CLIENT',
        ];
        $failedBody = json_encode($failed);
        $failedSignature = base64_encode(hash_hmac('sha256', $failedBody, 'mono-secret', true));

        $this->withHeader('signature', $failedSignature)
            ->postJson(route('store.checkout.partial.mono.bank.payment'), $failed)
            ->assertOk();

        $this->assertSame(OrderPaymentStatusesDataClass::STATUS_PAID, (int) $order->fresh()->payment_status_id);
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

    public function test_a_signed_privatbank_callback_is_accepted_and_cannot_be_downgraded(): void
    {
        Mail::fake();
        config()->set('payment.privatbank.store_id', 'privat-store');
        config()->set('payment.privatbank.password', 'privat-secret');
        $order = $this->makeOrder([
            'payment_type_id' => PaymentTypesDataClass::CARD_PAYMENT_PAYPART,
        ]);

        $callback = function (string $state) use ($order): array {
            $message = 'ok';
            $signature = base64_encode(sha1(
                'privat-secret'.'privat-store'.$order->id.$state.$message.'privat-secret',
                true,
            ));

            return [
                'storeId' => 'privat-store',
                'orderId' => (string) $order->id,
                'paymentState' => $state,
                'signature' => $signature,
                'message' => $message,
            ];
        };

        $this->postJson(route('store.checkout.partial.payment'), $callback('SUCCESS'))->assertOk();
        $this->assertSame(OrderPaymentStatusesDataClass::STATUS_PAID, (int) $order->fresh()->payment_status_id);

        $this->postJson(route('store.checkout.partial.payment'), $callback('FAIL'))->assertNoContent();
        $this->assertSame(OrderPaymentStatusesDataClass::STATUS_PAID, (int) $order->fresh()->payment_status_id);
    }
}
