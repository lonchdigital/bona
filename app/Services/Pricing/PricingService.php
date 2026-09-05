<?php

namespace App\Services\Pricing;

use App\DataClasses\DeliveryTypesDataClass;
use App\Models\Cart;
use App\Models\Order;
use App\Models\PromoCode;
use App\Services\PromoCode\PromoCodeService;
use App\Support\Payment\InstallmentPricing;
use Illuminate\Support\Enumerable;

class PricingService
{
    public function __construct(
        private readonly PromoCodeService $promoCodeService,
    ) {}

    public function forCart(
        Cart $cart,
        ?int $deliveryTypeId = null,
        ?int $paymentTypeId = null,
        ?int $installmentPeriod = null,
    ): array {
        $summary = $this->calculate(
            $cart->products,
            $cart->promoCode,
            $deliveryTypeId,
        );

        $provider = InstallmentPricing::providerForPaymentType($paymentTypeId);

        return $provider && $installmentPeriod
            ? $this->withCalculatedInstallment($summary, $provider, $installmentPeriod)
            : $this->withoutInstallment($summary);
    }

    public function forOrder(Order $order): array
    {
        $summary = $this->calculate(
            $order->products,
            $order->promoCode,
            $order->delivery_type_id,
        );

        if (! $order->installment_provider || ! $order->installment_period) {
            return $this->withoutInstallment($summary);
        }

        return $this->withStoredInstallment(
            $summary,
            (string) $order->installment_provider,
            (int) $order->installment_period,
            (float) $order->installment_surcharge_percent,
            $this->toCents((float) $order->installment_surcharge_amount),
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

    private function withoutInstallment(array $summary): array
    {
        return array_merge($summary, [
            'base_total' => $summary['total'],
            'base_total_in_cents' => $summary['total_in_cents'],
            'installment_provider' => null,
            'installment_period' => null,
            'installment_rate' => 0.0,
            'installment_fee' => 0.0,
            'installment_fee_in_cents' => 0,
        ]);
    }

    private function withCalculatedInstallment(array $summary, string $provider, int $period): array
    {
        $quote = InstallmentPricing::quoteInCents($summary['total_in_cents'], $provider, $period);

        return $this->withStoredInstallment(
            $summary,
            $provider,
            $period,
            $quote['rate'],
            $quote['fee_in_cents'],
        );
    }

    private function withStoredInstallment(
        array $summary,
        string $provider,
        int $period,
        float $rate,
        int $feeInCents,
    ): array {
        $baseTotalInCents = $summary['total_in_cents'];
        $totalInCents = $baseTotalInCents + max(0, $feeInCents);

        return array_merge($summary, [
            'base_total' => $this->fromCents($baseTotalInCents),
            'base_total_in_cents' => $baseTotalInCents,
            'installment_provider' => $provider,
            'installment_period' => $period,
            'installment_rate' => $rate,
            'installment_fee' => $this->fromCents(max(0, $feeInCents)),
            'installment_fee_in_cents' => max(0, $feeInCents),
            'total' => $this->fromCents($totalInCents),
            'total_in_cents' => $totalInCents,
        ]);
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
