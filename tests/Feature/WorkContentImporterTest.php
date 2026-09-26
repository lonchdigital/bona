<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Work;
use App\Services\Work\DTO\EditWorkDTO;
use App\Services\Work\WorkContentImporter;
use App\Services\Work\WorkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class WorkContentImporterTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    private string $contentFile = '2026_09_27_verified_projects.json';

    public function test_verified_projects_are_bilingual_admin_managed_and_idempotent(): void
    {
        Storage::fake(config('app.images_disk_default'));
        $this->author();

        $importer = app(WorkContentImporter::class);
        $path = database_path('content/works/'.$this->contentFile);
        $first = $importer->importFile($path);
        $second = $importer->importFile($path);

        $this->assertSame(['works' => 3, 'created' => 3, 'restored_images' => 0], $first);
        $this->assertSame(['works' => 0, 'created' => 0, 'restored_images' => 0], $second);
        $this->assertSame(3, Work::query()->published()->count());

        foreach (Work::query()->orderBy('sort_order')->get() as $work) {
            Storage::disk(config('app.images_disk_default'))->assertExists($work->image_path);

            foreach (['uk', 'ru'] as $locale) {
                $this->assertNotSame('', trim($work->getTranslation('name', $locale)));
                $this->assertNotSame('', trim(strip_tags($work->getTranslation('intro', $locale))));
                $description = $work->getTranslation('description', $locale);
                $this->assertStringContainsString('Instagram', $description);
                $wordCount = count(preg_split(
                    '/\s+/u',
                    trim(strip_tags($description)),
                    -1,
                    PREG_SPLIT_NO_EMPTY,
                ));
                $this->assertGreaterThanOrEqual(120, $wordCount);
                $this->assertLessThanOrEqual(65, mb_strlen($work->getTranslation('meta_title', $locale)));
                $this->assertGreaterThanOrEqual(130, mb_strlen($work->getTranslation('meta_description', $locale)));
                $this->assertLessThanOrEqual(165, mb_strlen($work->getTranslation('meta_description', $locale)));
            }
        }

        $portofino = Work::query()->where('slug', 'dverni-rishennia-portofino-resort')->firstOrFail();
        $this->assertSame('Portofino Resort', $portofino->location);
        $this->assertStringContainsString(
            'скляними дверима в металевому профілі',
            $portofino->getTranslation('description', 'uk'),
        );
        $this->assertStringContainsString(
            'стеклянными дверями в металлическом профиле',
            $portofino->getTranslation('description', 'ru'),
        );

        $adminResponse = $this->actingAs($this->admin())
            ->get(route('admin.work.edit.page', ['work' => $portofino->id]))
            ->assertOk();

        $adminResponse->assertSee(
            e(json_encode($portofino->getTranslations('name'))),
            false,
        );
    }

    public function test_project_pages_and_homepage_render_imported_database_content_in_both_languages(): void
    {
        Storage::fake(config('app.images_disk_default'));
        $this->author();
        app(WorkContentImporter::class)->importFile(
            database_path('content/works/'.$this->contentFile),
        );

        $this->get('/nashi-roboty')
            ->assertOk()
            ->assertSee('Дверні рішення для Portofino Resort')
            ->assertSee('Двері в деревному декорі з розсувним полотном')
            ->assertSee('Білі двері з чорною фурнітурою у ванній кімнаті')
            ->assertDontSee('Роботи поки не додані');

        $this->get('/ru/nashi-roboty')
            ->assertOk()
            ->assertSee('Дверные решения для Portofino Resort')
            ->assertSee('Двери в древесном декоре с раздвижным полотном')
            ->assertSee('Белые двери с черной фурнитурой в ванной комнате')
            ->assertDontSee('Работы пока не добавлены');

        app()->setLocale('uk');

        $this->get('/nashi-roboty/dverni-rishennia-portofino-resort')
            ->assertOk()
            ->assertSee('Індивідуальні двері для простору біля моря')
            ->assertSee('https://www.instagram.com/reel/DZ79PUaoo4F/', false)
            ->assertSee('"@type":"CreativeWork"', false);

        $this->get('/ru/nashi-roboty/dverni-rishennia-portofino-resort')
            ->assertOk()
            ->assertSee('Индивидуальные двери для пространства у моря')
            ->assertSee('https://www.instagram.com/reel/DZ79PUaoo4F/', false);

        app()->setLocale('uk');

        $this->get('/')
            ->assertOk()
            ->assertSee('Дверні рішення для Portofino Resort')
            ->assertSee('/nashi-roboty/dverni-rishennia-portofino-resort', false)
            ->assertDontSee('Квартира, ЖК «Акварель»');
    }

    public function test_import_preserves_manager_copy_and_image_but_fills_blank_fields(): void
    {
        Storage::fake(config('app.images_disk_default'));
        $author = $this->author();
        $work = Work::query()->create([
            'creator_id' => $author->id,
            'slug' => 'dverni-rishennia-portofino-resort',
            'name' => ['uk' => 'Назва менеджера', 'ru' => 'Название менеджера'],
            'description' => ['uk' => '<p>Текст менеджера.</p>', 'ru' => '<p>Текст менеджера.</p>'],
            'image_path' => 'work-images/manager-photo.webp',
            'is_published' => true,
        ]);
        Storage::disk(config('app.images_disk_default'))->put($work->image_path, 'manager image');

        app(WorkContentImporter::class)->importFile(
            database_path('content/works/'.$this->contentFile),
        );

        $work->refresh();
        $this->assertSame('Назва менеджера', $work->getTranslation('name', 'uk'));
        $this->assertSame('<p>Текст менеджера.</p>', $work->getTranslation('description', 'ru'));
        $this->assertSame('work-images/manager-photo.webp', $work->image_path);
        $this->assertStringContainsString('Portofino Resort', $work->getTranslation('intro', 'uk'));
        Storage::disk(config('app.images_disk_default'))->assertExists('work-images/manager-photo.webp');
    }

    public function test_admin_service_stores_new_cover_and_gallery_uploads_as_webp(): void
    {
        Storage::fake(config('app.images_disk_default'));
        $admin = $this->admin();
        $this->actingAs($admin);

        $result = app(WorkService::class)->createWork(new EditWorkDTO(
            name: ['uk' => 'Тестовий проєкт', 'ru' => 'Тестовый проект'],
            slug: 'testovyi-proiekt',
            metaTitle: null,
            metaDescription: null,
            metaKeyWords: null,
            mainImage: UploadedFile::fake()->image('cover.jpg', 1200, 900),
            intro: ['uk' => 'Вступ', 'ru' => 'Вступление'],
            description: ['uk' => '<p>Опис.</p>', 'ru' => '<p>Описание.</p>'],
            images: [[
                'caption' => ['uk' => 'Деталь', 'ru' => 'Деталь'],
                'image' => UploadedFile::fake()->image('detail.png', 900, 900),
            ]],
        ));

        $this->assertTrue($result->isSuccess());
        $work = Work::query()->where('slug', 'testovyi-proiekt')->firstOrFail();
        $this->assertStringEndsWith('.webp', $work->image_path);
        Storage::disk(config('app.images_disk_default'))->assertExists($work->image_path);
        $gallery = $work->images()->firstOrFail();
        $this->assertStringEndsWith('.webp', $gallery->image_path);
        Storage::disk(config('app.images_disk_default'))->assertExists($gallery->image_path);
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
