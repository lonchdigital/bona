<?php

namespace Tests\Feature;

use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class ManufacturerCatalogTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_manufacturer_url_renders_the_catalog_with_its_brand_filter_selected(): void
    {
        $this->seedCurrency();
        $productType = $this->productType([
            'slug' => 'interior-doors',
            'name' => ['uk' => 'Міжкімнатні двері', 'ru' => 'Межкомнатные двери'],
            'has_brand' => true,
        ]);
        $artporte = $this->brand('artporte', 'Artporte');
        $otherBrand = $this->brand('other-brand', 'Інший виробник');

        $matchingProduct = $this->makeProduct([
            'slug' => 'artporte-door',
            'product_type_id' => $productType->id,
            'brand_id' => $artporte->id,
            'name' => ['uk' => 'Двері Artporte', 'ru' => 'Двери Artporte'],
        ]);
        $otherProduct = $this->makeProduct([
            'slug' => 'other-door',
            'product_type_id' => $productType->id,
            'brand_id' => $otherBrand->id,
            'name' => ['uk' => 'Сторонні двері', 'ru' => 'Другие двери'],
        ]);

        $response = $this->get(route('store.catalog.manufacturer.page', [
            'productTypeSlug' => $productType->slug,
            'brandSlug' => $artporte->slug,
        ]));

        $response
            ->assertOk()
            ->assertSee('Міжкімнатні двері Artporte')
            ->assertSee('name="brand"', false)
            ->assertSee('value="artporte"', false)
            ->assertSee('checked', false)
            ->assertSee($matchingProduct->name)
            ->assertDontSee($otherProduct->name)
            ->assertDontSee('Mali'.'na', false);
    }

    public function test_legacy_brand_page_permanently_redirects_to_the_filtered_catalog(): void
    {
        $productType = $this->productType([
            'slug' => 'interior-doors',
            'name' => ['uk' => 'Міжкімнатні двері', 'ru' => 'Межкомнатные двери'],
            'has_brand' => true,
        ]);
        $brand = $this->brand('artporte', 'Artporte');
        $this->makeProduct([
            'product_type_id' => $productType->id,
            'brand_id' => $brand->id,
        ]);

        $expectedUrl = route('store.catalog.manufacturer.page', [
            'productTypeSlug' => $productType->slug,
            'brandSlug' => $brand->slug,
        ]);

        $this->get(route('store.brand.page', ['brandSlug' => $brand->slug]))
            ->assertStatus(301)
            ->assertRedirect($expectedUrl);
    }

    private function brand(string $slug, string $name): Brand
    {
        return Brand::query()->create([
            'creator_id' => $this->author()->id,
            'name' => ['uk' => $name, 'ru' => $name],
            'slug' => $slug,
            'description' => ['uk' => '', 'ru' => ''],
        ]);
    }
}
