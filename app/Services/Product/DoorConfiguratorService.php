<?php

namespace App\Services\Product;

use App\DataClasses\ProductStatusDataClass;
use App\Helpers\MultiLangRoute;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/** Prepared photography is an allowlist; all commercial data stays in the catalog. */
class DoorConfiguratorService
{
    public function presets(): array
    {
        $catalog = app(ConfiguratorCatalog::class);

        return $catalog->managed() ? $catalog->presets() : $this->filePresets();
    }

    public function filePresets(): array
    {
        $presets = json_decode(file_get_contents(resource_path('data/door-configurator.json')), true, 512, JSON_THROW_ON_ERROR);
        $interior = json_decode(file_get_contents(resource_path('data/door-configurator-interior.json')), true, 512, JSON_THROW_ON_ERROR);
        $presets['products'] = array_merge($presets['products'], $interior['products']);

        return $presets;
    }

    public function catalog(?array $presets = null): array
    {
        $presets ??= $this->presets();
        $products = $this->products(collect($presets['products'])->merge($presets['handles'])->all());

        return [
            'products' => collect($presets['products'])->map(fn ($preset) => $this->present($preset, $products->get($this->lookupKey($preset))))->filter()->values()->all(),
            'handles' => collect($presets['handles'])->map(fn ($preset) => $this->present($preset, $products->get($this->lookupKey($preset))))->filter()->values()->all(),
        ];
    }

    public function selection(array $input): array
    {
        $presets = $this->presets();
        $door = collect($presets['products'])->firstWhere('id', $input['product']);
        $handle = empty($input['handle']) ? null : collect($presets['handles'])->firstWhere('id', $input['handle']);
        if (! $door || (! empty($input['handle']) && (! $handle || $door['category'] !== 'interior'))) {
            $this->invalid();
        }
        $products = $this->products(array_filter([$door, $handle]), true);
        $doorModel = $products->get($this->lookupKey($door));
        $display = $this->present($door, $doorModel);
        $color = collect($display['colors'] ?? [])->firstWhere('id', $input['color']);
        if (! $color) {
            $this->invalid();
        }
        $lines = [['product' => $doorModel, 'attributes' => $this->attributes($doorModel, $color['colorId'], $color['optionIds'] ?? [])]];
        $total = $color['price'];
        if ($handle) {
            $model = $products->get($this->lookupKey($handle));
            $displayHandle = $this->present($handle, $model);
            if (! $displayHandle) {
                $this->invalid();
            }
            $lines[] = ['product' => $model, 'attributes' => $this->attributes($model, $displayHandle['colorId'])];
            $total += $displayHandle['price'];
        }

        return ['lines' => $lines, 'total' => round($total, 2)];
    }

    private function lookupKey(array $preset): string
    {
        return isset($preset['productId']) ? 'id:'.$preset['productId'] : 'slug:'.$preset['slug'];
    }

    private function products(array $presets, bool $lock = false): Collection
    {
        // The legacy admin writes is_active=0 even for published products;
        // storefront availability is controlled by availability_status_id.
        $ids = collect($presets)->pluck('productId')->filter()->all();
        $slugs = collect($presets)->reject(fn ($p) => isset($p['productId']))->pluck('slug')->all();
        $query = Product::query()->with(['colors', 'brand', 'productType.attributes', 'attributeOptions'])->where(fn ($q) => $q->whereIn('id', $ids)->orWhereIn('slug', $slugs))
            ->whereHas('productType')->where('price', '>', 0)->whereIn('availability_status_id', [ProductStatusDataClass::PRODUCT_STATUS_STOCK, ProductStatusDataClass::PRODUCT_STATUS_ORDER]);
        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->get()->flatMap(fn ($product) => ['id:'.$product->id => $product, 'slug:'.$product->slug => $product]);
    }

    private function present(array $preset, ?Product $product): ?array
    {
        if (! $product) {
            return null;
        }
        $result = $preset;
        $result['name'] = $product->name;
        $result['brand'] = $product->brand?->name ?? ($preset['brand'] ?? '');
        $result['short'] = $preset['short'][app()->getLocale()] ?? $product->name;
        $result['url'] = MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $product->slug]);
        $result['availability'] = ProductStatusDataClass::get((int) $product->availability_status_id)['name'];
        if (isset($preset['colors'])) {
            $result['colors'] = collect($preset['colors'])->filter(fn ($color) => $this->hasColor($product, $color['colorId']) && $this->hasOptions($product, $color['optionIds'] ?? []))->map(function ($color) use ($product) {
                $catalogColor = $product->colors->firstWhere('id', $color['colorId']);
                $color['name'] = $catalogColor?->name ?? ($color['name'][app()->getLocale()] ?? '');
                $options = $product->attributeOptions->whereIn('id', $color['optionIds'] ?? []);
                $color['price'] = round((float) $product->price + (float) ($catalogColor?->pivot?->price ?? 0) + (float) $options->sum('price'), 2);
                $color['details'] = $options->map(fn ($option) => $product->productType->attributes->firstWhere('id', $option->product_attribute_id)->attribute_name.': '.$option->name)->implode(', ');

                return $color;
            })->values()->all();
            if (! $result['colors']) {
                return null;
            }
            $result['price'] = $result['colors'][0]['price'];
        } else {
            if (! $this->hasColor($product, $preset['colorId'])) {
                return null;
            }
            $color = $product->colors->firstWhere('id', $preset['colorId']);
            $result['finish'] = $color?->name ?? '';
            $result['short'] = $result['finish'];
            $result['price'] = round((float) $product->price + (float) ($color?->pivot?->price ?? 0), 2);
        }

        return $result;
    }

    private function hasColor(Product $product, ?int $id): bool
    {
        // A colorless preset may only represent a genuinely colorless catalog item.
        return $id === null ? $product->colors->isEmpty() : $product->colors->contains('id', $id);
    }

    private function hasOptions(Product $product, array $ids): bool
    {
        $options = $product->attributeOptions->whereIn('id', $ids);
        $attributeIds = $product->productType->attributes->pluck('id');

        return $options->count() === count($ids)
            && $options->pluck('product_attribute_id')->unique()->count() === count($ids)
            && $options->every(fn ($option) => $attributeIds->contains($option->product_attribute_id));
    }

    private function attributes(Product $product, ?int $id, array $optionIds = []): ?array
    {
        $color = $product->colors->firstWhere('id', $id);
        $attributes = $color ? ['color_id' => (string) $color->id, 'color_name' => json_encode($color->getTranslations('name'), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)] : [];
        foreach ($product->attributeOptions->whereIn('id', $optionIds) as $option) {
            $attributes['product_attribute_'.$option->product_attribute_id] = json_encode([
                'id' => $option->id,
                'name' => json_encode($option->getTranslations('name'), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }

        return $attributes ?: null;
    }

    private function invalid(): never
    {
        throw ValidationException::withMessages(['selection' => trans('configurator.unavailable')]);
    }
}
