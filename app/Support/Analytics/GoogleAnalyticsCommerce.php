<?php

namespace App\Support\Analytics;

use App\DataClasses\DeliveryTypesDataClass;
use App\DataClasses\PaymentTypesDataClass;
use App\Models\Order;
use App\Models\Product;
use App\Support\Commerce\ProductConfiguration;
use Illuminate\Support\Collection;

/**
 * Builds GA4 recommended e-commerce payloads.
 *
 * Names are always Ukrainian and payment/delivery values are stable keys, so
 * the /ru storefront does not split every product and funnel step in two.
 */
final class GoogleAnalyticsCommerce
{
    public const CURRENCY = 'UAH';

    private const PAYMENT_TYPES = [
        PaymentTypesDataClass::CASH_PAYMENT => 'cash',
        PaymentTypesDataClass::CARD_PAYMENT => 'card_liqpay',
        PaymentTypesDataClass::CARD_PAYMENT_PAYPART => 'installments_privatbank',
        PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK => 'installments_monobank',
        PaymentTypesDataClass::INVOICE_PAYMENT => 'invoice',
        PaymentTypesDataClass::MANAGER_CONFIRMATION_PAYMENT => 'manager_confirmation',
    ];

    private const DELIVERY_TYPES = [
        DeliveryTypesDataClass::ADDRESS_DELIVERY => 'courier_odesa',
        DeliveryTypesDataClass::NP_DELIVERY => 'nova_poshta',
        DeliveryTypesDataClass::MIST_EXPRESS_DELIVERY => 'meest',
        DeliveryTypesDataClass::PICK_UP_DELIVERY => 'store_pickup',
        DeliveryTypesDataClass::SAT_DELIVERY => 'sat',
    ];

    /**
     * @return array<string, mixed>
     */
    public static function item(Product $product, ?float $price = null, int $quantity = 1, ?string $variant = null): array
    {
        $product->loadMissing(['brand', 'productType']);

        return array_filter([
            'item_id' => filled($product->sku) ? (string) $product->sku : (string) $product->slug,
            'item_name' => self::ukrainian(self::rawTranslations($product->getRawOriginal('name'))) ?: (string) $product->name,
            'item_brand' => $product->brand ? (self::ukrainian($product->brand->getTranslations('name')) ?: (string) $product->brand->name) : null,
            'item_category' => $product->productType ? (self::ukrainian($product->productType->getTranslations('name')) ?: (string) $product->productType->name) : null,
            'item_variant' => filled($variant) ? $variant : null,
            'price' => round((float) ($price ?? $product->price), 2),
            'quantity' => max(1, $quantity),
        ], fn ($value) => $value !== null && $value !== '');
    }

    /**
     * A cart or order line: unit price includes the selected options.
     *
     * @return array<string, mixed>
     */
    public static function lineItem(Product $product): array
    {
        $pivot = $product->pivot;
        $variant = collect(ProductConfiguration::for($product, $pivot?->attributes))
            ->pluck('label')
            ->filter()
            ->implode(' / ');

        return self::item(
            $product,
            (float) ($pivot?->price ?? $product->price) + (float) ($pivot?->attributes_price ?? 0),
            (int) ($pivot?->count ?? 1),
            $variant,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function viewItem(Product $product): array
    {
        $item = self::item($product);

        return ['currency' => self::CURRENCY, 'value' => $item['price'], 'items' => [$item]];
    }

    /**
     * @param  Collection<int, Product>  $products
     * @return array<string, mixed>
     */
    public static function beginCheckout(Collection $products, float $value, ?string $coupon): array
    {
        return array_filter([
            'currency' => self::CURRENCY,
            'value' => round($value, 2),
            'coupon' => $coupon ?: null,
            'items' => $products->map(fn (Product $product) => self::lineItem($product))->values()->all(),
        ], fn ($value) => $value !== null);
    }

    /**
     * @param  array<string, mixed>  $summary  PricingService::forOrder()
     * @return array<string, mixed>
     */
    public static function purchase(Order $order, array $summary): array
    {
        $shipping = (float) ($summary['delivery'] ?? 0);

        return array_filter([
            'transaction_id' => 'BD-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
            'currency' => self::CURRENCY,
            'value' => round((float) ($summary['total'] ?? 0) - $shipping, 2),
            'shipping' => round($shipping, 2),
            'coupon' => $order->promoCode?->code,
            'payment_type' => self::paymentType($order->payment_type_id),
            'shipping_tier' => self::deliveryType($order->delivery_type_id),
            'items' => $order->products->map(fn (Product $product) => self::lineItem($product))->values()->all(),
        ], fn ($value) => $value !== null);
    }

    public static function paymentType(mixed $id): ?string
    {
        return self::PAYMENT_TYPES[(int) $id] ?? null;
    }

    public static function deliveryType(mixed $id): ?string
    {
        return self::DELIVERY_TYPES[(int) $id] ?? null;
    }

    /** @return array<int, string> */
    public static function paymentTypeKeys(): array
    {
        return self::PAYMENT_TYPES;
    }

    /** @return array<int, string> */
    public static function deliveryTypeKeys(): array
    {
        return self::DELIVERY_TYPES;
    }

    /**
     * The cart service overwrites the name attribute with its raw JSON, so the
     * stored original is the only reliable source of the translations.
     */
    private static function rawTranslations(mixed $raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }

        $decoded = is_string($raw) ? json_decode($raw, true) : null;

        return is_array($decoded) ? $decoded : [];
    }

    private static function ukrainian(mixed $translations): string
    {
        return is_array($translations) ? trim((string) ($translations['uk'] ?? reset($translations) ?: '')) : '';
    }
}
