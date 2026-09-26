<?php

namespace App\Services\Product;

use App\Models\DoorConfiguratorItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ConfiguratorCatalog
{
    public function managed(): bool
    {
        return (bool) DB::table('door_configurator_state')->where('id', 1)->value('managed');
    }

    public function presets(): array
    {
        $presets = ['products' => [], 'handles' => []];
        foreach (DoorConfiguratorItem::with('product')->whereNotNull('published')->orderBy('published_sort_order')->orderBy('id')->get() as $item) {
            if ($item->product) {
                $presets[$item->kind === 'door' ? 'products' : 'handles'][] = $this->preset($item, $item->published);
            }
        }

        return $presets;
    }

    public function preset(DoorConfiguratorItem $item, array $data): array
    {
        // IDs are immutable; the current slug is resolved only when building links.
        return array_merge($data, ['id' => $item->key, 'productId' => $item->product_id, 'slug' => $item->product?->slug]);
    }

    public function validateDraft(array $data, string $kind): array
    {
        $rules = [
            'short' => 'sometimes|array:uk,ru', 'short.*' => 'nullable|string|max:160',
            'category' => 'required_if:kind,door|in:interior,exterior',
            'types' => 'sometimes|array', 'types.*' => 'in:hidden,mirror,classic',
            'crop' => 'sometimes|array|size:4', 'crop.*' => 'numeric|min:0|max:10000',
            'handle' => 'sometimes|array|size:2', 'handle.*' => 'numeric|min:0|max:1',
            'face' => 'sometimes|array|size:8', 'face.*' => 'numeric|min:0|max:10000',
            'handleSide' => 'sometimes|in:left,right', 'flush' => 'sometimes|boolean',
            'brand' => 'sometimes|nullable|string|max:160',
            'colors' => 'required_if:kind,door|array|max:100',
            'colors.*' => 'array:id,name,hex,image,preview,colorId,optionIds,crop,handle,handleSide,enabled',
            'colors.*.id' => 'required|string|max:100|regex:/^[a-zA-Z0-9_-]+$/|distinct',
            'colors.*.name' => 'sometimes|array:uk,ru', 'colors.*.name.*' => 'nullable|string|max:160',
            'colors.*.hex' => ['required', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'colors.*.colorId' => 'present|nullable|integer|min:1',
            'colors.*.enabled' => 'sometimes|boolean',
            'colors.*.optionIds' => 'sometimes|array|max:20', 'colors.*.optionIds.*' => 'integer|min:1',
            'colors.*.crop' => 'sometimes|array|size:4', 'colors.*.crop.*' => 'numeric|min:0|max:10000',
            'colors.*.handle' => 'sometimes|array|size:2', 'colors.*.handle.*' => 'numeric|min:0|max:1',
            'colors.*.handleSide' => 'sometimes|in:left,right',
            'colorId' => ($kind === 'handle' ? 'present|' : 'sometimes|').'nullable|integer|min:1',
            'color' => ['sometimes', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'accent' => ['sometimes', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'shape' => 'sometimes|in:square,round',
        ];
        // Only our immutable media namespace can be referenced, never remote URLs or arbitrary files.
        foreach (['image', 'colors.*.image', 'colors.*.preview'] as $field) {
            $rules[$field] = ['sometimes', 'nullable', 'string', 'max:160', 'regex:~^/storage/door-configurator/[a-f0-9]{64}\.(webp|png|jpg)$~D'];
        }
        $validator = Validator::make(array_merge($data, ['kind' => $kind]), $rules);
        $validated = $validator->validate();

        $allowed = $kind === 'door'
            ? ['short', 'category', 'types', 'crop', 'handle', 'face', 'handleSide', 'flush', 'brand', 'colors']
            : ['colorId', 'color', 'accent', 'shape', 'image'];

        return array_intersect_key($validated, array_flip($allowed));
    }

    /** Errors are keyed by shade so the editor can pinpoint what prevents publication. */
    public function issues(DoorConfiguratorItem $item, ?array $data = null): array
    {
        $data ??= $item->draft;
        $product = $item->product;
        if (! $product) {
            return ['product' => 'Товар видалений або ще не прив’язаний до каталогу.'];
        }
        $product->loadMissing(['colors', 'attributeOptions', 'productType.attributes']);
        $issues = [];
        if (! $product->productType || (float) $product->price <= 0 || ! in_array((int) $product->availability_status_id, [2, 3], true)) {
            $issues['product'] = 'У каталозі товар недоступний для замовлення або не має ціни.';
        }
        $variants = $item->kind === 'door' ? ($data['colors'] ?? []) : [$data];
        $enabled = 0;
        $seen = [];
        foreach ($variants as $index => $variant) {
            if (($variant['enabled'] ?? true) === false) {
                continue;
            }
            $enabled++;
            $key = $item->kind === 'door' ? 'colors.'.$index : 'handle';
            $id = $variant['colorId'] ?? null;
            if ($id === null ? $product->colors->isNotEmpty() : ! $product->colors->contains('id', $id)) {
                $issues[$key] = 'Колір більше не належить цьому товару. Оберіть колір із каталогу.';

                continue;
            }
            $ids = $variant['optionIds'] ?? [];
            $sortedIds = $ids;
            sort($sortedIds);
            $combination = json_encode([$id, $sortedIds]);
            if (isset($seen[$combination])) {
                $issues[$key] = 'Ця комбінація кольору й опцій уже додана. Залиште один увімкнений варіант.';

                continue;
            }
            $seen[$combination] = true;
            $options = $product->attributeOptions->whereIn('id', $ids);
            if ($options->count() !== count($ids) || $options->pluck('product_attribute_id')->unique()->count() !== count($ids)
                || ! $options->every(fn ($o) => $product->productType?->attributes->contains('id', $o->product_attribute_id))) {
                $issues[$key] = 'Скло / молдинг не належить товару або вибрано кілька значень одного атрибута.';

                continue;
            }
            $media = app(ConfiguratorMedia::class);
            $image = $media->path($variant['image'] ?? null);
            $preview = $item->kind === 'door' && ($data['category'] ?? '') === 'interior'
                ? $media->path($variant['preview'] ?? null) : $image;
            if (! $image || ! $preview) {
                $issues[$key] = 'Додайте фото картки та, для міжкімнатних дверей, підготовлене фото без ручки.';

                continue;
            }
            if ($item->kind === 'door') {
                $crop = $variant['crop'] ?? $data['crop'] ?? [];
                // Renderer coordinates are normalized to an 800 × 837 reference grid.
                if (count($crop) !== 4 || $crop[2] <= 0 || $crop[3] <= 0 || $crop[0] < 0 || $crop[1] < 0
                    || $crop[0] + $crop[2] > 800 || $crop[1] + $crop[3] > 837) {
                    $issues[$key] = 'Область полотна виходить за межі підготовленого фото. Перевірте кадрування.';
                }
                $handle = $variant['handle'] ?? $data['handle'] ?? [];
                if (($data['category'] ?? '') === 'interior' && count($handle) !== 2) {
                    $issues[$key] = 'Вкажіть положення ручки на полотні.';
                }
                if (isset($data['face'])) {
                    [$lx, $lt, $rx, $rt, $bx, $rb, $ax, $lb] = $data['face'];
                    if ($lx >= $rx || $lx !== $ax || $rx !== $bx || $lt >= $lb || $rt >= $rb || max($lx, $rx) > 800 || max($lt, $rt, $lb, $rb) > 837) {
                        $issues[$key] = 'Невірні точки перспективи. Ліва та права грані мають бути вертикальними в межах сітки 800 × 837.';
                    }
                }
            } elseif (! isset($data['color'], $data['accent'], $data['shape'])) {
                $issues[$key] = 'Оберіть форму й кольори ручки.';
            }
        }
        if (! $enabled) {
            $issues['colors'] = 'Увімкніть хоча б один готовий відтінок.';
        }

        return $issues;
    }

    public function publishable(array $data): array
    {
        if (isset($data['colors'])) {
            $data['colors'] = array_values(array_filter($data['colors'], fn ($color) => $color['enabled'] ?? true));
        }

        return $data;
    }
}
