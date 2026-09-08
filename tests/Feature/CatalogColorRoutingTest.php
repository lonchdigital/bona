<?php

namespace Tests\Feature;

use App\Models\Color;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class CatalogColorRoutingTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_legacy_product_type_color_page_redirects_to_the_current_filtered_catalog(): void
    {
        $productType = $this->productType([
            'slug' => 'interior-doors',
            'name' => ['uk' => 'Міжкімнатні двері', 'ru' => 'Межкомнатные двери'],
            'has_color' => true,
        ]);
        $color = $this->color('white', 'Білий');

        $legacyUrl = route('store.product-type-by-color.page', [
            'productTypeSlug' => $productType->slug,
            'color' => $color->id,
        ], false);
        $targetUrl = route('store.catalog.filter.page', [
            'productTypeSlug' => $productType->slug,
            'catalogFiltersString' => 'color='.$color->slug,
        ], false);

        $this->get($legacyUrl.'?page=2&utm_source=legacy-menu')
            ->assertStatus(301)
            ->assertRedirect($targetUrl.'?page=2&utm_source=legacy-menu');
    }

    public function test_legacy_global_color_page_redirects_to_the_all_products_filter(): void
    {
        $color = $this->color('white', 'Білий');

        $this->get(route('store.products-by-color.page', ['color' => $color->id], false))
            ->assertStatus(301)
            ->assertRedirect(route('store.all-products.filter.page', [
                'catalogFiltersString' => 'color='.$color->slug,
            ], false));
    }

    public function test_legacy_color_redirect_keeps_the_active_locale(): void
    {
        $productType = $this->productType([
            'slug' => 'interior-doors',
            'name' => ['uk' => 'Міжкімнатні двері', 'ru' => 'Межкомнатные двери'],
        ]);
        $color = $this->color('white', 'Білий');

        $this->get(route('localized.store.product-type-by-color.page', [
            'lang' => 'ru',
            'productTypeSlug' => $productType->slug,
            'color' => $color->id,
        ], false))
            ->assertStatus(301)
            ->assertRedirect(route('localized.store.catalog.filter.page', [
                'lang' => 'ru',
                'productTypeSlug' => $productType->slug,
                'catalogFiltersString' => 'color='.$color->slug,
            ], false));
    }

    public function test_single_color_filter_uses_the_redesigned_catalog_and_its_own_canonical(): void
    {
        config()->set('constants.ROZSUVNI_DVERI_ID', -1);
        $this->seedCurrency();
        $productType = $this->productType([
            'slug' => 'interior-doors',
            'name' => ['uk' => 'Міжкімнатні двері', 'ru' => 'Межкомнатные двери'],
            'has_color' => true,
        ]);
        $white = $this->color('white', 'Білий');
        $black = $this->color('black', 'Чорний');
        $whiteDoor = $this->makeProduct([
            'slug' => 'white-door',
            'product_type_id' => $productType->id,
            'name' => ['uk' => 'Білі двері Тест', 'ru' => 'Белые двери Тест'],
        ]);
        $blackDoor = $this->makeProduct([
            'slug' => 'black-door',
            'product_type_id' => $productType->id,
            'name' => ['uk' => 'Чорні двері Тест', 'ru' => 'Черные двери Тест'],
        ]);
        $whiteDoor->colors()->attach($white);
        $blackDoor->colors()->attach($black);

        $targetPath = route('store.catalog.filter.page', [
            'productTypeSlug' => $productType->slug,
            'catalogFiltersString' => 'color='.$white->slug,
        ], false);
        $response = $this->get($targetPath);

        $response
            ->assertOk()
            ->assertSee('class="bona-catalog__grid', false)
            ->assertSee('Білі двері Тест')
            ->assertDontSee('Чорні двері Тест')
            ->assertSee('value="white"', false)
            ->assertSee('<link rel="canonical" href="'.url($targetPath).'">', false);

        $allProductsTarget = route('store.all-products.filter.page', [
            'catalogFiltersString' => 'color='.$white->slug,
        ], false);

        $this->get($allProductsTarget)
            ->assertOk()
            ->assertSee('Білі двері Тест')
            ->assertDontSee('Чорні двері Тест')
            ->assertSee('<link rel="canonical" href="'.url($allProductsTarget).'">', false);
    }

    public function test_unknown_legacy_color_returns_not_found_instead_of_redirecting_to_an_unfiltered_page(): void
    {
        $productType = $this->productType(['slug' => 'interior-doors']);

        $this->get('/product-category/'.$productType->slug.'/color/999999')
            ->assertNotFound();
    }

    private function color(string $slug, string $name): Color
    {
        return Color::query()->create([
            'creator_id' => $this->author()->id,
            'name' => ['uk' => $name, 'ru' => $name],
            'slug' => $slug,
            'display_as_image' => false,
            'hex' => '#ffffff',
        ]);
    }
}
