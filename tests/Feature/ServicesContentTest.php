<?php

namespace Tests\Feature;

use App\Models\Faqs;
use App\Models\Role;
use App\Models\ServicesConfig;
use App\Models\ServicesPageSections;
use App\Models\User;
use App\Services\ServicesPage\ServicesContentImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class ServicesContentTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_services_content_is_complete_bilingual_and_idempotent(): void
    {
        $services = $this->createServicePages();
        $importer = app(ServicesContentImporter::class);
        $path = database_path('content/services/2026_09_26_services_content.json');

        $first = $importer->importFile($path);
        $second = $importer->importFile($path);

        $this->assertSame(3, $first['services']);
        $this->assertSame([], $first['missing']);
        $this->assertFalse($second['page']);
        $this->assertSame(0, $second['services']);

        $targets = collect([ServicesConfig::CONTENT_PAGE_TYPE => ServicesConfig::query()->firstOrFail()])
            ->merge($services->keyBy(fn (ServicesPageSections $service): string => $service->editorialPageType()));

        foreach ($targets as $pageType => $target) {
            $target->refresh();
            $faqs = Faqs::query()->where('page_type', $pageType)->orderBy('id')->get();
            $this->assertCount(5, $faqs);

            foreach (['uk', 'ru'] as $locale) {
                $faqText = $faqs->map(fn (Faqs $faq): string => implode(' ', [
                    $faq->getTranslation('question', $locale),
                    $faq->getTranslation('answer', $locale),
                ]))->implode(' ');
                $visibleText = implode(' ', [
                    $target instanceof ServicesPageSections ? $target->getTranslation('description', $locale) : '',
                    $target->getTranslation('intro', $locale),
                    $target->getTranslation('content', $locale),
                    $faqText,
                ]);
                $wordCount = count(preg_split(
                    '/\s+/u',
                    trim(strip_tags($visibleText)),
                    -1,
                    PREG_SPLIT_NO_EMPTY,
                ));

                $this->assertGreaterThanOrEqual(400, $wordCount, "{$pageType} {$locale} is too short");
                $this->assertLessThanOrEqual(700, $wordCount, "{$pageType} {$locale} is too long");
                $this->assertLessThanOrEqual(65, mb_strlen($target->getTranslation('meta_title', $locale)));
                $this->assertGreaterThanOrEqual(130, mb_strlen($target->getTranslation('meta_description', $locale)));
                $this->assertLessThanOrEqual(165, mb_strlen($target->getTranslation('meta_description', $locale)));
            }
        }
    }

    public function test_services_pages_render_managed_copy_and_faq_schema_in_both_languages(): void
    {
        $services = $this->createServicePages();
        app(ServicesContentImporter::class)->importFile(
            database_path('content/services/2026_09_26_services_content.json'),
        );

        $this->get('/services')
            ->assertOk()
            ->assertSee('Послуги Bona Doors')
            ->assertSee('Від вибору дверей до готового результату')
            ->assertSee('Чи можна замовити лише одну послугу?')
            ->assertSee('"@type":"FAQPage"', false);

        $this->get('/ru/services')
            ->assertOk()
            ->assertSee('Услуги Bona Doors')
            ->assertSee('От выбора дверей до готового результата')
            ->assertSee('Можно ли заказать только одну услугу?')
            ->assertSee('"@type":"FAQPage"', false);

        app()->setLocale('uk');
        $consultation = $services->firstWhere('slug', 'konsultatsiia');
        $this->get('/services/'.$consultation->slug)
            ->assertOk()
            ->assertSee('Консультація з вибору дверей')
            ->assertSee('Консультація, після якої зрозуміло, що замовляти')
            ->assertSee('Що взяти із собою на консультацію?')
            ->assertSee('"@type":"Service"', false)
            ->assertSee('"@type":"FAQPage"', false);

        $this->get('/ru/services/'.$consultation->slug)
            ->assertOk()
            ->assertSee('Консультация по выбору дверей')
            ->assertSee('Консультация, после которой понятно, что заказывать')
            ->assertSee('Что взять с собой на консультацию?')
            ->assertSee('"@type":"FAQPage"', false);
    }

    public function test_admin_can_edit_services_page_and_individual_service_faqs(): void
    {
        $service = $this->createServicePages()->first();
        app(ServicesContentImporter::class)->importFile(
            database_path('content/services/2026_09_26_services_content.json'),
        );
        $service->refresh();

        $this->actingAs($this->admin())
            ->get(route('admin.services.edit.page'))
            ->assertOk()
            ->assertViewHas('faqs', fn (array $faqs): bool => data_get(
                $faqs,
                '0.question.uk',
            ) === 'Чи можна замовити лише одну послугу?')
            ->assertViewHas('sections', fn ($sections): bool => data_get(
                $sections->firstWhere('slug', 'konsultatsiia'),
                'faqs.0.question.uk',
            ) === 'Що взяти із собою на консультацію?');

        $payload = [
            'title' => ['uk' => 'Оновлені послуги', 'ru' => 'Обновленные услуги'],
            'intro' => ['uk' => 'Оновлений вступ.', 'ru' => 'Обновленное вступление.'],
            'content' => ['uk' => '<p>Оновлений текст послуг.</p>', 'ru' => '<p>Обновленный текст услуг.</p>'],
            'meta_title' => ['uk' => 'Послуги | Bona Doors', 'ru' => 'Услуги | Bona Doors'],
            'meta_description' => ['uk' => 'Оновлений опис послуг.', 'ru' => 'Обновленное описание услуг.'],
            'meta_keywords' => ['uk' => 'послуги', 'ru' => 'услуги'],
            'faqs_managed' => '1',
            'faqs' => [[
                'question' => ['uk' => 'Нове питання?', 'ru' => 'Новый вопрос?'],
                'answer' => ['uk' => 'Нова відповідь.', 'ru' => 'Новый ответ.'],
            ]],
            'sections' => [[
                'id' => $service->id,
                'slug' => $service->slug,
                'title' => $service->getTranslations('title'),
                'description' => $service->getTranslations('description'),
                'intro' => $service->getTranslations('intro'),
                'content' => [
                    'uk' => '<p>Оновлений текст консультації.</p>',
                    'ru' => '<p>Обновленный текст консультации.</p>',
                ],
                'button_text' => $service->getTranslations('button_text'),
                'button_url' => '#dialog-call-measurer',
                'meta_title' => $service->getTranslations('meta_title'),
                'meta_description' => $service->getTranslations('meta_description'),
                'meta_keywords' => $service->getTranslations('meta_keywords'),
                'meta_tags' => '',
                'faqs_managed' => '1',
                'faqs' => [[
                    'question' => ['uk' => 'Нове питання послуги?', 'ru' => 'Новый вопрос услуги?'],
                    'answer' => ['uk' => 'Нова відповідь послуги.', 'ru' => 'Новый ответ услуги.'],
                ]],
            ]],
        ];

        $this->actingAs($this->admin())
            ->post(route('admin.services.edit'), $payload)
            ->assertOk()
            ->assertJsonPath('data.success', true);

        $this->assertSame('Оновлені послуги', ServicesConfig::query()->firstOrFail()->getTranslation('title', 'uk'));
        $this->assertSame('<p>Оновлений текст консультації.</p>', $service->fresh()->getTranslation('content', 'uk'));
        $this->assertSame(1, Faqs::query()->where('page_type', ServicesConfig::CONTENT_PAGE_TYPE)->count());
        $this->assertSame(1, Faqs::query()->where('page_type', $service->editorialPageType())->count());
    }

    /** @return Collection<int, ServicesPageSections> */
    private function createServicePages()
    {
        ServicesConfig::query()->firstOrCreate([], [
            'title' => ['uk' => '', 'ru' => ''],
            'intro' => ['uk' => '', 'ru' => ''],
            'content' => ['uk' => '', 'ru' => ''],
            'meta_title' => ['uk' => '', 'ru' => ''],
            'meta_description' => ['uk' => '', 'ru' => ''],
            'meta_keywords' => ['uk' => '', 'ru' => ''],
        ]);

        return collect([
            ['konsultatsiia', 'consultation'],
            ['vyklyk-maistra', 'measurement'],
            ['montazh-dverei', 'installation'],
        ])->map(fn (array $service, int $index): ServicesPageSections => ServicesPageSections::query()->create([
            'slug' => $service[0],
            'title' => ['uk' => 'Послуга', 'ru' => 'Услуга'],
            'description' => ['uk' => '', 'ru' => ''],
            'intro' => ['uk' => '', 'ru' => ''],
            'content' => ['uk' => '', 'ru' => ''],
            'button_text' => ['uk' => 'Замовити', 'ru' => 'Заказать'],
            'button_url' => '#dialog-call-measurer',
            'section_image_path' => 'assets/images/services/'.$service[1].'.webp',
            'sort_order' => $index,
        ]));
    }

    private function admin(): User
    {
        DB::table('roles')->insertOrIgnore([
            'id' => Role::ADMIN_ROLE_ID,
            'role' => 'Admin',
            'role_slug' => 'admin',
        ]);

        $admin = $this->author();
        $admin->update(['role_id' => Role::ADMIN_ROLE_ID]);

        return $admin;
    }
}
