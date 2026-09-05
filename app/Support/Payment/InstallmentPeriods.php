<?php

namespace App\Support\Payment;

final class InstallmentPeriods
{
    public static function for(string $provider): array
    {
        $minimum = max(2, (int) config("payment.{$provider}.minimum_period", 3));
        $rates = InstallmentPricing::ratesFor($provider);

        $periods = collect(config("payment.{$provider}.periods", []))
            ->map(fn ($period) => (int) $period)
            ->filter(fn ($period) => $period >= $minimum && array_key_exists($period, $rates))
            ->unique()
            ->sort()
            ->values()
            ->all();

        return $periods ?: collect(array_keys($rates))
            ->map(fn ($period) => (int) $period)
            ->filter(fn ($period) => $period >= $minimum)
            ->sort()
            ->values()
            ->all();
    }
}
