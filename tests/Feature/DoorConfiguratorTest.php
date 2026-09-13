<?php

namespace Tests\Feature;

use App\Models\CartProducts;
use App\Models\Color;
use App\Services\Product\DoorConfiguratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class DoorConfiguratorTest extends TestCase
{
    use MakesShopData, RefreshDatabase;

    private function door(array $overrides = [])
    {
        $this->seedCurrency();
        // Match ProductService::createProduct/productEdit and the live legacy catalog.
        $door = $this->makeProduct(array_merge(['slug' => 'mizhkimnatni-dveri-artporte-nyu-york', 'price' => 5000, 'availability_status_id' => 2, 'is_active' => 0], $overrides));
        $color = Color::create(['id' => 148, 'slug' => 'ivory', 'hex' => '#eeeedd', 'display_as_image' => false, 'name' => ['uk' => 'Айворі', 'ru' => 'Айвори'], 'creator_id' => $this->author()->id]);
        $door->colors()->attach($color->id, ['price' => 250]);

        return $door;
    }

    private function payload(array $overrides = []): array
    {
        return array_merge(['product' => 'new-york', 'color' => 'ivory', 'request_id' => (string) Str::uuid(), 'expected_total' => 5250], $overrides);
    }

    public function test_live_catalog_and_localized_seo_render_without_demo_prices_or_duplicate_main(): void
    {
        $door = $this->door(['name' => ['uk' => 'Двері з каталогу', 'ru' => 'Дверь из каталога']]);
        foreach (['uk' => '/door-configurator', 'ru' => '/ru/door-configurator'] as $locale => $path) {
            $response = $this->get($path)->assertOk()->assertSee('data-door-studio', false)->assertDontSee('Локальний прототип');
            $html = $response->getContent();
            preg_match('/<script type="application\/json" id="door-studio-data">(.*?)<\/script>/s', $html, $matches);
            $data = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
            $this->assertSame($locale, $data['locale']);
            $this->assertSame($door->getTranslation('name', $locale), $data['products'][0]['name']);
            $this->assertSame(5250, $data['products'][0]['colors'][0]['price']);
            $this->assertCount(1, $data['products'][0]['colors']);
            $this->assertSame(1, substr_count($html, '<main'));
            $this->assertStringContainsString('hreflang="uk-UA"', $html);
            $this->assertStringContainsString('hreflang="ru-UA"', $html);
            $this->assertStringNotContainsString('"@type":"Product"', $html);
        }
    }

    public function test_empty_catalog_remains_useful_and_does_not_offer_out_of_stock_products(): void
    {
        $this->door(['availability_status_id' => 4]);
        $this->get('/door-configurator')->assertOk()->assertSee('Наразі моделі для примірки недоступні')->assertDontSee('data-door-studio', false);
        $this->assertSame([], app(DoorConfiguratorService::class)->catalog()['products']);
    }

    public function test_both_configurator_locales_are_discoverable_in_sitemap(): void
    {
        $this->get('/sitemap.xml')->assertOk()
            ->assertSee('<loc>'.url('/door-configurator').'</loc>', false)
            ->assertSee('<loc>'.url('/ru/door-configurator').'</loc>', false);
    }

    public function test_server_prices_color_and_separate_handle_lines_and_retries_are_idempotent(): void
    {
        $door = $this->door();
        $handle = $this->makeProduct(['slug' => 'z-1814-chorniy', 'price' => 1000, 'availability_status_id' => 2]);
        $color = Color::create(['id' => 8, 'slug' => 'black', 'hex' => '#000000', 'display_as_image' => false, 'name' => ['uk' => 'Чорний', 'ru' => 'Черный'], 'creator_id' => $this->author()->id]);
        $handle->colors()->attach($color->id, ['price' => 50]);
        $payload = $this->payload(['handle' => 'black', 'expected_total' => 6300, 'price' => 1, 'product_count' => 999, 'color_id' => 666]);
        $response = $this->postJson('/door-configurator/cart', $payload)->assertOk();
        $this->keepCookies($response);
        $this->postJson('/door-configurator/cart', $payload)->assertOk();
        $this->assertSame(2, CartProducts::count());
        $this->assertSame(2, (int) CartProducts::sum('count'));
        $line = CartProducts::where('product_id', $door->id)->first();
        $this->assertEquals(5000, $line->price);
        $this->assertEquals(250, $line->attributes_price);
        $this->assertSame('148', json_decode($line->attributes, true)['color_id']);
        $this->assertNull($line->bundle_key);
        $this->assertDatabaseCount('configurator_cart_requests', 1);
        $this->postJson('/door-configurator/cart', array_merge($payload, ['handle' => null]))->assertStatus(409);
    }

    public function test_bad_selection_and_outdated_quote_do_not_partially_fill_cart(): void
    {
        $door = $this->door();
        foreach ([['product' => 'not-allowed'], ['color' => 'anthracite'], ['handle' => 'not-allowed']] as $change) {
            $this->postJson('/door-configurator/cart', $this->payload($change))->assertUnprocessable();
        }
        $this->postJson('/door-configurator/cart', $this->payload(['expected_total' => 10]))->assertStatus(409);
        $door->update(['availability_status_id' => 4]);
        $this->postJson('/door-configurator/cart', $this->payload())->assertUnprocessable();
        $this->assertDatabaseCount('cart_products', 0);
        $this->assertDatabaseCount('configurator_cart_requests', 0);
    }

    public function test_readding_an_existing_selection_refreshes_its_catalog_price(): void
    {
        $door = $this->door();
        $response = $this->postJson('/door-configurator/cart', $this->payload())->assertOk();
        $this->keepCookies($response);
        $door->update(['price' => 6000]);
        $door->colors()->updateExistingPivot(148, ['price' => 300]);
        $this->postJson('/door-configurator/cart', $this->payload(['expected_total' => 6300]))->assertOk();
        $this->assertDatabaseCount('cart_products', 1);
        $line = CartProducts::first();
        $this->assertEquals(2, $line->count);
        $this->assertEquals(6000, $line->price);
        $this->assertEquals(300, $line->attributes_price);
    }

    public function test_missing_color_mapping_is_not_replaced_by_arbitrary_color(): void
    {
        $door = $this->door();
        $door->colors()->detach();
        $this->assertSame([], app(DoorConfiguratorService::class)->catalog()['products']);
    }

    public function test_colorless_mirror_and_russian_endpoint_use_real_catalog(): void
    {
        $this->seedCurrency();
        $this->makeProduct(['slug' => 'dveri-prihovanogo-montazhu-dzerkalo-sriblo', 'price' => 25000, 'availability_status_id' => 3]);
        $this->postJson('/ru/door-configurator/cart', $this->payload(['product' => 'mirror-silver', 'color' => 'silver', 'expected_total' => 25000]))->assertOk();
        $this->assertNull(CartProducts::first()->attributes);
        $this->assertEquals(25000, CartProducts::first()->price);
    }

    public function test_catalog_json_cannot_break_out_into_executable_markup(): void
    {
        $this->door(['name' => ['uk' => '</script><script>alert(1)</script>', 'ru' => 'Двери']]);
        $this->get('/door-configurator')->assertOk()->assertDontSee('</script><script>alert(1)</script>', false)->assertSee('\\u003C', false);
    }

    public function test_all_prepared_assets_exist_and_ui_translations_have_parity(): void
    {
        $presets = app(DoorConfiguratorService::class)->presets();
        foreach ($presets['products'] as $product) {
            foreach ($product['colors'] as $color) {
                $this->assertFileExists(public_path('assets/door-configurator/v1/'.$color['image']));
            }
        }
        $uk = require base_path('lang/uk/configurator.php');
        $ru = require base_path('lang/ru/configurator.php');
        $this->assertSame(array_keys($uk['ui']), array_keys($ru['ui']));
        $this->assertSame(count($uk['faq']), count($ru['faq']));
    }
}
