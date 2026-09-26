<?php

namespace Tests\Feature;

use App\DataClasses\ProductFieldTypeOptionsDataClass;
use App\Models\Faqs;
use App\Models\FilterGroup;
use App\Models\ProductField;
use App\Models\ProductFieldOption;
use App\Models\ProductType;
use App\Models\Role;
use App\Models\SeoText;
use App\Models\User;
use App\Services\CatalogMenu\CatalogMenuService;
use App\Services\FilterGroups\FilterGroupContentImporter;
use App\Services\Sitemap\SitemapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class FilterGroupContentTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_style_landing_content_is_complete_unique_and_idempotent(): void
    {
        [$productType, $field, $options] = $this->styleCatalogue();
        $importer = app(FilterGroupContentImporter::class);
        $path = database_path('content/filter-groups/2026_09_26_interior_door_styles.json');

        $first = $importer->importFile($path);
        $second = $importer->importFile($path);

        $this->assertSame(4, $first['groups']);
        $this->assertSame([], $first['missing']);
        $this->assertSame(0, $second['groups']);
        $this->assertSame(4, FilterGroup::query()->count());

        $slugs = [
            'dveri-modern' => 'modern',
            'klasychni-dveri' => 'klassyka',
            'dveri-neoklasyka' => 'neoklassyka',
            'dveri-minimalizm' => 'mynymalyzm',
        ];
        $descriptions = ['uk' => [], 'ru' => []];

        foreach ($slugs as $groupSlug => $optionSlug) {
            $group = FilterGroup::query()->where('slug', $groupSlug)->firstOrFail();

            $this->assertSame($productType->id, $group->product_type_id);
            $this->assertSame($field->id, data_get($group->filters, 'custom_fields.0.id'));
            $this->assertSame([$options[$optionSlug]->id], data_get($group->filters, 'custom_fields.0.value'));
            $this->assertSame(5, Faqs::query()->where('page_type', "filter-group:{$group->id}")->count());

            foreach (['uk', 'ru'] as $locale) {
                $content = (string) SeoText::query()
                    ->where(['page_type' => "filter-group:{$group->id}", 'language' => $locale])
                    ->value('content');
                $wordCount = count(preg_split('/\s+/u', trim(strip_tags($content)), -1, PREG_SPLIT_NO_EMPTY));

                $this->assertGreaterThanOrEqual(300, $wordCount, "{$groupSlug} {$locale} is too short");
                $this->assertLessThanOrEqual(500, $wordCount, "{$groupSlug} {$locale} is too long");
                $this->assertLessThanOrEqual(65, mb_strlen($group->getTranslation('meta_title', $locale)));
                $this->assertGreaterThanOrEqual(130, mb_strlen($group->getTranslation('meta_description', $locale)));
                $this->assertLessThanOrEqual(165, mb_strlen($group->getTranslation('meta_description', $locale)));
                $descriptions[$locale][] = $content;
            }
        }

        $this->assertCount(4, array_unique($descriptions['uk']));
        $this->assertCount(4, array_unique($descriptions['ru']));
    }

    public function test_style_landing_filters_products_and_renders_its_own_localized_content(): void
    {
        config()->set('constants.ROZSUVNI_DVERI_ID', -1);
        $this->seedCurrency();
        [$productType, $field, $options] = $this->styleCatalogue();
        app(FilterGroupContentImporter::class)->importFile(
            database_path('content/filter-groups/2026_09_26_interior_door_styles.json'),
        );

        SeoText::query()->create([
            'page_type' => $productType->slug,
            'language' => 'uk',
            'title' => 'Загальний текст каталогу',
            'content' => '<p>Цей текст не належить посадковій сторінці.</p>',
        ]);

        $modern = $this->makeProduct([
            'slug' => 'modern-test-door',
            'product_type_id' => $productType->id,
            'name' => ['uk' => 'Тестові двері модерн', 'ru' => 'Тестовые двери модерн'],
            'custom_fields' => [(string) $field->id => (string) $options['modern']->id],
        ]);
        $classic = $this->makeProduct([
            'slug' => 'classic-test-door',
            'product_type_id' => $productType->id,
            'name' => ['uk' => 'Тестові класичні двері', 'ru' => 'Тестовые классические двери'],
            'custom_fields' => [(string) $field->id => (string) $options['klassyka']->id],
        ]);
        $path = '/product-category/interior-doors/dveri-modern';

        $this->get($path)
            ->assertOk()
            ->assertSee('Міжкімнатні двері в стилі модерн')
            ->assertSee($modern->getTranslation('name', 'uk'))
            ->assertDontSee($classic->getTranslation('name', 'uk'))
            ->assertSee('Двері модерн підтримують чітку архітектуру кімнати')
            ->assertSee('Чим двері модерн відрізняються від мінімалістичних?')
            ->assertSee('"@type":"FAQPage"', false)
            ->assertDontSee('Цей текст не належить посадковій сторінці')
            ->assertSee('<link rel="canonical" href="'.url($path).'">', false);

        $this->get('/ru'.$path)
            ->assertOk()
            ->assertSee('Межкомнатные двери в стиле модерн')
            ->assertSee($modern->getTranslation('name', 'ru'))
            ->assertDontSee($classic->getTranslation('name', 'ru'))
            ->assertSee('Двери модерн поддерживают четкую архитектуру комнаты')
            ->assertSee('Чем двери модерн отличаются от минималистичных?')
            ->assertSee('<link rel="canonical" href="'.url('/ru'.$path).'">', false);

        $otherType = $this->productType(['slug' => 'entry-doors']);
        $this->get("/product-category/{$otherType->slug}/dveri-modern")->assertNotFound();
    }

    public function test_editorial_content_can_be_reviewed_and_changed_in_filter_group_admin(): void
    {
        [$productType, $field, $options] = $this->styleCatalogue();
        app(FilterGroupContentImporter::class)->importFile(
            database_path('content/filter-groups/2026_09_26_interior_door_styles.json'),
        );
        $group = FilterGroup::query()->where('slug', 'dveri-modern')->firstOrFail();
        $faq = Faqs::query()->where('page_type', "filter-group:{$group->id}")->firstOrFail();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.filter-groups.edit.page', $group))
            ->assertOk()
            ->assertViewHas('seoText', fn (array $content): bool => str_contains(
                data_get($content, 'uk', ''),
                'Двері модерн підтримують чітку архітектуру кімнати',
            ))
            ->assertViewHas('faqs', fn (array $faqs): bool => data_get(
                $faqs,
                '0.question.uk',
            ) === 'Чим двері модерн відрізняються від мінімалістичних?');

        $this->actingAs($admin)
            ->post(route('admin.filter-groups.edit', $group), [
                'product_type_id' => $productType->id,
                'slug' => $group->slug,
                'name' => $group->getTranslations('name'),
                'title_tag' => $group->getTranslations('title_tag'),
                'meta_title' => $group->getTranslations('meta_title'),
                'meta_description' => $group->getTranslations('meta_description'),
                'meta_keywords' => $group->getTranslations('meta_keywords'),
                'custom_field' => [[
                    'id' => $field->id,
                    'value' => (string) $options['modern']->id,
                ]],
                'seo_title' => ['uk' => 'Оновлений заголовок', 'ru' => 'Обновленный заголовок'],
                'seo_text' => ['uk' => '<p>Оновлений текст менеджера.</p>', 'ru' => '<p>Обновленный текст менеджера.</p>'],
                'faqs' => [[
                    'id' => $faq->id,
                    'question' => ['uk' => 'Оновлене питання?', 'ru' => 'Обновленный вопрос?'],
                    'answer' => ['uk' => 'Оновлена відповідь.', 'ru' => 'Обновленный ответ.'],
                ]],
            ])
            ->assertOk()
            ->assertJsonPath('data.success', true);

        $this->assertSame(
            '<p>Оновлений текст менеджера.</p>',
            SeoText::query()->where(['page_type' => "filter-group:{$group->id}", 'language' => 'uk'])->value('content'),
        );
        $this->assertSame(1, Faqs::query()->where('page_type', "filter-group:{$group->id}")->count());
        $this->assertSame('Оновлене питання?', $faq->fresh()->getTranslation('question', 'uk'));
    }

    public function test_style_landings_are_linked_from_navigation_and_sitemap(): void
    {
        [$productType] = $this->styleCatalogue();
        app(FilterGroupContentImporter::class)->importFile(
            database_path('content/filter-groups/2026_09_26_interior_door_styles.json'),
        );
        FilterGroup::query()->create([
            'user_id' => $this->author()->id,
            'product_type_id' => $productType->id,
            'slug' => 'empty-filter-group',
            'name' => ['uk' => 'Порожня група', 'ru' => 'Пустая группа'],
            'title_tag' => ['uk' => '', 'ru' => ''],
            'meta_title' => ['uk' => '', 'ru' => ''],
            'meta_description' => ['uk' => '', 'ru' => ''],
            'meta_keywords' => ['uk' => '', 'ru' => ''],
            'filters' => [],
        ]);

        $cards = app(CatalogMenuService::class)->getInteriorStyleCards($productType);
        $this->assertSame(
            '/product-category/interior-doors/dveri-modern',
            collect($cards)->firstWhere('key', 'modern')['url'],
        );

        app()->setLocale('ru');
        $localizedCards = app(CatalogMenuService::class)->getInteriorStyleCards($productType);
        $this->assertSame(
            '/ru/product-category/interior-doors/dveri-modern',
            collect($localizedCards)->firstWhere('key', 'modern')['url'],
        );
        app()->setLocale('uk');

        $sitemap = app(SitemapService::class)->buildSitemap()->render();
        $this->assertStringContainsString('/product-category/interior-doors/dveri-modern', $sitemap);
        $this->assertStringContainsString('/ru/product-category/interior-doors/dveri-modern', $sitemap);
        $this->assertStringNotContainsString('/product-category/interior-doors/empty-filter-group', $sitemap);
    }

    /** @return array{0: ProductType, 1: ProductField, 2: array<string, ProductFieldOption>} */
    private function styleCatalogue(): array
    {
        $productType = $this->productType([
            'slug' => 'interior-doors',
            'name' => ['uk' => 'Міжкімнатні двері', 'ru' => 'Межкомнатные двери'],
        ]);
        $field = ProductField::query()->create([
            'creator_id' => $this->author()->id,
            'field_name' => ['uk' => 'Стиль', 'ru' => 'Стиль'],
            'slug' => 'styl',
            'field_type_id' => ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION,
        ]);
        $productType->fields()->attach($field->id, [
            'show_as_filter' => true,
            'show_on_main_filters_list' => true,
            'filter_name' => json_encode(['uk' => 'Стиль', 'ru' => 'Стиль'], JSON_UNESCAPED_UNICODE),
            'filter_full_position_id' => 1,
        ]);

        $options = collect([
            'modern' => ['uk' => 'Модерн', 'ru' => 'Модерн'],
            'klassyka' => ['uk' => 'Класика', 'ru' => 'Классика'],
            'neoklassyka' => ['uk' => 'Неокласика', 'ru' => 'Неоклассика'],
            'mynymalyzm' => ['uk' => 'Мінімалізм', 'ru' => 'Минимализм'],
        ])->mapWithKeys(function (array $name, string $slug) use ($field): array {
            $option = ProductFieldOption::query()->create([
                'product_field_id' => $field->id,
                'name' => $name,
                'slug' => $slug,
            ]);

            return [$slug => $option];
        })->all();

        return [$productType, $field, $options];
    }

    private function admin(): User
    {
        DB::table('roles')->insertOrIgnore([
            'id' => Role::ADMIN_ROLE_ID,
            'role' => 'Admin',
            'role_slug' => 'admin',
        ]);

        $admin = User::factory()->create();
        $admin->update(['role_id' => Role::ADMIN_ROLE_ID]);

        return $admin;
    }
}
