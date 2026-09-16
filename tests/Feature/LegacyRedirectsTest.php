<?php

namespace Tests\Feature;

use App\Models\ProductSlugRedirect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class LegacyRedirectsTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_renamed_product_keeps_its_old_address_in_both_languages_without_chains(): void
    {
        $this->seedCurrency();
        $product = $this->makeProduct(['slug' => 'avangard-bakardi']);

        $product->update(['slug' => 'bakardi-avangard']);
        $product->update(['slug' => 'bakardi-avangard-qdoors-vhidni']);

        $this->get('/product/avangard-bakardi?utm_source=gsc')
            ->assertStatus(301)
            ->assertRedirect(url('/product/bakardi-avangard-qdoors-vhidni?utm_source=gsc'));
        $this->get('/ru/product/bakardi-avangard')
            ->assertStatus(301)
            ->assertRedirect(url('/ru/product/bakardi-avangard-qdoors-vhidni'));
        $this->get('/product/bakardi-avangard-qdoors-vhidni')->assertOk();
        $this->get('/product/never-existed')->assertNotFound();
    }

    public function test_reusing_an_old_slug_serves_the_new_product_instead_of_redirecting(): void
    {
        $this->seedCurrency();
        $first = $this->makeProduct(['slug' => 'loft']);
        $first->update(['slug' => 'loft-sliding']);
        $second = $this->makeProduct(['slug' => 'loft-temp']);
        $second->update(['slug' => 'loft']);

        $this->assertNull(ProductSlugRedirect::query()->where('slug', 'loft')->first());
        $this->get('/product/loft')->assertOk();
    }

    public function test_deleted_product_and_its_old_addresses_lead_to_its_catalog(): void
    {
        $this->seedCurrency();
        $type = $this->productType(['slug' => 'dvery-na-ulycu']);
        $product = $this->makeProduct(['slug' => 'trio-kvadro', 'product_type_id' => $type->id]);
        $product->update(['slug' => 'kvadro-trio-fort-m-vhidni']);

        $product->delete();

        $this->get('/product/kvadro-trio-fort-m-vhidni')->assertStatus(301)->assertRedirect(url('/product-category/dvery-na-ulycu'));
        $this->get('/ru/product/trio-kvadro')->assertStatus(301)->assertRedirect(url('/ru/product-category/dvery-na-ulycu'));
    }

    public function test_search_console_legacy_paths_redirect_and_counters_are_disallowed(): void
    {
        $this->get('/ru/product/tehno-1-2050-860-antracit-7024/similar')->assertStatus(301)->assertRedirect('/ru/product/tehno-1-2050-860-antracit-7024');
        $this->get('/blog/doborni-planky-ta-nalychnyky')->assertStatus(301)->assertRedirect('/product-category/aksessuar/category/dobir');
        $this->get('/ru/nashi-roboty/testoviy')->assertStatus(301)->assertRedirect('/ru/nashi-roboty');

        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /*filtered-count');
    }

    public function test_search_console_map_points_only_to_valid_targets(): void
    {
        $map = json_decode(file_get_contents(database_path('content/redirects/2026_09_17_search_console_404.json')), true);

        $this->assertCount(169, $map);
        foreach ($map as $oldSlug => $target) {
            $this->assertMatchesRegularExpression('/^[a-z0-9.-]+$/', $oldSlug);
            $this->assertTrue(isset($target['product']) xor isset($target['path']), $oldSlug);
            if (isset($target['path'])) {
                $this->assertStringStartsWith('/product-category/', $target['path']);
            }
        }
    }
}
