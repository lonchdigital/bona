<?php

namespace Tests\Unit;

use App\Support\Payment\InstallmentPricing;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class InstallmentPricingTest extends TestCase
{
    public static function rates(): array
    {
        return [
            'mono 3' => ['monobank', 3, 2.9],
            'mono 4' => ['monobank', 4, 4.1],
            'mono 5' => ['monobank', 5, 5.9],
            'mono 6' => ['monobank', 6, 7.2],
            'mono 7' => ['monobank', 7, 8.3],
            'mono 8' => ['monobank', 8, 9.5],
            'mono 9' => ['monobank', 9, 10.8],
            'mono 10' => ['monobank', 10, 12.0],
            'privat 2' => ['privatbank', 2, 3.5],
            'privat 3' => ['privatbank', 3, 3.8],
            'privat 4' => ['privatbank', 4, 4.9],
            'privat 5' => ['privatbank', 5, 6.6],
            'privat 6' => ['privatbank', 6, 7.8],
            'privat 7' => ['privatbank', 7, 9.0],
            'privat 8' => ['privatbank', 8, 10.1],
            'privat 9' => ['privatbank', 9, 11.2],
            'privat 10' => ['privatbank', 10, 12.5],
        ];
    }

    #[DataProvider('rates')]
    public function test_every_configured_rate_is_applied_to_the_financed_total(
        string $provider,
        int $period,
        float $rate,
    ): void {
        $quote = InstallmentPricing::quoteInCents(1_000_000, $provider, $period);
        $expectedFee = (int) round(1_000_000 * $rate / 100);

        $this->assertSame($rate, $quote['rate']);
        $this->assertSame($expectedFee, $quote['fee_in_cents']);
        $this->assertSame(1_000_000 + $expectedFee, $quote['total_in_cents']);
        $this->assertSame(
            intdiv($quote['total_in_cents'] + $period - 1, $period),
            $quote['monthly_in_cents'],
        );
    }

    public function test_money_is_rounded_once_to_the_nearest_kopeck(): void
    {
        $quote = InstallmentPricing::quoteInCents(9_999, 'monobank', 3);

        $this->assertSame(290, $quote['fee_in_cents']);
        $this->assertSame(10_289, $quote['total_in_cents']);
        $this->assertSame(3_430, $quote['monthly_in_cents']);
    }
}
