<?php

namespace Tests\Feature;

use App\DataClasses\ProductStatusDataClass;
use App\Models\Color;
use App\Models\Currency;
use App\Services\Seo\ProductPageSchemaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class StructuredDataIntegrityTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_product_schema_uses_one_visible_color_and_valid_offer_values(): void
    {
        $this->seedCurrency();
        Currency::query()->where('is_base', true)->update(['code' => 'грн.']);

        $product = $this->makeProduct([
            'price' => 6240,
            'availability_status_id' => ProductStatusDataClass::PRODUCT_STATUS_STOCK,
        ]);
        $product->colors()->attach([
            $this->color('white-pp', 'Білий пп')->id,
            $this->color('ivory', 'Айворі')->id,
            $this->color('concrete-beige', 'Бетон бежевий')->id,
        ]);
        $product->load(['colors', 'productType']);

        $schema = app(ProductPageSchemaService::class)->build(
            product: $product,
            currency: Currency::query()->where('is_base', true)->firstOrFail(),
            url: 'https://bona-doors.com.ua/product/test-door',
            webPageId: 'https://bona-doors.com.ua/product/test-door#webpage',
        );

        $this->assertNotNull($schema);
        $this->assertSame('Білий пп', $schema['color']);
        $this->assertSame('6240.00', $schema['offers']['price']);
        $this->assertSame('UAH', $schema['offers']['priceCurrency']);
        $this->assertSame('https://schema.org/InStock', $schema['offers']['availability']);
    }

    public function test_product_schema_is_not_published_without_an_offer_or_real_reviews(): void
    {
        $this->seedCurrency();
        $product = $this->makeProduct([
            'price' => 0,
            'availability_status_id' => ProductStatusDataClass::PRODUCT_STATUS_OUT_ASK_MANAGER,
        ]);
        $product->load(['colors', 'productType']);

        $schema = app(ProductPageSchemaService::class)->build(
            product: $product,
            currency: Currency::query()->where('is_base', true)->firstOrFail(),
            url: 'https://bona-doors.com.ua/product/price-on-request',
            webPageId: 'https://bona-doors.com.ua/product/price-on-request#webpage',
        );

        $this->assertNull($schema);
    }

    public function test_zero_price_product_pages_do_not_render_an_invalid_product_entity(): void
    {
        $this->seedCurrency();
        $product = $this->makeProduct([
            'slug' => 'price-on-request',
            'price' => 0,
            'availability_status_id' => ProductStatusDataClass::PRODUCT_STATUS_OUT_ASK_MANAGER,
        ]);

        $this->get(route('store.product.page', ['productSlug' => $product->slug]))
            ->assertOk()
            ->assertDontSee('"@type":"Product"', false)
            ->assertSee('"@type":"WebPage"', false)
            ->assertSee('"@type":"BreadcrumbList"', false);

        config()->set('constants.ROZSUVNI_DVERI_ID', $product->product_type_id);

        $this->get(route('store.product.page', ['productSlug' => $product->slug]))
            ->assertOk()
            ->assertDontSee('"@type":"Product"', false)
            ->assertSee('"@type":"WebPage"', false)
            ->assertSee('"@type":"BreadcrumbList"', false);
    }

    public function test_shared_breadcrumb_markup_has_required_names_and_positions(): void
    {
        $html = view('pages.store.partials.page_header', [
            'links' => [
                '/product-category/interior-doors' => 'Міжкімнатні двері',
                'own' => 'Тестові двері',
            ],
        ])->render();

        $document = new \DOMDocument;
        $previousErrors = libxml_use_internal_errors(true);
        $document->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);
        $xpath = new \DOMXPath($document);
        $items = $xpath->query('//*[@itemprop="itemListElement"]');

        $this->assertCount(3, $items);
        foreach ($items as $index => $item) {
            $this->assertSame(1, $xpath->query('.//*[@itemprop="name"]', $item)->length);
            $position = $xpath->query('.//*[@itemprop="position"]', $item)->item(0);
            $this->assertInstanceOf(\DOMElement::class, $position);
            $this->assertSame((string) ($index + 1), $position->getAttribute('content'));
        }

        foreach ([0, 1] as $index) {
            $link = $xpath->query('.//*[@itemprop="item"]', $items->item($index))->item(0);
            $this->assertInstanceOf(\DOMElement::class, $link);
            $this->assertStringStartsWith('http', $link->getAttribute('href'));
        }
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
