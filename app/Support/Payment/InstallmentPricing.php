<?php

namespace App\Support\Payment;

use App\DataClasses\PaymentTypesDataClass;
use InvalidArgumentException;

/**
 * One authoritative installment calculation for storefront and payments.
 *
 * Rates are converted to basis points before any money is multiplied. That
 * keeps 10,000.00 + 2.9% at exactly 10,290.00 rather than letting a binary
 * floating-point approximation leak into an order or bank request.
 */
final class InstallmentPricing
{
    public const MONOBANK = 'monobank';

    public const PRIVATBANK = 'privatbank';

    public static function providerForPaymentType(?int $paymentTypeId): ?string
    {
        return match ($paymentTypeId) {
            PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK => self::MONOBANK,
            PaymentTypesDataClass::CARD_PAYMENT_PAYPART => self::PRIVATBANK,
            default => null,
        };
    }

    /** @return array<int, float> */
    public static function ratesFor(string $provider): array
    {
        return collect(config("payment.{$provider}.installment_surcharges", []))
            ->mapWithKeys(fn ($rate, $period) => [(int) $period => (float) $rate])
            ->filter(fn ($rate, $period) => $period > 0 && $rate >= 0)
            ->sortKeys()
            ->all();
    }

    public static function rateFor(string $provider, int $period): float
    {
        $rates = self::ratesFor($provider);

        if (! array_key_exists($period, $rates)) {
            throw new InvalidArgumentException("No installment surcharge for {$provider} and {$period} payments.");
        }

        return $rates[$period];
    }

    /**
     * @return array{
     *     provider: string,
     *     period: int,
     *     rate: float,
     *     rate_basis_points: int,
     *     base_in_cents: int,
     *     fee_in_cents: int,
     *     total_in_cents: int,
     *     monthly_in_cents: int
     * }
     */
    public static function quoteInCents(int $baseInCents, string $provider, int $period): array
    {
        $baseInCents = max(0, $baseInCents);
        $rate = self::rateFor($provider, $period);
        $rateBasisPoints = (int) round($rate * 100, 0, PHP_ROUND_HALF_UP);
        $feeInCents = (int) round($baseInCents * $rateBasisPoints / 10_000, 0, PHP_ROUND_HALF_UP);
        $totalInCents = $baseInCents + $feeInCents;

        return [
            'provider' => $provider,
            'period' => $period,
            'rate' => $rate,
            'rate_basis_points' => $rateBasisPoints,
            'base_in_cents' => $baseInCents,
            'fee_in_cents' => $feeInCents,
            'total_in_cents' => $totalInCents,
            'monthly_in_cents' => intdiv($totalInCents + $period - 1, $period),
        ];
    }
}
