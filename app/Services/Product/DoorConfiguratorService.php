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
        return json_decode(file_get_contents(resource_path('data/door-configurator.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    public function catalog(): array
    {
        $presets = $this->presets();
        $products = $this->products(collect($presets['products'])->merge($presets['handles'])->pluck('slug')->all());

        return [
            'products' => collect($presets['products'])->map(fn ($preset) => $this->present($preset, $products->get($preset['slug'])))->filter()->values()->all(),
            'handles' => collect($presets['handles'])->map(fn ($preset) => $this->present($preset, $products->get($preset['slug'])))->filter()->values()->all(),
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
        $products = $this->products(array_filter([$door['slug'], $handle['slug'] ?? null]), true);
        $doorModel = $products->get($door['slug']);
        $display = $this->present($door, $doorModel);
        $color = collect($display['colors'] ?? [])->firstWhere('id', $input['color']);
        if (! $color) {
            $this->invalid();
        }
        $lines = [['product' => $doorModel, 'attributes' => $this->attributes($doorModel, $color['colorId'])]];
        $total = $color['price'];
        if ($handle) {
            $model = $products->get($handle['slug']);
            $displayHandle = $this->present($handle, $model);
            if (! $displayHandle) {
                $this->invalid();
            }
            $lines[] = ['product' => $model, 'attributes' => $this->attributes($model, $displayHandle['colorId'])];
            $total += $displayHandle['price'];
        }

        return ['lines' => $lines, 'total' => round($total, 2)];
    }

    private function products(array $slugs, bool $lock = false): Collection
    {
        // The legacy admin writes is_active=0 even for published products;
        // storefront availability is controlled by availability_status_id.
        $query = Product::query()->with(['colors', 'brand', 'productType'])->whereIn('slug', $slugs)
            ->whereHas('productType')->where('price', '>', 0)->whereIn('availability_status_id', [ProductStatusDataClass::PRODUCT_STATUS_STOCK, ProductStatusDataClass::PRODUCT_STATUS_ORDER]);
        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->get()->keyBy('slug');
    }

    private function present(array $preset, ?Product $product): ?array
    {
        if (! $product) {
            return null;
        }
        $result = $preset;
        $result['name'] = $product->name;
        $result['short'] = $preset['short'][app()->getLocale()] ?? $product->name;
        $result['url'] = MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $product->slug]);
        $result['availability'] = ProductStatusDataClass::get((int) $product->availability_status_id)['name'];
        if (isset($preset['colors'])) {
            $result['colors'] = collect($preset['colors'])->filter(fn ($color) => $this->hasColor($product, $color['colorId']))->map(function ($color) use ($product) {
                $catalogColor = $product->colors->firstWhere('id', $color['colorId']);
                $color['name'] = $catalogColor?->name ?? ($color['name'][app()->getLocale()] ?? '');
                $color['price'] = round((float) $product->price + (float) ($catalogColor?->pivot?->price ?? 0), 2);

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

    private function attributes(Product $product, ?int $id): ?array
    {
        $color = $product->colors->firstWhere('id', $id);

        return $color ? ['color_id' => (string) $color->id, 'color_name' => json_encode($color->getTranslations('name'), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)] : null;
    }

    private function invalid(): never
    {
        throw ValidationException::withMessages(['selection' => trans('configurator.unavailable')]);
    }
}
