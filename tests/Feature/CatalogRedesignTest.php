<?php

namespace Tests\Feature;

use App\Http\Requests\Store\Catalog\CatalogFilterRequest;
use App\Models\Brand;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductGalleries;
use App\Models\ProductType;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\Route;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class CatalogRedesignTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

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
        $this->assertStringContainsString('bona-product-card__actions', $html);
        $this->assertStringContainsString('data-product-compare', $html);
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
        $this->assertStringNotContainsString('filter-item--brands', $filters);
        $this->assertStringNotContainsString('search_by_brand', $filters);
        $this->assertStringContainsString('id="art-filter-display"', $toolbar);
        $this->assertStringContainsString('sort-by-option', $toolbar);
        $this->assertStringContainsString('$loop->iteration % 9 === 0', $content);
        $this->assertStringNotContainsString('catalog-count-menu', $toolbar);
        $this->assertStringNotContainsString('per_page', $toolbar);
    }

    public function test_catalog_always_renders_eighteen_products_and_two_consultation_cards(): void
    {
        config()->set('constants.ROZSUVNI_DVERI_ID', -1);
        $this->seedCurrency();
        $productType = $this->productType([
            'slug' => 'interior-doors',
            'name' => ['uk' => 'Міжкімнатні двері', 'ru' => 'Межкомнатные двери'],
        ]);

        foreach (range(1, 25) as $index) {
            $this->makeProduct([
                'slug' => "catalog-door-{$index}",
                'product_type_id' => $productType->id,
                'name' => ['uk' => "Двері {$index}", 'ru' => "Двери {$index}"],
            ]);
        }

        $response = $this->get(route('store.catalog.filter.page', [
            'productTypeSlug' => $productType->slug,
            'catalogFiltersString' => 'per_page=48',
        ], false));

        $response->assertOk();

        $document = new \DOMDocument;
        $previousErrors = libxml_use_internal_errors(true);
        $document->loadHTML($response->getContent());
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);

        $grid = (new \DOMXPath($document))->query('//*[@data-catalog-grid]')->item(0);
        $this->assertInstanceOf(\DOMElement::class, $grid);

        $cards = [];
        foreach ($grid->childNodes as $node) {
            if ($node instanceof \DOMElement) {
                $cards[] = $node;
            }
        }

        $productCards = array_filter($cards, fn (\DOMElement $card) => $card->hasAttribute('data-product-card'));
        $consultationCards = array_filter($cards, fn (\DOMElement $card) => str_contains($card->getAttribute('class'), 'bona-catalog__consultant'));

        $this->assertCount(18, $productCards);
        $this->assertCount(2, $consultationCards);
        $this->assertCount(20, $cards);
        $this->assertStringContainsString('bona-catalog__consultant', $cards[9]->getAttribute('class'));
        $this->assertStringContainsString('bona-catalog__consultant', $cards[19]->getAttribute('class'));
        $this->assertStringNotContainsString('catalog-count-menu', $response->getContent());
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

    public function test_catalog_pagination_uses_real_compact_page_urls_and_progressive_load_more(): void
    {
        $paginator = new LengthAwarePaginator(
            range(1, 18),
            216,
            18,
            6,
            ['path' => 'https://bona.test/product-category/interior-doors/filter/color=white'],
        );

        $html = view('pagination.store', ['paginator' => $paginator])->render();

        $this->assertStringContainsString('data-catalog-load-more', $html);
        $this->assertStringContainsString('href="https://bona.test/product-category/interior-doors/filter/color=white?page=7"', $html);
        $this->assertStringContainsString('aria-current="page"', $html);
        $this->assertStringContainsString('>…</span>', $html);
        $this->assertStringNotContainsString('href="#', $html);
        $this->assertStringNotContainsString('>2</a>', $html);
    }

    public function test_query_page_overrides_the_legacy_page_filter(): void
    {
        $request = CatalogFilterRequest::create(
            '/product-category/interior-doors/filter/color=white;page=7;per_page=48',
            'GET',
            ['page' => 2],
        );
        $route = new Route(
            ['GET'],
            'product-category/interior-doors/filter/{catalogFiltersString?}',
            fn () => null,
        );
        $route->bind($request);
        $request->setRouteResolver(fn () => $route);
        $request->setContainer($this->app);
        $request->setRedirector($this->app->make(Redirector::class));
        $request->validateResolved();

        $filters = $request->toDTO()->filters;

        $this->assertSame('white', $filters['color']);
        $this->assertSame(2, $filters['page']);
        $this->assertArrayNotHasKey('per_page', $filters);
    }

    public function test_price_range_auto_applies_without_the_removed_page_size_controls(): void
    {
        foreach ([
            resource_path('js/store/pages/store.catalog.page/filter-submit.js'),
            resource_path('js/store/pages/store.all-products.page/filter-submit.js'),
        ] as $scriptPath) {
            $script = file_get_contents($scriptPath);

            $this->assertStringContainsString('PriceSlider.$on(\'stop\'', $script);
            $this->assertStringContainsString("on('keyup', '[role=\"slider\"]'", $script);
            $this->assertStringContainsString('schedulePriceSubmit()', $script);
            $this->assertStringNotContainsString('show-24-items-per-page', $script);
            $this->assertStringNotContainsString("filterAdd('per_page'", $script);
        }
    }
}
