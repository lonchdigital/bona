<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductGalleries;
use App\Models\ProductType;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Tests\TestCase;

class CatalogRedesignTest extends TestCase
{
    public function test_product_card_switches_real_color_image_and_price_data(): void
    {
        $product = new Product([
            'name' => ['uk' => 'Двері Нью-Йорк', 'ru' => 'Двери Нью-Йорк'],
            'slug' => 'new-york',
            'price' => 10000,
            'old_price' => 12000,
            'main_image_path' => 'products/default.webp',
            'availability_status_id' => 2,
            'main_color_id' => 8,
        ]);
        $product->id = 15;
        $product->setRelation('brand', new Brand(['name' => ['uk' => 'ArtPorte', 'ru' => 'ArtPorte']]));
        $product->setRelation('productType', new ProductType(['name' => ['uk' => 'Міжкімнатні двері', 'ru' => 'Межкомнатные двери']]));

        $color = new Color([
            'name' => ['uk' => 'Графіт', 'ru' => 'Графит'],
            'hex' => '#343638',
        ]);
        $color->id = 8;
        $color->setRelation('pivot', new Pivot(['price' => 1250]));

        $alternateColor = new Color([
            'name' => ['uk' => 'Білий', 'ru' => 'Белый'],
            'hex' => '#f4f3f0',
        ]);
        $alternateColor->id = 9;
        $alternateColor->setRelation('pivot', new Pivot(['price' => 800]));

        $gallery = new ProductGalleries([
            'product_id' => 15,
            'color_id' => 9,
            'image_path' => 'products/new-york-white.webp',
        ]);

        $product->setRelation('colors', collect([$alternateColor, $color]));
        $product->setRelation('galleries', collect([$gallery]));

        $html = view('components.store.product-card', [
            'product' => $product,
            'baseCurrency' => (object) ['name_short' => 'грн'],
        ])->render();

        $this->assertStringContainsString('data-product-card', $html);
        $this->assertStringContainsString('data-product-card-swatch', $html);
        $this->assertStringContainsString('data-image="/storage/products/new-york-white.webp"', $html);
        $this->assertStringContainsString('data-price-adjustment="800"', $html);
        $this->assertStringContainsString('data-price-adjustment="1250"', $html);
        $this->assertStringContainsString('11 250 грн', $html);
        $this->assertStringContainsString('13 250 грн', $html);
    }

    public function test_catalog_keeps_the_existing_filter_contract_inside_the_new_layout(): void
    {
        $content = file_get_contents(resource_path('views/pages/store/partials/catalog-content.blade.php'));
        $filters = file_get_contents(resource_path('views/pages/store/partials/catalog-filters.blade.php'));
        $toolbar = file_get_contents(resource_path('views/pages/store/partials/catalog-toolbar.blade.php'));

        $this->assertStringContainsString('bona-catalog__grid', $content);
        $this->assertStringContainsString('catalog-consultant-card', $content);
        $this->assertStringContainsString('id="filter-left-form"', $filters);
        $this->assertStringContainsString('filter-submit-main', $filters);
        $this->assertStringContainsString('filter-reset', $filters);
        $this->assertStringContainsString('id="art-filter-display"', $toolbar);
        $this->assertStringContainsString('sort-by-option', $toolbar);
    }

    public function test_home_and_catalog_use_one_shared_product_card(): void
    {
        $home = file_get_contents(resource_path('views/components/store/home-popular-products.blade.php'));
        $catalog = file_get_contents(resource_path('views/pages/store/partials/product_item.blade.php'));

        $this->assertStringContainsString('x-store.product-card', $home);
        $this->assertStringContainsString('x-store.product-card', $catalog);
        $this->assertStringContainsString('variant="slider"', $home);
        $this->assertStringContainsString('variant="catalog"', $catalog);
    }
}
