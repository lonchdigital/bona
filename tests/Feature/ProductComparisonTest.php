<?php

namespace Tests\Feature;

use App\Models\ProductCharacteristics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class ProductComparisonTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_selected_products_render_in_order_with_real_characteristic_differences(): void
    {
        $first = $this->makeProduct([
            'slug' => 'compare-oak',
            'name' => ['uk' => 'Двері Дуб', 'ru' => 'Двери Дуб'],
            'price' => 7400,
            'availability_status_id' => 2,
        ]);
        $second = $this->makeProduct([
            'slug' => 'compare-enamel',
            'name' => ['uk' => 'Двері Емаль', 'ru' => 'Двери Эмаль'],
            'price' => 8200,
            'availability_status_id' => 3,
        ]);

        foreach ([
            [$first, 'Шпон дуба', '2000 мм'],
            [$second, 'Фарбована емаль', '2000 мм'],
        ] as [$product, $material, $height]) {
            ProductCharacteristics::create([
                'product_id' => $product->id,
                'name' => ['uk' => 'Матеріал', 'ru' => 'Материал'],
                'value' => ['uk' => $material, 'ru' => $material],
            ]);
            ProductCharacteristics::create([
                'product_id' => $product->id,
                'name' => ['uk' => 'Висота', 'ru' => 'Высота'],
                'value' => ['uk' => $height, 'ru' => $height],
            ]);
        }

        $response = $this->get(route('store.comparison.page', [
            'products' => 'compare-enamel,compare-oak',
        ]));

        $response
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertHeader('Cache-Control')
            ->assertSeeInOrder(['Двері Емаль', 'Двері Дуб'])
            ->assertSee('Матеріал')
            ->assertSee('Фарбована емаль')
            ->assertSee('Шпон дуба')
            ->assertSee('data-comparison-different="true"', false)
            ->assertSee('data-comparison-different="false"', false)
            ->assertSee('data-comparison-remove', false)
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    public function test_comparison_accepts_only_four_unique_valid_storefront_products(): void
    {
        $products = collect(range(1, 5))->map(fn (int $number) => $this->makeProduct([
            'slug' => 'compare-door-'.$number,
            'name' => [
                'uk' => 'Модель порівняння '.$number,
                'ru' => 'Модель сравнения '.$number,
            ],
        ]));

        $products[2]->update(['is_active' => false]);

        $response = $this->get(route('store.comparison.page', [
            'products' => 'compare-door-1,compare-door-1,../bad,compare-door-2,compare-door-3,compare-door-4,compare-door-5',
        ]));

        $response
            ->assertOk()
            ->assertSee('Модель порівняння 1')
            ->assertSee('Модель порівняння 2')
            ->assertSee('Модель порівняння 3')
            ->assertSee('Модель порівняння 4')
            ->assertDontSee('Модель порівняння 5');
    }

    public function test_empty_and_russian_comparison_states_are_localized(): void
    {
        $this->get(route('store.comparison.page'))
            ->assertOk()
            ->assertSee('Список порівняння порожній')
            ->assertSee('data-comparison-dock', false)
            ->assertSee('data-comparison-link', false);

        $product = $this->makeProduct([
            'slug' => 'russian-comparison-door',
            'name' => ['uk' => 'Українська модель', 'ru' => 'Русская модель'],
        ]);

        $this->get(route('localized.store.comparison.page', [
            'lang' => 'ru',
            'products' => $product->slug,
        ]))
            ->assertOk()
            ->assertSee('Сравнение моделей')
            ->assertSee('Русская модель')
            ->assertSee('/ru/compare', false);
    }

    public function test_comparison_javascript_covers_storage_limit_links_and_page_controls(): void
    {
        $source = file_get_contents(resource_path('js/store/common/product-comparison.js'));

        $this->assertStringContainsString("const STORAGE_KEY = 'bona-compared-products'", $source);
        $this->assertStringContainsString('DEFAULT_MAX_PRODUCTS = 4', $source);
        $this->assertStringContainsString("url.searchParams.set('products'", $source);
        $this->assertStringContainsString('[data-comparison-remove]', $source);
        $this->assertStringContainsString('[data-comparison-differences]', $source);
        $this->assertStringContainsString('window.location.assign(comparisonUrl())', $source);
    }
}
