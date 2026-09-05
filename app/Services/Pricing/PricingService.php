<?php

namespace App\Services\Pricing;

use App\DataClasses\DeliveryTypesDataClass;
use App\Models\Cart;
use App\Models\Order;
use App\Models\PromoCode;
use App\Services\PromoCode\PromoCodeService;
use Illuminate\Support\Enumerable;

class PricingService
{
    public function __construct(
        private readonly PromoCodeService $promoCodeService,
    ) {}

    public function forCart(Cart $cart, ?int $deliveryTypeId = null): array
    {
        return $this->calculate(
            $cart->products,
            $cart->promoCode,
            $deliveryTypeId,
        );
    }

    public function forOrder(Order $order): array
    {
        return $this->calculate(
            $order->products,
            $order->promoCode,
            $order->delivery_type_id,
        );
    }

    private function calculate(Enumerable $products, ?PromoCode $promoCode, ?int $deliveryTypeId): array
    {
        $productsInCents = 0;

        foreach ($products as $product) {
            $unitPriceInCents = $this->toCents((float) $product->pivot->price)
                + $this->toCents((float) ($product->pivot->attributes_price ?? 0));
            $productsInCents += $unitPriceInCents * max(0, (int) $product->pivot->count);
        }

        $discountInCents = $promoCode
            ? $this->toCents($this->promoCodeService->discount($promoCode, $products))
            : 0;

        $freeDeliveryThresholdInCents = $this->toCents((float) config('domain.free_delivery_from_price', 0));
        $hasFreeDelivery = $freeDeliveryThresholdInCents > 0
            && $productsInCents >= $freeDeliveryThresholdInCents;

        $standardDeliveryInCents = $this->toCents((float) config('domain.delivery_price', 0));
        $isAddressDelivery = $deliveryTypeId === DeliveryTypesDataClass::ADDRESS_DELIVERY;
        $deliveryInCents = $isAddressDelivery && ! $hasFreeDelivery ? $standardDeliveryInCents : 0;
        $deliveryOldInCents = $isAddressDelivery && $hasFreeDelivery ? $standardDeliveryInCents : 0;

        $isCarrier = in_array($deliveryTypeId, [
            DeliveryTypesDataClass::NP_DELIVERY,
            DeliveryTypesDataClass::MIST_EXPRESS_DELIVERY,
            DeliveryTypesDataClass::SAT_DELIVERY,
        ], true);

        $totalInCents = $productsInCents - $discountInCents + $deliveryInCents;

        return [
            'products' => $this->fromCents($productsInCents),
            'discount' => $this->fromCents($discountInCents),
            'delivery' => $this->fromCents($deliveryInCents),
            'delivery_old' => $this->fromCents($deliveryOldInCents),
            'total' => $this->fromCents($totalInCents),
            'has_free_delivery' => $hasFreeDelivery,
            'is_carrier' => $isCarrier,
            'total_in_cents' => $totalInCents,
        ];
    }

    private function toCents(float $amount): int
    {
        return (int) round($amount * 100);
    }

    private function fromCents(int $amount): float
    {
        return $amount / 100;
    }
}
