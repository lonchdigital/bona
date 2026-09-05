<?php

namespace App\Http\Resources\Store\Cart;

use App\DataClasses\ProductStatusDataClass;
use App\Http\Resources\BaseProductResource;
use Illuminate\Http\Request;

class CartProductResource extends BaseProductResource
{
    public function toArray(Request $request): array
    {
        $existingMapping = parent::toArray($request);
        $rawNames = $this->resource->getRawOriginal('name');

        if (is_string($rawNames)) {
            $rawNames = json_decode($rawNames, true);
        }

        $displayName = is_array($rawNames)
            ? ($rawNames[app()->getLocale()] ?? $rawNames[config('app.fallback_locale')] ?? reset($rawNames))
            : $this->resource->name;

        $existingMapping['count'] = $this->resource->pivot->count;
        $existingMapping['price'] = round($this->resource->pivot->count * $this->resource->pivot->price, 2);
        $existingMapping['price_per_product'] = $this->resource->price;
        $existingMapping['price_per_product_with_attributes'] = $this->resource->price + $this->resource->pivot->attributes_price;
        $existingMapping['attributes'] = $this->resource->pivot->attributes;
        $existingMapping['configuration'] = $this->configuration();
        $existingMapping['attributes_price'] = $this->resource->pivot->attributes_price;
        $existingMapping['current_image_path'] = $this->resource->pivot->current_image_path;
        // CartService keeps the raw JSON name for the legacy drawer. Resolve
        // the storefront label from that source explicitly so the cart page
        // never prints the JSON payload when the resource is serialized.
        $existingMapping['display_name'] = (string) $displayName;
        $existingMapping['brand_name'] = $this->resource->brand?->name;
        $existingMapping['availability'] = ProductStatusDataClass::get((int) $this->resource->availability_status_id)['name'] ?? null;
        $existingMapping['line_total'] = round(
            ((float) $this->resource->pivot->price + (float) ($this->resource->pivot->attributes_price ?? 0))
                * (int) $this->resource->pivot->count,
            2
        );

        return $existingMapping;
    }

    /** @return array<int, array{key: string, id: int|string, label: string}> */
    private function configuration(): array
    {
        $attributes = $this->resource->pivot->attributes;
        $attributes = is_string($attributes) ? json_decode($attributes, true) : $attributes;

        if (! is_array($attributes)) {
            return [];
        }

        $configuration = [];
        $colorId = $attributes['color_id'] ?? null;

        if ($colorId !== null && $colorId !== '') {
            $color = $this->resource->colors?->firstWhere('id', (int) $colorId);
            $fallbackColorName = $attributes['color_name'] ?? '';

            $configuration[] = [
                'key' => 'color_name',
                'id' => $colorId,
                'label' => (string) ($color?->name ?: $fallbackColorName),
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

            $label = $this->localizedLabel($option['name'] ?? '');
            if ($label === '') {
                continue;
            }

            $configuration[] = [
                'key' => (string) $key,
                'id' => $option['id'],
                'label' => $label,
            ];
        }

        return $configuration;
    }

    private function localizedLabel(mixed $value): string
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : $value;
        }

        if (! is_array($value)) {
            return trim((string) $value);
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
}
