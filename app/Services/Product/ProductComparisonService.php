<?php

namespace App\Services\Product;

use App\DataClasses\ProductFieldTypeOptionsDataClass;
use App\DataClasses\ProductStatusDataClass;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProductComparisonService
{
    public const MAX_PRODUCTS = 4;

    public function parseSlugs(mixed $value): array
    {
        if (! is_string($value)) {
            return [];
        }

        return collect(explode(',', $value))
            ->map(fn (string $slug) => trim($slug))
            ->filter(fn (string $slug) => $slug !== ''
                && mb_strlen($slug) <= 191
                && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/i', $slug) === 1)
            ->unique()
            ->take(self::MAX_PRODUCTS)
            ->values()
            ->all();
    }

    public function getProducts(array $slugs): Collection
    {
        if ($slugs === []) {
            return collect();
        }

        $products = Product::query()
            ->where('is_active', true)
            ->whereIn('slug', $slugs)
            ->with([
                'attributeOptions.attribute',
                'brand',
                'characteristics',
                'colors',
                'country',
                'productType.fields.options',
            ])
            ->get()
            ->keyBy('slug');

        return collect($slugs)
            ->map(fn (string $slug) => $products->get($slug))
            ->filter()
            ->values();
    }

    public function buildRows(Collection $products): Collection
    {
        $labels = [];
        $groups = [];
        $values = [];

        foreach ($products as $product) {
            foreach ($this->valuesForProduct($product) as $key => $item) {
                $labels[$key] ??= $item['label'];
                $groups[$key] ??= $item['group'];
                $values[$product->slug][$key] = $item['value'];
            }
        }

        return collect($labels)->map(function (string $label, string $key) use ($groups, $products, $values) {
            $rowValues = $products->mapWithKeys(fn (Product $product) => [
                $product->slug => $values[$product->slug][$key] ?? null,
            ]);
            $normalizedValues = $rowValues
                ->map(fn ($value) => Str::of((string) ($value ?? ''))->squish()->lower()->toString())
                ->unique();

            return [
                'key' => $key,
                'label' => $label,
                'group' => $groups[$key],
                'values' => $rowValues,
                'different' => $normalizedValues->count() > 1,
            ];
        })->values();
    }

    private function valuesForProduct(Product $product): array
    {
        $values = [];
        $status = $product->availability_status_id === ProductStatusDataClass::PRODUCT_STATUS_NONE
            ? null
            : data_get(ProductStatusDataClass::get($product->availability_status_id), 'name');

        $this->addValue($values, trans('base.brand'), $product->brand?->name, 'main');
        $this->addValue($values, trans('base.comparison_product_type'), $product->productType?->name, 'main');
        $this->addValue($values, trans('base.availability'), $status, 'main');
        $this->addValue($values, trans('base.sku'), $product->sku, 'main');
        $this->addValue($values, trans('base.country'), $product->country?->name, 'main');
        $this->addValue(
            $values,
            trans('base.comparison_colors'),
            $product->colors->pluck('name')->filter()->unique()->implode(', '),
            'main',
        );

        foreach ($product->productType?->fields ?? collect() as $field) {
            if ($field->as_image || ! $field->display_on_single) {
                continue;
            }

            $rawValue = $product->getCustomFieldValue($field->id);
            $value = $rawValue;

            if ($field->field_type_id === ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION) {
                $selectedIds = collect(is_array($rawValue) ? $rawValue : [$rawValue])
                    ->filter(fn ($id) => $id !== null && $id !== '');
                $value = $field->options
                    ->whereIn('id', $selectedIds)
                    ->pluck('name')
                    ->filter()
                    ->implode(', ');
            } elseif (is_array($rawValue)) {
                $value = collect($rawValue)->filter()->implode(', ');
            }

            $this->addValue($values, $field->field_name, $value, 'characteristics');
        }

        foreach ($product->attributeOptions->groupBy('product_attribute_id') as $options) {
            $this->addValue(
                $values,
                $options->first()?->attribute?->attribute_name,
                $options->pluck('name')->filter()->unique()->implode(', '),
                'characteristics',
            );
        }

        foreach ($product->characteristics as $characteristic) {
            $this->addValue($values, $characteristic->name, $characteristic->value, 'characteristics');
        }

        return $values;
    }

    private function addValue(array &$values, mixed $label, mixed $value, string $group): void
    {
        $label = Str::of((string) ($label ?? ''))->squish()->toString();
        $value = Str::of((string) ($value ?? ''))->squish()->toString();

        if ($label === '' || $value === '') {
            return;
        }

        $key = Str::of($label)->lower()->toString();

        if (isset($values[$key]) && $values[$key]['value'] !== $value) {
            $parts = collect(explode(', ', $values[$key]['value']))
                ->push($value)
                ->filter()
                ->unique();
            $values[$key]['value'] = $parts->implode(', ');

            return;
        }

        $values[$key] = compact('label', 'value', 'group');
    }
}
