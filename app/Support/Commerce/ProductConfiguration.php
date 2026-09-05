<?php

namespace App\Support\Commerce;

use App\Models\Product;

final class ProductConfiguration
{
    /**
     * @return array<int, array{key: string, id: int|string, name: string, label: string, swatch: ?string}>
     */
    public static function for(Product $product, mixed $rawAttributes): array
    {
        $attributes = is_string($rawAttributes) ? json_decode($rawAttributes, true) : $rawAttributes;

        if (! is_array($attributes)) {
            return [];
        }

        $product->loadMissing(['colors', 'productType.attributes']);
        $configuration = [];
        $colorId = $attributes['color_id'] ?? null;

        if ($colorId !== null && $colorId !== '') {
            $color = $product->colors->firstWhere('id', (int) $colorId);
            $fallbackColorName = self::localized($attributes['color_name'] ?? '');

            $configuration[] = [
                'key' => 'color_name',
                'id' => $colorId,
                'name' => trans('base.color'),
                'label' => trim((string) ($color?->name ?: $fallbackColorName)),
                'swatch' => self::safeHex($color?->hex),
            ];
        }

        foreach ($attributes as $key => $value) {
            if (in_array($key, ['color_id', 'color_name'], true) || $value === null) {
                continue;
            }

            $option = is_string($value) ? json_decode($value, true) : $value;
            if (! is_array($option) || ! array_key_exists('id', $option)) {
                continue;
            }

            $label = self::localized($option['name'] ?? '');
            if ($label === '') {
                continue;
            }

            preg_match('/(\d+)$/', (string) $key, $matches);
            $attributeId = isset($matches[1]) ? (int) $matches[1] : null;
            $attribute = $attributeId
                ? $product->productType?->attributes?->firstWhere('id', $attributeId)
                : null;

            $configuration[] = [
                'key' => (string) $key,
                'id' => $option['id'],
                'name' => trim((string) ($attribute?->attribute_name ?? '')),
                'label' => $label,
                'swatch' => null,
            ];
        }

        return $configuration;
    }

    private static function localized(mixed $value): string
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : $value;
        }

        if (! is_array($value)) {
            return trim((string) $value);
        }

        if (array_key_exists('name', $value)) {
            return self::localized($value['name']);
        }

        return trim((string) (
            $value[app()->getLocale()]
            ?? $value[config('app.fallback_locale')]
            ?? $value['uk']
            ?? $value['ru']
            ?? reset($value)
            ?? ''
        ));
    }

    private static function safeHex(mixed $value): ?string
    {
        $hex = trim((string) $value);

        if (! preg_match('/^#?[0-9a-f]{3}(?:[0-9a-f]{1}|[0-9a-f]{3}|[0-9a-f]{5})?$/i', $hex)) {
            return null;
        }

        return str_starts_with($hex, '#') ? $hex : '#'.$hex;
    }
}
