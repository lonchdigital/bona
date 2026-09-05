<?php

namespace Tests\Feature;

use App\DataClasses\OrderPaymentStatusesDataClass;
use App\DataClasses\OrderStatusesDataClass;
use App\DataClasses\PaymentTypesDataClass;
use App\DataClasses\RecipientTypesDataClass;
use App\Models\Order;
use App\Services\Order\OrderAccessUrlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class OrderAccessSecurityTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    private function order(): Order
    {
        $this->seedCurrency();
        $this->productType();

        return Order::create([
            'status_id' => OrderStatusesDataClass::STATUS_NEW,
            'user_id' => $this->author()->id,
            'recipient_type_id' => RecipientTypesDataClass::RECIPIENT_USER,
            'payment_status_id' => OrderPaymentStatusesDataClass::STATUS_PAID_AS_RECEIVED,
        ]);
    }

    public function test_an_unsigned_guest_cannot_view_an_order_confirmation(): void
    {
        $order = $this->order();

        $this->get(route('store.checkout.thank-you', ['order' => $order]))
            ->assertForbidden();
    }

    public function test_a_guest_can_view_an_order_confirmation_with_its_temporary_signature(): void
    {
        $order = $this->order();

        $this->get(app(OrderAccessUrlService::class)->thankYou($order))
            ->assertOk()
            ->assertSee($order->user->phone);
    }

    public function test_an_unpaid_invoice_still_gets_the_successful_order_page(): void
    {
        $order = $this->order();
        $order->update([
            'payment_type_id' => PaymentTypesDataClass::INVOICE_PAYMENT,
            'payment_status_id' => OrderPaymentStatusesDataClass::STATUS_UNPAID,
        ]);

        $this->get(app(OrderAccessUrlService::class)->thankYou($order))
            ->assertOk()
            ->assertSee(trans('base.checkout_success_title'))
            ->assertSee('#BD-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT));
    }

    public function test_an_unpaid_online_card_order_still_gets_the_payment_failure_page(): void
    {
        $order = $this->order();
        $order->update([
            'payment_type_id' => PaymentTypesDataClass::CARD_PAYMENT,
            'payment_status_id' => OrderPaymentStatusesDataClass::STATUS_UNPAID,
        ]);

        $this->get(app(OrderAccessUrlService::class)->thankYou($order))
            ->assertOk()
            ->assertViewIs('pages.store.payment-failure');
    }
}
