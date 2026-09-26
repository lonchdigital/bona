<?php

namespace Tests\Feature;

use App\Models\Color;
use App\Models\DoorConfiguratorItem;
use App\Models\Role;
use App\Models\User;
use App\Services\Product\ConfiguratorCatalog;
use App\Services\Product\ConfiguratorEditor;
use App\Services\Product\ConfiguratorImporter;
use App\Services\Product\DoorConfiguratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class DoorConfiguratorAdminTest extends TestCase
{
    use MakesShopData, RefreshDatabase;

    private function admin(): User
    {
        DB::table('roles')->insertOrIgnore(['id' => Role::ADMIN_ROLE_ID, 'role' => 'Admin', 'role_slug' => 'admin']);
        $user = User::factory()->create();
        $user->update(['role_id' => Role::ADMIN_ROLE_ID]);

        return $user;
    }

    private function material(): array
    {
        Storage::fake('public');
        $this->seedCurrency();
        $product = $this->makeProduct(['slug' => 'mizhkimnatni-dveri-artporte-nyu-york', 'price' => 5000, 'availability_status_id' => 2]);
        $color = Color::create(['id' => 148, 'slug' => 'ivory', 'hex' => '#eeeeee', 'display_as_image' => false, 'name' => ['uk' => 'Айворі', 'ru' => 'Айвори'], 'creator_id' => $this->author()->id]);
        $product->colors()->attach($color->id, ['price' => 250]);
        $presets = app(DoorConfiguratorService::class)->filePresets();
        $door = collect($presets['products'])->firstWhere('id', 'new-york');
        $door['colors'] = [collect($door['colors'])->firstWhere('colorId', 148)];

        return [$product, ['products' => [$door], 'handles' => []]];
    }

    private function imported(): DoorConfiguratorItem
    {
        [, $presets] = $this->material();
        app(ConfiguratorImporter::class)->run($presets, false);

        return DoorConfiguratorItem::firstOrFail();
    }

    private function nextShade(array $presets, bool $link = true): array
    {
        $white = collect(app(DoorConfiguratorService::class)->filePresets()['products'][0]['colors'])->firstWhere('id', 'white');
        if ($link) {
            Color::create(['id' => 155, 'slug' => 'white', 'hex' => '#ffffff', 'display_as_image' => false, 'name' => ['uk' => 'Білий', 'ru' => 'Белый'], 'creator_id' => $this->author()->id]);
            DoorConfiguratorItem::firstOrFail()->product->colors()->attach(155);
        }
        $presets['products'][0]['colors'][] = $white;

        return $presets;
    }

    public function test_incremental_import_preserves_drafts_and_only_publishes_new_shades(): void
    {
        [$product, $presets] = $this->material();
        $importer = app(ConfiguratorImporter::class);
        $importer->run($presets, false);
        $item = DoorConfiguratorItem::firstOrFail();
        $published = $item->published;
        $draft = $item->draft;
        $draft['short']['uk'] = 'Ручна чернетка';
        $draft['colors'][0]['handle'] = [0.3, 0.4];
        $item = app(ConfiguratorEditor::class)->change($item, 1, 'save', null, $draft, 77);
        $product->update(['slug' => 'renamed-after-import']);
        $presets = $this->nextShade($presets);
        $files = Storage::disk('public')->allFiles();
        $this->assertSame(1, $importer->run($presets, appendShades: true)['shades_added']);
        $this->assertSame($files, Storage::disk('public')->allFiles());
        $this->assertSame(2, $item->fresh()->version);
        $report = $importer->run($presets, false, appendShades: true, publishShades: true);
        $item->refresh();
        $this->assertSame(1, $report['shades_added']);
        $this->assertSame(1, $report['shades_published']);
        $this->assertSame('Ручна чернетка', $item->draft['short']['uk']);
        $this->assertSame($published['short'], $item->published['short']);
        $this->assertEquals($draft['colors'][0], $item->draft['colors'][0]);
        $this->assertEquals($published['colors'][0], $item->published['colors'][0]);
        $this->assertSame(77, $item->sort_order);
        $this->assertSame(0, $item->published_sort_order);
        $this->assertSame($presets['products'][0]['crop'], $item->published['colors'][1]['crop']);
        $this->assertSame(3, $item->version);
        $this->assertSame(0, $importer->run($presets, false, appendShades: true, publishShades: true)['shades_added']);
        $this->assertSame(3, $item->fresh()->version);
        $this->assertSame(1, $item->revisions()->where('action', 'import-shades')->count());
    }

    public function test_import_never_resurrects_removed_shades_or_hidden_models(): void
    {
        [, $presets] = $this->material();
        $importer = app(ConfiguratorImporter::class);
        $importer->run($presets, false);
        $item = DoorConfiguratorItem::firstOrFail();
        $editor = app(ConfiguratorEditor::class);
        $draft = $item->draft;
        $draft['colors'] = [array_replace($draft['colors'][0], ['id' => 'manual-placeholder', 'colorId' => null, 'enabled' => false])];
        $item = $editor->change($item, 1, 'save', null, $draft);
        $item = $editor->change($item, 2, 'hide', null);
        $presets = $this->nextShade($presets);
        $report = $importer->run($presets, false, appendShades: true, publishShades: true);
        $this->assertSame(1, $report['shades_added']);
        $this->assertSame(0, $report['shades_published']);
        $item->refresh();
        $this->assertNull($item->published);
        $this->assertSame([null, 155], array_column($item->draft['colors'], 'colorId'));
        $original = $item->revisions()->where('action', 'import')->firstOrFail();
        $item = $editor->change($item, 4, 'restore', null, revisionId: $original->id);
        $this->assertSame(0, $importer->run($presets, false, appendShades: true)['shades_added']);
        $this->assertSame([148], array_column($item->fresh()->draft['colors'], 'colorId'));
    }

    public function test_unlinked_shade_stays_disabled_and_cannot_enter_published_catalog(): void
    {
        [, $presets] = $this->material();
        $importer = app(ConfiguratorImporter::class);
        $importer->run($presets, false);
        $presets = $this->nextShade($presets, false);
        $report = $importer->run($presets, false, appendShades: true, publishShades: true);
        $item = DoorConfiguratorItem::firstOrFail();
        $this->assertSame(1, $report['shades_added']);
        $this->assertSame(0, $report['shades_published']);
        $this->assertNotEmpty($report['warnings']);
        $this->assertFalse($item->draft['colors'][1]['enabled']);
        $this->assertCount(1, $item->published['colors']);
    }

    public function test_draft_only_import_requires_explicit_publication_and_deduplicates_combinations(): void
    {
        [, $presets] = $this->material();
        $importer = app(ConfiguratorImporter::class);
        $importer->run($presets, false);
        $presets = $this->nextShade($presets);
        $presets['products'][0]['colors'][] = array_replace($presets['products'][0]['colors'][0], ['id' => 'duplicate-color']);
        $this->assertSame(1, $importer->run($presets, false, appendShades: true)['shades_added']);
        $item = DoorConfiguratorItem::firstOrFail();
        $this->assertCount(2, $item->draft['colors']);
        $this->assertCount(1, $item->published['colors']);
        $this->assertSame(0, $importer->run($presets, false, appendShades: true, publishShades: true)['shades_published']);
        $this->assertCount(1, $item->fresh()->published['colors']);
        $item = app(ConfiguratorEditor::class)->change($item, 2, 'publish', null);
        $this->assertCount(2, $item->published['colors']);
    }

    public function test_dry_run_writes_nothing_and_import_is_idempotent_with_immutable_media(): void
    {
        [, $presets] = $this->material();
        $importer = app(ConfiguratorImporter::class);
        $this->assertSame(1, $importer->run($presets)['created']);
        $this->assertDatabaseCount('door_configurator_items', 0);
        $this->assertEmpty(Storage::disk('public')->allFiles());
        $this->assertFalse(app(ConfiguratorCatalog::class)->managed());
        $report = $importer->run($presets, false);
        $this->assertSame(1, $report['published']);
        $item = DoorConfiguratorItem::firstOrFail();
        $files = Storage::disk('public')->allFiles();
        $this->assertCount(2, $files);
        $draft = $item->draft;
        $draft['short']['uk'] = 'Ручна правка';
        app(ConfiguratorEditor::class)->change($item, 1, 'save', null, $draft);
        $this->assertSame(1, $importer->run($presets, false)['preserved']);
        $this->assertDatabaseCount('door_configurator_items', 1);
        $this->assertSame($files, Storage::disk('public')->allFiles());
        $this->assertSame('Ручна правка', $item->fresh()->draft['short']['uk']);
    }

    public function test_stable_id_draft_publish_hide_restore_and_current_prices(): void
    {
        $item = $this->imported();
        $editor = app(ConfiguratorEditor::class);
        $service = app(DoorConfiguratorService::class);
        $initial = $service->catalog()['products'][0];
        $item->product->update(['slug' => 'renamed-door', 'price' => 7000]);
        $this->assertStringContainsString('renamed-door', $service->catalog()['products'][0]['url']);
        $this->assertEquals(7250, $service->catalog()['products'][0]['price']);
        $draft = $item->draft;
        $draft['short']['uk'] = 'Змінена назва';
        $item = $editor->change($item, 1, 'save', null, $draft, 99);
        $this->assertSame($initial['short'], $service->catalog()['products'][0]['short']);
        $this->assertSame(0, $item->published_sort_order);
        $item = $editor->change($item, 2, 'publish', null);
        $this->assertSame('Змінена назва', $service->catalog()['products'][0]['short']);
        $this->assertSame(99, $item->published_sort_order);
        $selection = $service->selection(['product' => 'new-york', 'color' => 'ivory', 'handle' => null]);
        $this->assertEquals(7250, $selection['total']);
        $item = $editor->change($item, 3, 'hide', null);
        $this->assertEmpty($service->catalog()['products']);
        $revision = $item->revisions()->where('action', 'import')->firstOrFail();
        $item = $editor->change($item, 4, 'restore', null, revisionId: $revision->id);
        $this->assertSame($initial['short'], $item->draft['short']['uk']);
        $this->assertEmpty($service->catalog()['products']);
        $this->assertCount(2, Storage::disk('public')->allFiles());
    }

    public function test_admin_pages_and_preview_are_private_and_never_allow_draft_cart(): void
    {
        $item = $this->imported();
        $this->get('/admin/door-configurator')->assertForbidden();
        $this->get('/admin/door-configurator/'.$item->id.'/preview')->assertForbidden();
        $this->actingAs(User::factory()->create())->get('/admin/door-configurator')->assertNotFound();
        $this->actingAs($this->admin())->get('/admin/door-configurator')->assertOk()->assertSee('Конфігуратор дверей');
        $this->get('/admin/door-configurator/'.$item->id)->assertOk()->assertSee('Зберегти чернетку')->assertSee('cfg-editor-data', false);
        $this->get('/admin/door-configurator/'.$item->id.'/preview')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow')->assertSee('"preview":true', false);
        $draft = $item->draft;
        $draft['colors'][0]['id'] = 'not-published';
        app(ConfiguratorEditor::class)->change($item, 1, 'save', null, $draft);
        $this->postJson('/door-configurator/cart', ['product' => 'new-york', 'color' => 'not-published', 'expected_total' => 5250, 'request_id' => '11111111-1111-4111-a111-111111111111'])->assertUnprocessable();
    }

    public function test_edit_conflicts_and_foreign_revision_restore_are_rejected(): void
    {
        $item = $this->imported();
        $this->actingAs($this->admin());
        $this->postJson('/admin/door-configurator/'.$item->id, ['version' => 99, 'payload' => json_encode($item->draft), 'sort_order' => 1])->assertUnprocessable()->assertJsonValidationErrors('version');
        $other = DoorConfiguratorItem::create(['kind' => 'door', 'key' => 'unlinked', 'draft' => $item->draft]);
        app(ConfiguratorEditor::class)->record($other, 'create');
        $this->post('/admin/door-configurator/'.$item->id.'/restore', ['version' => 1, 'revision_id' => $other->revisions()->first()->id])->assertNotFound();
        $this->assertSame(1, $item->fresh()->version);
    }

    public function test_rejected_form_input_remains_visibly_unsaved(): void
    {
        $item = $this->imported();
        $this->actingAs($this->admin());
        $url = '/admin/door-configurator/'.$item->id;
        $draft = $item->draft;
        $draft['short']['uk'] = 'Незбережена зміна';
        $this->from($url)->post($url, ['version' => 99, 'payload' => json_encode($draft), 'sort_order' => 1])
            ->assertRedirect($url)->assertSessionHasErrors('version');
        $this->get($url)->assertOk()->assertSee('"unsaved":true', false)
            ->assertSee('id="cfg-publish" disabled', false)->assertSee('Незбережена зміна');
        $this->assertNotSame('Незбережена зміна', $item->fresh()->draft['short']['uk']);
        $draft['colors'][0]['hex'] = 'invalid';
        $this->from($url)->post($url, ['version' => 1, 'payload' => json_encode($draft), 'sort_order' => 1])
            ->assertRedirect($url)->assertSessionHasErrors('colors.0.hex');
        $this->get($url)->assertOk()->assertSee('"unsaved":true', false)->assertSee('id="cfg-publish" disabled', false);
    }

    public function test_publication_rejects_missing_assets_foreign_colors_options_and_bad_geometry(): void
    {
        $item = $this->imported();
        $this->actingAs($this->admin());
        foreach ([['colorId', 99999], ['optionIds', [99999]], ['crop', [0, 0, 900, 900]], ['preview', '/storage/door-configurator/'.str_repeat('a', 64).'.webp']] as [$key, $value]) {
            $draft = $item->draft;
            $draft['colors'][0][$key] = $value;
            $item->update(['draft' => $draft]);
            $this->postJson('/admin/door-configurator/'.$item->id.'/publish', ['version' => 1])->assertUnprocessable();
            $this->assertSame(1, $item->fresh()->version);
        }
        $draft = $item->draft;
        $draft['colors'][0]['preview'] = 'https://example.com/image.webp';
        $this->postJson('/admin/door-configurator/'.$item->id, ['version' => 1, 'payload' => json_encode($draft), 'sort_order' => 1])->assertUnprocessable()->assertJsonValidationErrors('colors.0.preview');
        $draft['colors'][0]['preview'] = '/storage/door-configurator/../../.env';
        $this->postJson('/admin/door-configurator/'.$item->id, ['version' => 1, 'payload' => json_encode($draft), 'sort_order' => 1])->assertUnprocessable();
    }

    public function test_upload_is_reencoded_and_previous_image_survives_replacement(): void
    {
        $item = $this->imported();
        $this->actingAs($this->admin());
        $response = $this->post('/admin/door-configurator/'.$item->id.'/upload', ['image' => UploadedFile::fake()->image('door.png', 800, 837)]);
        $response->assertOk();
        $this->assertMatchesRegularExpression('~^/storage/door-configurator/[a-f0-9]{64}\.webp$~', $response->json('url'));
        $this->assertCount(3, Storage::disk('public')->allFiles());
        $this->assertSame(1, $item->fresh()->version);
        $this->postJson('/admin/door-configurator/'.$item->id.'/upload', ['image' => UploadedFile::fake()->create('script.svg', 1, 'image/svg+xml')])->assertUnprocessable();
    }

    public function test_new_catalog_color_is_reported_but_not_published_and_deleted_product_is_removed(): void
    {
        $item = $this->imported();
        $color = Color::create(['slug' => 'new-color', 'hex' => '#999999', 'display_as_image' => false, 'name' => ['uk' => 'Новий відтінок', 'ru' => 'Новый оттенок'], 'creator_id' => $this->author()->id]);
        $item->product->colors()->attach($color->id);
        $this->actingAs($this->admin())->get('/admin/door-configurator/'.$item->id)->assertOk()->assertSee('Нові кольори в каталозі без матеріалів');
        $this->get('/admin/door-configurator?status=missing')->assertSee($item->product->name);
        $this->assertCount(1, app(DoorConfiguratorService::class)->catalog()['products'][0]['colors']);
        $item->product->colors()->detach();
        $item->product->delete();
        $this->assertNull($item->fresh()->product_id);
        $this->assertEmpty(app(DoorConfiguratorService::class)->catalog()['products']);
        $this->assertNotEmpty(app(ConfiguratorCatalog::class)->issues($item->fresh()));
    }
}
