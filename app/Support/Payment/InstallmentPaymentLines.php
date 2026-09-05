<?php

namespace App\Support\Payment;

use App\Models\Order;
use RuntimeException;

/**
 * Builds bank receipt lines whose cent total exactly matches order pricing.
 *
 * A promotion is folded proportionally into the product prices because the
 * installment APIs do not have a separate negative discount line. When a
 * discounted row cannot be divided evenly by its quantity, it is split into
 * at most two rows that differ by one kopeck.
 */
final class InstallmentPaymentLines
{
    /**
     * @param  array{products: float, discount: float, delivery: float, installment_fee: float, installment_rate: float, total_in_cents: int}  $summary
     * @return array<int, array{name: string, count: int, unit_in_cents: int}>
     */
    public static function forOrder(Order $order, array $summary): array
    {
        $rows = $order->products
            ->map(function ($product) {
                $unitInCents = self::toCents(
                    (float) $product->pivot->price + (float) ($product->pivot->attributes_price ?? 0)
                );

                return [
                    'name' => (string) $product->name,
                    'count' => max(0, (int) $product->pivot->count),
                    'unit_in_cents' => max(0, $unitInCents),
                ];
            })
            ->filter(fn (array $row) => $row['count'] > 0)
            ->values();

        $productsInCents = max(0, self::toCents((float) $summary['products']));
        $discountInCents = min($productsInCents, max(0, self::toCents((float) $summary['discount'])));
        $lines = [];
        $cumulativeGrossInCents = 0;
        $allocatedDiscountInCents = 0;

        foreach ($rows as $row) {
            $grossInCents = $row['unit_in_cents'] * $row['count'];
            $cumulativeGrossInCents += $grossInCents;
            $discountAfterThisRow = $productsInCents > 0
                ? intdiv($discountInCents * $cumulativeGrossInCents, $productsInCents)
                : 0;
            $rowDiscountInCents = min(
                $grossInCents,
                max(0, $discountAfterThisRow - $allocatedDiscountInCents),
            );
            $allocatedDiscountInCents += $rowDiscountInCents;

            self::appendDividedRow(
                $lines,
                $row['name'],
                $row['count'],
                $grossInCents - $rowDiscountInCents,
            );
        }

        $deliveryInCents = max(0, self::toCents((float) $summary['delivery']));
        if ($deliveryInCents > 0) {
            $lines[] = [
                'name' => trans('base.delivery'),
                'count' => 1,
                'unit_in_cents' => $deliveryInCents,
            ];
        }

        $installmentFeeInCents = max(0, self::toCents((float) $summary['installment_fee']));
        if ($installmentFeeInCents > 0) {
            $lines[] = [
                'name' => trans('base.installment_surcharge_with_rate', [
                    'rate' => self::formatRate((float) $summary['installment_rate']),
                ]),
                'count' => 1,
                'unit_in_cents' => $installmentFeeInCents,
            ];
        }

        $linesTotalInCents = collect($lines)->sum(
            fn (array $line) => $line['unit_in_cents'] * $line['count']
        );

        if ($linesTotalInCents !== (int) $summary['total_in_cents']) {
            throw new RuntimeException('Installment payment lines do not match the order total.');
        }

        return $lines;
    }

    /**
     * @param  array<int, array{name: string, count: int, unit_in_cents: int}>  $lines
     */
    private static function appendDividedRow(array &$lines, string $name, int $count, int $totalInCents): void
    {
        if ($count <= 0 || $totalInCents <= 0) {
            return;
        }

        $lowerUnitInCents = intdiv($totalInCents, $count);
        $higherUnitCount = $totalInCents % $count;
        $lowerUnitCount = $count - $higherUnitCount;

        if ($lowerUnitCount > 0 && $lowerUnitInCents > 0) {
            $lines[] = [
                'name' => $name,
                'count' => $lowerUnitCount,
                'unit_in_cents' => $lowerUnitInCents,
            ];
        }

        if ($higherUnitCount > 0) {
            $lines[] = [
                'name' => $name,
                'count' => $higherUnitCount,
                'unit_in_cents' => $lowerUnitInCents + 1,
            ];
        }
    }

    private static function toCents(float $amount): int
    {
        return (int) round($amount * 100, 0, PHP_ROUND_HALF_UP);
    }

    private static function formatRate(float $rate): string
    {
        return rtrim(rtrim(number_format($rate, 2, '.', ''), '0'), '.');
    }
}
