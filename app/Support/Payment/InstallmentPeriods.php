<?php

namespace App\Support\Payment;

final class InstallmentPeriods
{
    public const MINIMUM = 3;

    public static function for(string $provider): array
    {
        $periods = collect(config("payment.{$provider}.periods", []))
            ->map(fn ($period) => (int) $period)
            ->filter(fn ($period) => $period >= self::MINIMUM)
            ->unique()
            ->sort()
            ->values()
            ->all();

        return $periods ?: [self::MINIMUM];
    }
}
