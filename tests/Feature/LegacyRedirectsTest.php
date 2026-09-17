<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\ProductSlugRedirect;
use App\Models\ProductText;
use App\Services\Sitemap\SitemapService;
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
        $this->get('/ru/product-category/aksessuar/category/dobir-estet')->assertStatus(301)->assertRedirect('/ru/product-category/aksessuar/category/dobir');

        $this->seedCurrency();
        $product = $this->makeProduct(['slug' => 'tehno-1-antracit-2050-860']);
        $product->update(['slug' => 'tehno-1-2050-860-antracit-7024']);
        $this->get('/ru/product/tehno-1-antracit-2050-860/similar')->assertStatus(301)->assertRedirect('/ru/product/tehno-1-2050-860-antracit-7024');

        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /*filtered-count');
    }

    public function test_old_woocommerce_query_links_and_old_articles_redirect_to_clean_addresses(): void
    {
        $this->get('/product-category/interior-doors?filter_style=milano')->assertStatus(301)->assertRedirect(url('/product-category/interior-doors'));
        $this->get('/product-category/hidden-doors?orderby=date&add_to_wishlist=2663')->assertStatus(301)->assertRedirect(url('/product-category/hidden-doors'));
        $this->get('/product-category/interior-doors?orderby=price-desc&page=2')->assertStatus(301)->assertRedirect(url('/product-category/interior-doors?page=2'));
        $this->get('/ru/blog/yak-doglyadaty-za-dveryma-comeo')->assertStatus(301)->assertRedirect('/ru/blog/yak-dohlyadaty-za-dveryma-comeo-praktychni-porady');
        $this->get('/robots.txt')->assertSee('Disallow: /*/count/');
    }

    public function test_techno_slugs_and_korfad_typos_are_fixed_by_migration(): void
    {
        $this->seedCurrency();
        $door = $this->makeProduct(['slug' => 'tehno-1-2050-960-antracit-7024']);
        $korfad = $this->makeProduct(['slug' => 'mizhkimnatni-dveri-porto-pr-01-korfad']);
        ProductText::query()->create(['product_id' => $korfad->id, 'language' => 'uk', 'content' => '<p>Виробник міжкімнатних дверей ТМ МKorfad г. Корюківка.</p><p>Двері покриваються плівкой Sincrolam.</p>']);
        ProductText::query()->create(['product_id' => $korfad->id, 'language' => 'ru', 'content' => '<p>Производитель ТМ МKorfad г. Корюковка.</p>']);

        (require database_path('migrations/2026_09_17_120000_fix_korfad_copy_and_tehno_redirects.php'))->up();

        $this->get('/product/tehno-1-antracit-2050-960')->assertStatus(301)->assertRedirect(url('/product/tehno-1-2050-960-antracit-7024'));
        $this->assertSame('<p>Виробник міжкімнатних дверей ТМ Korfad м. Корюківка.</p><p>Двері покриваються плівкою Sincrolam.</p>', ProductText::query()->where(['product_id' => $korfad->id, 'language' => 'uk'])->value('content'));
        $this->assertSame('<p>Производитель ТМ Korfad г. Корюковка.</p>', ProductText::query()->where(['product_id' => $korfad->id, 'language' => 'ru'])->value('content'));
        $this->assertNotNull($door);
    }

    public function test_brand_letter_pages_fold_into_a_real_manufacturers_hub(): void
    {
        $this->seedCurrency();
        $type = $this->productType(['slug' => 'interior-doors', 'name' => 'Міжкімнатні двері']);
        $withProducts = Brand::query()->create(['creator_id' => $this->author()->id, 'name' => ['uk' => 'Korfad', 'ru' => 'Korfad'], 'slug' => 'korfad', 'description' => ['uk' => '', 'ru' => '']]);
        Brand::query()->create(['creator_id' => $this->author()->id, 'name' => ['uk' => 'Порожній', 'ru' => 'Пустой'], 'slug' => 'empty-brand', 'description' => ['uk' => '', 'ru' => '']]);
        $this->makeProduct(['product_type_id' => $type->id, 'brand_id' => $withProducts->id]);

        foreach (['/brands/list/k', '/brands/list/zzz', '/brands/list'] as $letterPage) {
            $this->get($letterPage)->assertStatus(301)->assertRedirect('/brands/list/all');
        }
        $this->get('/brands/list/all')
            ->assertOk()
            ->assertSee('<title>Виробники дверей і фурнітури — Bona Doors</title>', false)
            ->assertSee('/product-category/interior-doors/manufacturer/korfad', false)
            ->assertSee('Korfad')
            ->assertDontSee('Порожній')
            ->assertDontSee('шпалер')
            ->assertDontSee('src="/storage/"', false);

        $this->assertStringContainsString('/brands/list/all', app(SitemapService::class)->buildSitemap()->render());

        // Russian last: the test client keeps the locale of the previous request.
        $this->get('/ru/brands/list/s')->assertStatus(301)->assertRedirect('/ru/brands/list/all');
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
