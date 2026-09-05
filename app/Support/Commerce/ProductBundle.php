<?php

namespace App\Support\Commerce;

use Illuminate\Support\Collection;

final class ProductBundle
{
    public const ROLE_PARENT = 'parent';

    public const ROLE_ITEM = 'item';

    /**
     * Turn the flat pivot rows used for pricing into presentation groups while
     * preserving their original order. Legacy rows remain standalone.
     *
     * @return Collection<int, array{key: ?string, parent: mixed, items: Collection, is_bundle: bool}>
     */
    public static function group(iterable $products): Collection
    {
        $products = collect($products)->values();
        $standalone = collect();
        $bundles = [];

        foreach ($products as $position => $product) {
            $key = trim((string) ($product->pivot?->bundle_key ?? ''));

            if ($key === '') {
                $standalone->push([
                    'position' => $position,
                    'key' => null,
                    'parent' => $product,
                    'items' => collect(),
                    'is_bundle' => false,
                ]);

                continue;
            }

            $bundles[$key] ??= [
                'position' => $position,
                'key' => $key,
                'parent' => null,
                'items' => collect(),
                'is_bundle' => true,
            ];

            $bundles[$key]['position'] = min($bundles[$key]['position'], $position);

            if (($product->pivot?->bundle_role ?? null) === self::ROLE_PARENT && ! $bundles[$key]['parent']) {
                $bundles[$key]['parent'] = $product;
            } else {
                $bundles[$key]['items']->push($product);
            }
        }

        $normalizedBundles = collect($bundles)->map(function (array $bundle) {
            if (! $bundle['parent']) {
                $bundle['parent'] = $bundle['items']->shift();
                $bundle['is_bundle'] = false;
            }

            return $bundle;
        })->filter(fn (array $bundle) => $bundle['parent'] !== null);

        return $standalone
            ->concat($normalizedBundles)
            ->sortBy('position')
            ->values()
            ->map(fn (array $group) => collect($group)->except('position')->all());
    }

    public static function localizedCategory(mixed $value): string
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

    /**
     * Count customer-facing sets instead of every component row. A configured
     * door with a frame and trims is one set; legacy standalone rows still use
     * their ordered quantity.
     */
    public static function countUnits(iterable $groups): int
    {
        return collect($groups)->sum(
            fn (array $group) => max(1, (int) ($group['parent']->pivot?->count ?? 1)),
        );
    }
}
