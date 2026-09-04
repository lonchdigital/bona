<?php

namespace Tests\Feature;

use App\Models\AboutUsConfig;
use App\Models\Brand;
use App\Models\ServicesConfig;
use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class ContentPagesRedesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_editorial_pages_render_the_new_layout_without_seed_data(): void
    {
        foreach (['store.about-us', 'store.delivery-info', 'store.services', 'store.works.page'] as $routeName) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertSee('bona-content-page', false)
                ->assertSee('bona-content-breadcrumbs', false)
                ->assertSee('bona-site-header--solid', false)
                ->assertDontSee('art-common-page-section', false)
                ->assertDontSee('art-blog-archive-wrapper', false);
        }
    }

    public function test_editorial_pages_keep_the_same_bilingual_structure(): void
    {
        $this->get(route('localized.store.services', ['lang' => 'ru']))
            ->assertOk()
            ->assertSee('Сервис Bona Doors')
            ->assertSee('Наши услуги')
            ->assertSee('bona-services-list', false);

        $this->get(route('localized.store.works.page', ['lang' => 'ru']))
            ->assertOk()
            ->assertSee('Реализованные проекты')
            ->assertSee('bona-works-list', false);
    }

    public function test_services_page_legacy_description_is_cleaned_without_overwriting_admin_copy(): void
    {
        $config = ServicesConfig::create([
            'meta_description' => [
                'uk' => 'Замовити послуги компанії Bona-Doors в Одесі ✅ Гарантія якості ✅ Гарантія якості',
                'ru' => 'Заказать услуги компании Bona-Doors в Одессе ✅ Гарантия качества',
            ],
        ]);

        $migration = require database_path('migrations/2026_09_05_230000_clean_services_page_description.php');
        $migration->up();

        $descriptions = $config->fresh()->getTranslations('meta_description');

        $this->assertSame(
            'Замовити послуги компанії Bona Doors в Одесі. Гарантія якості.',
            $descriptions['uk'],
        );
        $this->assertSame(
            'Заказать услуги компании Bona Doors в Одессе. Гарантия качества.',
            $descriptions['ru'],
        );

        $this->get(route('store.services'))
            ->assertOk()
            ->assertSee('Замовити послуги компанії Bona Doors в Одесі. Гарантія якості.')
            ->assertDontSee('✅');

        $config->update([
            'meta_description' => [
                'uk' => 'Текст, відредагований в адмінці.',
                'ru' => 'Текст, отредактированный в админке.',
            ],
        ]);
        $migration->up();

        $this->assertSame(
            'Текст, відредагований в адмінці.',
            $config->fresh()->getTranslation('meta_description', 'uk'),
        );
    }

    public function test_about_page_section_headings_are_translatable(): void
    {
        $config = new AboutUsConfig;

        foreach ([
            'facts_title',
            'history_title',
            'history_text',
            'steps_title',
            'team_title',
            'cta_title',
            'cta_text',
            'cta_button_text',
        ] as $field) {
            $this->assertTrue($config->isTranslatableAttribute($field), $field.' must be translated');
        }
    }

    public function test_projects_use_accessible_editorial_pagination(): void
    {
        $paginator = new LengthAwarePaginator(
            items: collect(range(1, 9)),
            total: 30,
            perPage: 9,
            currentPage: 2,
            options: ['path' => '/nashi-roboty'],
        );

        $html = $paginator->links('pagination.editorial')->render();

        $this->assertStringContainsString('bona-page-pagination', $html);
        $this->assertStringContainsString('aria-current="page"', $html);
        $this->assertStringContainsString('rel="prev"', $html);
        $this->assertStringContainsString('rel="next"', $html);
        $this->assertStringNotContainsString('href="#"', $html);
    }

    public function test_project_cards_and_details_render_real_managed_content(): void
    {
        $user = User::factory()->create();
        $work = Work::create([
            'creator_id' => $user->id,
            'name' => ['uk' => 'Квартира біля моря', 'ru' => 'Квартира у моря'],
            'slug' => 'kvartyra-bilia-moria',
            'image_path' => 'work-images/project.jpg',
            'intro' => ['uk' => 'Підібрали двері для світлого інтерʼєру.', 'ru' => 'Подобрали двери для светлого интерьера.'],
            'description' => ['uk' => '<p>Встановили приховані двері.</p>', 'ru' => '<p>Установили скрытые двери.</p>'],
            'location' => 'Одеса',
            'doors_count' => 4,
            'duration' => '14 днів',
            'is_published' => true,
        ]);

        $this->get(route('store.works.page'))
            ->assertOk()
            ->assertSee('Квартира біля моря')
            ->assertSee('bona-project-card', false)
            ->assertSee('Переглянути проєкт');

        $this->get(route('store.work.page', ['workSlug' => $work->slug]))
            ->assertOk()
            ->assertSee('Квартира біля моря')
            ->assertSee('bona-work-detail-page', false)
            ->assertSee('Встановили приховані двері.', false)
            ->assertDontSee('art-work-page', false);
    }

    public function test_about_page_partner_section_accepts_direct_brand_models(): void
    {
        $user = User::factory()->create();
        $brand = Brand::create([
            'creator_id' => $user->id,
            'name' => ['uk' => 'ArtPorte', 'ru' => 'ArtPorte'],
            'slug' => 'artporte',
            'description' => ['uk' => 'Двері ArtPorte', 'ru' => 'Двери ArtPorte'],
            'logo_image_path' => 'brand-images/artporte.webp',
        ]);

        $html = view('components.store.home-partners', [
            'brands' => collect([$brand]),
            'section' => ['kicker' => 'Фабрики', 'title' => 'Партнери'],
        ])->render();

        $this->assertStringContainsString('bona-partner-card', $html);
        $this->assertStringContainsString('ArtPorte', $html);
    }

    public function test_redesigned_templates_do_not_restore_legacy_page_shells_or_inline_styles(): void
    {
        foreach ([
            'views/pages/store/about-us-page.blade.php',
            'views/pages/store/delivery-page.blade.php',
            'views/pages/store/services.blade.php',
            'views/pages/works/main.blade.php',
            'views/pages/works/detail.blade.php',
        ] as $template) {
            $source = file_get_contents(resource_path($template));

            $this->assertStringContainsString('bona-content-page', $source);
            $this->assertStringNotContainsString('pages.store.partials.page_header', $source);
            $this->assertStringNotContainsString('art-common-page-section', $source);
            $this->assertStringNotContainsString('@push(\'head\')\n    <style>', $source);
        }
    }
}
