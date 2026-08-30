<?php

namespace App\Services\Brand;

use App\Helpers\MultiLangRoute;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductType;

class BrandCatalogUrlService
{
    public function preferredProductType(Brand $brand): ?ProductType
    {
        $interiorDoors = ProductType::query()
            ->where('slug', 'interior-doors')
            ->first();

        if ($interiorDoors && $this->hasProducts($brand, $interiorDoors)) {
            return $interiorDoors;
        }

        $product = Product::query()
            ->with(['productType', 'productTypes'])
            ->where('brand_id', $brand->id)
            ->orderBy('id')
            ->first();

        return $product?->productType ?? $product?->productTypes->first();
    }

    public function storefrontUrl(Brand $brand): string
    {
        $productType = $this->preferredProductType($brand);

        if (! $productType) {
            return MultiLangRoute::getMultiLangRoute('store.all-products.filter.page', [
                'catalogFiltersString' => 'brand='.$brand->slug,
            ]);
        }

        return MultiLangRoute::getMultiLangRoute('store.catalog.manufacturer.page', [
            'productTypeSlug' => $productType->slug,
            'brandSlug' => $brand->slug,
        ]);
    }

    private function hasProducts(Brand $brand, ProductType $productType): bool
    {
        return Product::query()
            ->where('brand_id', $brand->id)
            ->where(function ($query) use ($productType) {
                $query->where('product_type_id', $productType->id)
                    ->orWhereHas('productTypes', function ($query) use ($productType) {
                        $query->where('product_types.id', $productType->id);
                    });
            })
            ->exists();
    }
}
