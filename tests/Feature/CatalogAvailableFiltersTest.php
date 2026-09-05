<?php

namespace Tests\Feature;

use App\DataClasses\ProductFieldTypeOptionsDataClass;
use App\DataClasses\ProductStatusDataClass;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\ProductField;
use App\Models\ProductFieldOption;
use App\Models\ProductType;
use App\Services\Brand\BrandService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class CatalogAvailableFiltersTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_product_type_catalog_only_shows_facets_backed_by_its_products(): void
    {
        $catalog = $this->makeCatalogFixture();

        $response = $this->get(route('store.catalog.page', [
            'productTypeSlug' => $catalog['productType']->slug,
        ]));

        $response->assertOk();

        $filterHtml = $this->filterHtml($response->getContent());

        $this->assertSame(['artporte', 'hidden-door'], $this->filterValues($filterHtml, 'manufacturer'));
        $this->assertSame(['minimalism', 'modern'], $this->filterValues($filterHtml, 'style'));
        $this->assertSame(['2', '3'], $this->filterValues($filterHtml, 'availability_status'));
        $this->assertSame(['ivory', 'graphite'], $this->filterValues($filterHtml, 'color'));
        $this->assertStringNotContainsString('Скандинавський без товарів', $filterHtml);
        $this->assertStringNotContainsString('Бренд без міжкімнатних дверей', $filterHtml);
    }

    public function test_category_catalog_only_shows_facets_backed_by_products_in_that_category(): void
    {
        $catalog = $this->makeCatalogFixture();

        $response = $this->get(route('store.catalog-category.page', [
            'productTypeSlug' => $catalog['productType']->slug,
            'categorySlug' => $catalog['category']->slug,
        ]));

        $response->assertOk();

        $filterHtml = $this->filterHtml($response->getContent());

        $this->assertSame(['artporte'], $this->filterValues($filterHtml, 'manufacturer'));
        $this->assertSame(['minimalism'], $this->filterValues($filterHtml, 'style'));
        $this->assertSame(['2'], $this->filterValues($filterHtml, 'availability_status'));
        $this->assertSame(['ivory'], $this->filterValues($filterHtml, 'color'));

        $categoryBrands = app(BrandService::class)
            ->getAvailableBrandsByProductType($catalog['productType'], $catalog['category']);

        $this->assertSame(['artporte-brand'], $categoryBrands->pluck('slug')->all());
    }

    private function makeCatalogFixture(): array
    {
        config()->set('constants.ROZSUVNI_DVERI_ID', -1);
        $this->seedCurrency();

        $productType = $this->productType([
            'slug' => 'interior-doors',
            'name' => ['uk' => 'Міжкімнатні двері', 'ru' => 'Межкомнатные двери'],
            'has_brand' => true,
            'has_color' => true,
        ]);
        $otherProductType = $this->productType([
            'slug' => 'wall-panels',
            'name' => ['uk' => 'Стінові панелі', 'ru' => 'Стеновые панели'],
            'has_brand' => true,
            'has_color' => true,
        ]);

        $category = $this->category($productType, 'classic-doors', 'Класичні двері');
        $otherCategory = $this->category($productType, 'modern-doors', 'Сучасні двері');

        [$manufacturerField, $manufacturerOptions] = $this->filterField($productType, 'manufacturer', 'Виробник', [
            'artporte' => 'Artporte з товаром',
            'hidden-door' => 'Hidden Door з товаром',
            'empty-brand' => 'Бренд без міжкімнатних дверей',
        ]);
        [$styleField, $styleOptions] = $this->filterField($productType, 'style', 'Стиль', [
            'minimalism' => 'Мінімалізм',
            'modern' => 'Модерн',
            'scandinavian' => 'Скандинавський без товарів',
        ]);

        $artporteBrand = $this->brand('artporte-brand', 'Artporte');
        $hiddenDoorBrand = $this->brand('hidden-door-brand', 'Hidden Door');
        $unusedBrand = $this->brand('unused-brand', 'Бренд без товарів');

        $ivory = $this->color('ivory', 'Айворі', '#e8dfce');
        $graphite = $this->color('graphite', 'Графіт', '#303238');
        $unusedColor = $this->color('unused-color', 'Колір без дверей', '#aa00aa');

        $firstProduct = $this->makeProduct([
            'slug' => 'classic-artporte-door',
            'product_type_id' => $productType->id,
            'brand_id' => $artporteBrand->id,
            'availability_status_id' => ProductStatusDataClass::PRODUCT_STATUS_STOCK,
            'custom_fields' => [
                (string) $manufacturerField->id => (string) $manufacturerOptions['artporte']->id,
                (string) $styleField->id => (string) $styleOptions['minimalism']->id,
            ],
        ]);
        $firstProduct->categories()->attach($category);
        $firstProduct->colors()->attach($ivory);

        $secondProduct = $this->makeProduct([
            'slug' => 'modern-hidden-door',
            'product_type_id' => $productType->id,
            'brand_id' => $hiddenDoorBrand->id,
            'availability_status_id' => ProductStatusDataClass::PRODUCT_STATUS_ORDER,
            'custom_fields' => [
                (string) $manufacturerField->id => (string) $manufacturerOptions['hidden-door']->id,
                (string) $styleField->id => [(string) $styleOptions['modern']->id],
            ],
        ]);
        $secondProduct->categories()->attach($otherCategory);
        $secondProduct->colors()->attach($graphite);

        $outsideProduct = $this->makeProduct([
            'slug' => 'outside-wall-panel',
            'product_type_id' => $otherProductType->id,
            'brand_id' => $unusedBrand->id,
            'availability_status_id' => ProductStatusDataClass::PRODUCT_STATUS_OUT_ASK_MANAGER,
            'custom_fields' => [
                (string) $manufacturerField->id => (string) $manufacturerOptions['empty-brand']->id,
                (string) $styleField->id => (string) $styleOptions['scandinavian']->id,
            ],
        ]);
        $outsideProduct->colors()->attach($unusedColor);

        return compact('productType', 'category');
    }

    private function filterField(ProductType $productType, string $slug, string $name, array $options): array
    {
        $field = ProductField::query()->create([
            'creator_id' => $this->author()->id,
            'field_name' => ['uk' => $name, 'ru' => $name],
            'slug' => $slug,
            'field_type_id' => ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION,
        ]);

        $productType->fields()->attach($field->id, [
            'show_as_filter' => true,
            'show_on_main_filters_list' => true,
            'filter_name' => json_encode(['uk' => $name, 'ru' => $name], JSON_UNESCAPED_UNICODE),
            'filter_full_position_id' => 1,
        ]);

        $createdOptions = collect($options)->mapWithKeys(function (string $optionName, string $optionSlug) use ($field) {
            $option = ProductFieldOption::query()->create([
                'product_field_id' => $field->id,
                'name' => ['uk' => $optionName, 'ru' => $optionName],
                'slug' => $optionSlug,
            ]);

            return [$optionSlug => $option];
        });

        return [$field, $createdOptions];
    }

    private function category(ProductType $productType, string $slug, string $name): Category
    {
        return Category::query()->create([
            'creator_id' => $this->author()->id,
            'product_type_id' => $productType->id,
            'name' => ['uk' => $name, 'ru' => $name],
            'slug' => $slug,
            'image_path' => 'test/category.webp',
        ]);
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

    private function color(string $slug, string $name, string $hex): Color
    {
        return Color::query()->create([
            'creator_id' => $this->author()->id,
            'name' => ['uk' => $name, 'ru' => $name],
            'slug' => $slug,
            'display_as_image' => false,
            'hex' => $hex,
        ]);
    }

    private function filterHtml(string $pageHtml): string
    {
        $document = new \DOMDocument;
        $previousErrors = libxml_use_internal_errors(true);
        $document->loadHTML($pageHtml);
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);

        $filter = (new \DOMXPath($document))->query('//*[@id="art-products-filter"]')->item(0);
        $this->assertInstanceOf(\DOMElement::class, $filter);

        return $document->saveHTML($filter);
    }

    private function filterValues(string $filterHtml, string $inputName): array
    {
        $document = new \DOMDocument;
        $previousErrors = libxml_use_internal_errors(true);
        $document->loadHTML($filterHtml);
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);

        $values = [];
        foreach ((new \DOMXPath($document))->query('//input[@name="'.$inputName.'"]') as $input) {
            $values[] = $input->getAttribute('value');
        }

        return $values;
    }
}
