<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductType;
use App\Services\Product\ProductFiltersService;
use Tests\TestCase;

class ProductFilterSecurityTest extends TestCase
{
    public function test_all_products_search_uses_sql_bindings(): void
    {
        $attack = "x%' OR 1=1 --";
        $query = app(ProductFiltersService::class)->handleAllProductFilters(
            ['search' => $attack],
            Product::query(),
            true,
            [],
        );

        $this->assertStringNotContainsString($attack, $query->toSql());
        $this->assertContains('%'.mb_strtoupper($attack).'%', $query->getBindings());
    }

    public function test_product_type_search_uses_sql_bindings(): void
    {
        $attack = "x%' OR 1=1 --";
        $query = app(ProductFiltersService::class)->handleProductFilters(
            new ProductType,
            ['search' => $attack],
            Product::query(),
            true,
        );

        $this->assertStringNotContainsString($attack, $query->toSql());
        $this->assertContains('%'.mb_strtoupper($attack).'%', $query->getBindings());
    }
}
