<?php

namespace Tests\Feature;

use App\Models\Color;
use App\Models\Faqs;
use App\Models\FilterGroup;
use App\Models\ProductType;
use App\Models\SeoText;
use App\Services\Catalog\CatalogColorUrlService;
use App\Services\FilterGroups\FilterGroupContentImporter;
use App\Services\Sitemap\SitemapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class FilterGroupColorContentTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_color_landing_content_is_complete_unique_and_idempotent(): void
    {
        [$productType, $colors] = $this->colorCatalogue();
        $importer = app(FilterGroupContentImporter::class);
        $path = database_path('content/filter-groups/2026_09_26_interior_door_colors.json');

        $first = $importer->importFile($path);
        $second = $importer->importFile($path);

        $this->assertSame(4, $first['groups']);
        $this->assertSame([], $first['missing']);
        $this->assertSame(0, $second['groups']);
        $this->assertSame(4, FilterGroup::query()->count());
        $this->assertDatabaseMissing('filter_groups', ['slug' => 'bili-mizhkimnatni-dveri']);

        $slugs = [
            'siri-mizhkimnatni-dveri' => 'siry',
            'dveri-antracyt' => 'antracyt',
            'dveri-ayvori' => 'avori',
            'dveri-beton-siry' => 'beton-siry',
        ];
        $descriptions = ['uk' => [], 'ru' => []];

        foreach ($slugs as $groupSlug => $colorSlug) {
            $group = FilterGroup::query()->where('slug', $groupSlug)->firstOrFail();

            $this->assertSame($productType->id, $group->product_type_id);
            $this->assertSame([$colors[$colorSlug]->id], data_get($group->filters, 'color_ids'));
            $this->assertSame(0, Faqs::query()->where('page_type', $group->editorialPageType())->count());

            foreach (['uk', 'ru'] as $locale) {
                $content = (string) SeoText::query()
                    ->where(['page_type' => $group->editorialPageType(), 'language' => $locale])
                    ->value('content');
                $wordCount = count(preg_split('/\s+/u', trim(strip_tags($content)), -1, PREG_SPLIT_NO_EMPTY));

                $this->assertGreaterThanOrEqual(150, $wordCount, "{$groupSlug} {$locale} is too short");
                $this->assertLessThanOrEqual(300, $wordCount, "{$groupSlug} {$locale} is too long");
                $this->assertLessThanOrEqual(65, mb_strlen($group->getTranslation('meta_title', $locale)));
                $this->assertGreaterThanOrEqual(130, mb_strlen($group->getTranslation('meta_description', $locale)));
                $this->assertLessThanOrEqual(165, mb_strlen($group->getTranslation('meta_description', $locale)));
                $descriptions[$locale][] = $content;
            }
        }

        $this->assertCount(4, array_unique($descriptions['uk']));
        $this->assertCount(4, array_unique($descriptions['ru']));
    }

    public function test_color_landing_filters_products_and_old_filter_canonicalizes_to_it(): void
    {
        config()->set('constants.ROZSUVNI_DVERI_ID', -1);
        $this->seedCurrency();
        [$productType, $colors] = $this->colorCatalogue();
        app(FilterGroupContentImporter::class)->importFile(
            database_path('content/filter-groups/2026_09_26_interior_door_colors.json'),
        );

        $greyDoor = $this->makeProduct([
            'slug' => 'grey-test-door',
            'product_type_id' => $productType->id,
            'name' => ['uk' => 'Сірі тестові двері', 'ru' => 'Серые тестовые двери'],
        ]);
        $greyDoor->colors()->attach($colors['siry']->id);
        $ivoryDoor = $this->makeProduct([
            'slug' => 'ivory-test-door',
            'product_type_id' => $productType->id,
            'name' => ['uk' => 'Тестові двері айворі', 'ru' => 'Тестовые двери айвори'],
        ]);
        $ivoryDoor->colors()->attach($colors['avori']->id);

        $landingPath = '/product-category/interior-doors/siri-mizhkimnatni-dveri';
        $this->get($landingPath)
            ->assertOk()
            ->assertSee('Сірі міжкімнатні двері')
            ->assertSee($greyDoor->getTranslation('name', 'uk'))
            ->assertDontSee($ivoryDoor->getTranslation('name', 'uk'))
            ->assertSee('Сірі міжкімнатні двері створюють спокійний перехід')
            ->assertSee('<link rel="canonical" href="'.url($landingPath).'">', false)
            ->assertDontSee('"@type":"FAQPage"', false);

        $this->get('/ru'.$landingPath)
            ->assertOk()
            ->assertSee('Серые межкомнатные двери')
            ->assertSee($greyDoor->getTranslation('name', 'ru'))
            ->assertDontSee($ivoryDoor->getTranslation('name', 'ru'))
            ->assertSee('Серые межкомнатные двери создают спокойный переход')
            ->assertSee('<link rel="canonical" href="'.url('/ru'.$landingPath).'">', false);

        app()->setLocale('uk');
        $oldFilterPath = '/product-category/interior-doors/filter/color=siry';
        $this->get($oldFilterPath)
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.url($landingPath).'">', false);

        $urls = app(CatalogColorUrlService::class);
        $this->assertSame($landingPath, $urls->productTypeFilterUrl($productType, $colors['siry']));
        $this->assertSame('/ru'.$landingPath, $urls->productTypeFilterUrl($productType, $colors['siry'], 'ru'));

        $legacyPath = route('store.product-type-by-color.page', [
            'productTypeSlug' => $productType->slug,
            'color' => $colors['siry']->id,
        ], false);
        $this->get($legacyPath)->assertStatus(301)->assertRedirect($landingPath);

        $white = $this->color('bily', ['uk' => 'Білий', 'ru' => 'Белый']);
        $this->assertSame(
            '/product-category/interior-doors/filter/color=bily',
            $urls->productTypeFilterUrl($productType, $white),
        );
    }

    public function test_filled_color_landings_are_in_the_sitemap(): void
    {
        $this->colorCatalogue();
        app(FilterGroupContentImporter::class)->importFile(
            database_path('content/filter-groups/2026_09_26_interior_door_colors.json'),
        );

        $sitemap = app(SitemapService::class)->buildSitemap()->render();
        foreach (['siri-mizhkimnatni-dveri', 'dveri-antracyt', 'dveri-ayvori', 'dveri-beton-siry'] as $slug) {
            $this->assertStringContainsString("/product-category/interior-doors/{$slug}", $sitemap);
            $this->assertStringContainsString("/ru/product-category/interior-doors/{$slug}", $sitemap);
        }
    }

    /** @return array{0: ProductType, 1: array<string, Color>} */
    private function colorCatalogue(): array
    {
        $productType = $this->productType([
            'slug' => 'interior-doors',
            'name' => ['uk' => 'Міжкімнатні двері', 'ru' => 'Межкомнатные двери'],
            'has_color' => true,
        ]);
        $colors = collect([
            'siry' => ['uk' => 'Сірий', 'ru' => 'Серый'],
            'antracyt' => ['uk' => 'Антрацит', 'ru' => 'Антрацит'],
            'avori' => ['uk' => 'Айворі', 'ru' => 'Айвори'],
            'beton-siry' => ['uk' => 'Бетон сірий', 'ru' => 'Бетон серый'],
        ])->mapWithKeys(fn (array $name, string $slug): array => [
            $slug => $this->color($slug, $name),
        ])->all();

        return [$productType, $colors];
    }

    /** @param array{uk: string, ru: string} $name */
    private function color(string $slug, array $name): Color
    {
        return Color::query()->create([
            'creator_id' => $this->author()->id,
            'name' => $name,
            'slug' => $slug,
            'display_as_image' => false,
            'hex' => '#777777',
        ]);
    }
}
