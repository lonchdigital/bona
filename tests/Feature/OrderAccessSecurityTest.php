<?php

namespace Tests\Feature;

use App\DataClasses\OrderPaymentStatusesDataClass;
use App\DataClasses\OrderStatusesDataClass;
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
}
