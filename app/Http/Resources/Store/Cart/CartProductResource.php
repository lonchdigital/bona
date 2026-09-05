<?php

namespace App\Http\Resources\Store\Cart;

use App\DataClasses\ProductStatusDataClass;
use App\Http\Resources\BaseProductResource;
use App\Support\Commerce\ProductBundle;
use App\Support\Commerce\ProductConfiguration;
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
        $existingMapping['line_id'] = (int) $this->resource->pivot->id;
        $existingMapping['price'] = round($this->resource->pivot->count * $this->resource->pivot->price, 2);
        $existingMapping['price_per_product'] = $this->resource->pivot->price;
        $existingMapping['price_per_product_with_attributes'] = $this->resource->pivot->price + $this->resource->pivot->attributes_price;
        $existingMapping['attributes'] = $this->resource->pivot->attributes;
        $existingMapping['configuration'] = $this->configuration();
        $existingMapping['attributes_price'] = $this->resource->pivot->attributes_price;
        $existingMapping['current_image_path'] = $this->resource->pivot->current_image_path;
        $existingMapping['bundle'] = $this->resource->pivot->bundle_key ? [
            'key' => (string) $this->resource->pivot->bundle_key,
            'role' => (string) $this->resource->pivot->bundle_role,
            'category' => ProductBundle::localizedCategory($this->resource->pivot->bundle_category),
        ] : null;
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

    /** @return array<int, array{key: string, id: int|string, name: string, label: string, swatch: ?string}> */
    private function configuration(): array
    {
        return ProductConfiguration::for($this->resource, $this->resource->pivot->attributes);
    }
}
