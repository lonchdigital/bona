<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\ProductField;
use App\Models\ProductFieldOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class ManufacturerCatalogTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_manufacturer_url_filters_the_catalog_without_rendering_a_duplicate_brand_filter(): void
    {
        $this->seedCurrency();
        $productType = $this->productType([
            'slug' => 'interior-doors',
            'name' => ['uk' => 'Міжкімнатні двері', 'ru' => 'Межкомнатные двери'],
            'has_brand' => true,
        ]);
        $artporte = $this->brand('artporte', 'Artporte');
        $otherBrand = $this->brand('other-brand', 'Інший виробник');
        $manufacturerField = ProductField::query()->create([
            'creator_id' => $this->author()->id,
            'field_name' => ['uk' => 'Виробник', 'ru' => 'Производитель'],
            'slug' => 'proyzvodytel-dvere',
            'field_type_id' => 4,
        ]);
        $styleField = ProductField::query()->create([
            'creator_id' => $this->author()->id,
            'field_name' => ['uk' => 'Стиль', 'ru' => 'Стиль'],
            'slug' => 'style',
            'field_type_id' => 4,
        ]);
        $manufacturerOption = ProductFieldOption::query()->create([
            'product_field_id' => $manufacturerField->id,
            'name' => ['uk' => 'Artporte', 'ru' => 'Artporte'],
            'slug' => 'artporte',
        ]);
        $styleOption = ProductFieldOption::query()->create([
            'product_field_id' => $styleField->id,
            'name' => ['uk' => 'Мінімалізм', 'ru' => 'Минимализм'],
            'slug' => 'minimalism',
        ]);

        foreach ([
            $manufacturerField->id => ['uk' => 'Виробник', 'ru' => 'Производитель'],
            $styleField->id => ['uk' => 'Стиль', 'ru' => 'Стиль'],
        ] as $fieldId => $filterName) {
            $productType->fields()->attach($fieldId, [
                'show_as_filter' => true,
                'show_on_main_filters_list' => true,
                'filter_name' => json_encode($filterName, JSON_UNESCAPED_UNICODE),
                'filter_full_position_id' => 1,
            ]);
        }

        $matchingProduct = $this->makeProduct([
            'slug' => 'artporte-door',
            'product_type_id' => $productType->id,
            'brand_id' => $artporte->id,
            'name' => ['uk' => 'Двері Artporte', 'ru' => 'Двери Artporte'],
            'custom_fields' => [
                (string) $manufacturerField->id => (string) $manufacturerOption->id,
                (string) $styleField->id => (string) $styleOption->id,
            ],
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
            ->assertDontSee('filter-item--brands', false)
            ->assertDontSee('search_by_brand', false)
            ->assertDontSee('name="proyzvodytel-dvere"', false)
            ->assertSee('name="style"', false)
            ->assertSee('Мінімалізм')
            ->assertSee($matchingProduct->name)
            ->assertDontSee($otherProduct->name)
            ->assertDontSee('Mali'.'na', false);

        $loader = file_get_contents(resource_path('js/store/app.js'));
        $stylesheet = file_get_contents(resource_path('scss/storefront/_catalog.scss'));

        $this->assertStringContainsString("pageToLoad === 'store.catalog.manufacturer.page'", $loader);
        $this->assertStringContainsString('.archive-catalog-filter-left.active > .filter-content { display: flex; }', $stylesheet);
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

    public function test_catalog_page_two_has_its_own_canonical_and_previous_page_link(): void
    {
        config()->set('constants.ROZSUVNI_DVERI_ID', -1);
        $this->seedCurrency();
        $productType = $this->productType([
            'slug' => 'interior-doors',
            'name' => ['uk' => 'Міжкімнатні двері', 'ru' => 'Межкомнатные двери'],
        ]);

        foreach (range(1, 19) as $index) {
            $this->makeProduct([
                'slug' => "door-{$index}",
                'product_type_id' => $productType->id,
                'name' => ['uk' => "Двері {$index}", 'ru' => "Двери {$index}"],
            ]);
        }

        $catalogPath = route('store.catalog.page', ['productTypeSlug' => $productType->slug], false);
        $firstPageResponse = $this->get($catalogPath);

        $this->assertSame(1, preg_match(
            '~<a\s+[^>]*href="([^"]+)"[^>]*data-catalog-load-more~',
            $firstPageResponse->getContent(),
            $loadMoreMatch,
        ));
        parse_str((string) parse_url($loadMoreMatch[1], PHP_URL_QUERY), $loadMoreQuery);
        $this->assertSame(['page' => '2'], $loadMoreQuery);

        $response = $this->get($catalogPath.'?page=2');

        $response
            ->assertOk()
            ->assertSee('Сторінка 2');

        $this->assertMatchesRegularExpression(
            '~<link rel="canonical" href="https?://[^"/]+'.preg_quote($catalogPath, '~').'\?page=2">~',
            $response->getContent(),
        );
        $this->assertMatchesRegularExpression(
            '~<link rel="prev" href="https?://[^"/]+'.preg_quote($catalogPath, '~').'">~',
            $response->getContent(),
        );
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
