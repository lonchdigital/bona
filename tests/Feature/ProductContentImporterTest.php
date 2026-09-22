<?php

namespace Tests\Feature;

use App\Models\ProductCharacteristics;
use App\Models\ProductFaqs;
use App\Models\ProductText;
use App\Services\Product\ProductContentImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class ProductContentImporterTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_it_fills_gaps_and_never_overwrites_manager_content(): void
    {
        $empty = $this->makeProduct(['slug' => 'dveri-prihovanogo-montazhu-shpon']);
        $stub = $this->makeProduct(['slug' => 'dveri-prihovanogo-montazhu-gruntovani-bez-obkladu']);
        $written = $this->makeProduct([
            'slug' => 'dveri-prihovanogo-montazhu-monoblack',
            'meta_title' => ['uk' => 'Власний title', 'ru' => 'Свой title'],
        ]);

        ProductText::query()->create(['product_id' => $stub->id, 'language' => 'uk', 'content' => 'Двері прихованого монтажу під фарбування, без обкладу.']);
        ProductCharacteristics::query()->create(['product_id' => $stub->id, 'name' => ['uk' => 'Відкривання:', 'ru' => 'Открывание:'], 'value' => ['uk' => 'праве/ліве', 'ru' => 'правое/левое']]);
        $longText = str_repeat('Опис, який менеджер уже написав. ', 20);
        ProductText::query()->create(['product_id' => $written->id, 'language' => 'uk', 'content' => $longText]);
        ProductFaqs::query()->create(['product_id' => $written->id, 'question' => ['uk' => 'Q', 'ru' => 'Q'], 'answer' => ['uk' => 'A', 'ru' => 'A']]);

        $boilerplate = $this->makeProduct(['slug' => 'replace-me']);
        $boilerplate->update(['meta_title' => ['uk' => 'Повторюваний title', 'ru' => 'Повторяющийся title']]);
        ProductText::query()->create(['product_id' => $boilerplate->id, 'language' => 'uk', 'content' => $longText]);
        $replacePath = tempnam(sys_get_temp_dir(), 'content').'.json';
        file_put_contents($replacePath, json_encode([[
            'slug' => 'replace-me',
            'replace_content' => true,
            'replace_meta' => true,
            'content' => ['uk' => '<p>Новий унікальний опис.</p>'],
            'meta_title' => ['uk' => 'Новий title'],
        ]], JSON_UNESCAPED_UNICODE));
        app(ProductContentImporter::class)->importFile($replacePath);
        $this->assertSame('<p>Новий унікальний опис.</p>', ProductText::query()->where(['product_id' => $boilerplate->id, 'language' => 'uk'])->value('content'));
        $this->assertSame('Новий title', $boilerplate->fresh()->getTranslation('meta_title', 'uk'));

        $path = database_path('content/products/2026_09_17_hidden_doors.json');
        $importer = app(ProductContentImporter::class);
        $importer->importFile($path);
        $second = $importer->importFile($path);

        $this->assertStringContainsString('<h2>', ProductText::query()->where(['product_id' => $empty->id, 'language' => 'uk'])->value('content'));
        $this->assertStringContainsString('<h2>', ProductText::query()->where(['product_id' => $empty->id, 'language' => 'ru'])->value('content'));
        $this->assertStringContainsString('Axor', ProductText::query()->where(['product_id' => $stub->id, 'language' => 'uk'])->value('content'));
        $this->assertSame($longText, ProductText::query()->where(['product_id' => $written->id, 'language' => 'uk'])->value('content'));

        $this->assertSame(1, ProductFaqs::query()->where('product_id', $written->id)->count());
        $this->assertSame(4, ProductFaqs::query()->where('product_id', $empty->id)->count());
        $this->assertSame('Власний title', $written->fresh()->getTranslation('meta_title', 'uk'));
        $this->assertStringContainsString('Приховані двері', $empty->fresh()->getTranslation('meta_title', 'uk'));

        $stubNames = ProductCharacteristics::query()->where('product_id', $stub->id)->get()->map->getTranslation('name', 'uk');
        $this->assertSame(1, $stubNames->filter(fn ($name) => str_starts_with($name, 'Відкривання'))->count());
        $this->assertContains('Завіси', $stubNames->all());

        $this->assertSame(0, $second['products'], 'A repeated import must be a no-op.');

        $this->get('/product/dveri-prihovanogo-montazhu-shpon')
            ->assertOk()
            ->assertSee('"@type":"FAQPage"', false)
            ->assertSee('Оздоблення: натуральний шпон', false);
    }

    public function test_it_imports_the_first_korfad_batch_and_is_idempotent(): void
    {
        $products = collect([
            'mizhkimnatni-dveri-aliano-al-01-korfad',
            'mizhkimnatni-dveri-aliano-al-02-korfad',
        ])->mapWithKeys(function (string $slug) {
            $product = $this->makeProduct(['slug' => $slug]);

            foreach (['uk', 'ru'] as $language) {
                ProductText::query()->create([
                    'product_id' => $product->id,
                    'language' => $language,
                    'content' => str_repeat('Спільний опис Korfad. ', 20),
                ]);
            }

            return [$slug => $product];
        });

        $path = database_path('content/products/2026_09_22_korfad_aliano_batch_01.json');
        $importer = app(ProductContentImporter::class);
        $first = $importer->importFile($path);
        $second = $importer->importFile($path);

        $this->assertSame(2, $first['products']);
        $this->assertSame([], $first['missing']);
        $this->assertSame(0, $second['products']);

        foreach ($products as $slug => $product) {
            $model = str_contains($slug, 'al-01') ? 'AL-01' : 'AL-02';

            foreach (['uk', 'ru'] as $language) {
                $content = ProductText::query()
                    ->where(['product_id' => $product->id, 'language' => $language])
                    ->value('content');

                $this->assertStringContainsString('<h2>', $content);
                $this->assertStringContainsString($model, $content);
            }

            $this->assertSame(12, ProductCharacteristics::query()->where('product_id', $product->id)->count());
            $this->assertSame(4, ProductFaqs::query()->where('product_id', $product->id)->count());

            $this->get("/product/{$slug}")
                ->assertOk()
                ->assertSee($model)
                ->assertSee('"@type":"FAQPage"', false);
        }

        $this->assertNotSame(
            ProductText::query()->where(['product_id' => $products->first()->id, 'language' => 'uk'])->value('content'),
            ProductText::query()->where(['product_id' => $products->last()->id, 'language' => 'uk'])->value('content'),
        );
    }

    public function test_it_imports_the_second_korfad_batch_and_keeps_every_description_unique(): void
    {
        $slugs = collect(range(3, 7))->map(
            fn (int $model) => sprintf('mizhkimnatni-dveri-aliano-al-%02d-korfad', $model),
        );
        $products = $slugs->mapWithKeys(function (string $slug) {
            $product = $this->makeProduct(['slug' => $slug]);

            foreach (['uk', 'ru'] as $language) {
                ProductText::query()->create([
                    'product_id' => $product->id,
                    'language' => $language,
                    'content' => str_repeat('Спільний опис Korfad. ', 20),
                ]);
            }

            return [$slug => $product];
        });

        $path = database_path('content/products/2026_09_22_korfad_aliano_batch_02.json');
        $importer = app(ProductContentImporter::class);
        $first = $importer->importFile($path);
        $second = $importer->importFile($path);

        $this->assertSame(5, $first['products']);
        $this->assertSame([], $first['missing']);
        $this->assertSame(0, $second['products']);

        $descriptions = [];
        foreach ($products as $slug => $product) {
            $model = strtoupper(str_replace(['mizhkimnatni-dveri-aliano-', '-korfad'], '', $slug));
            $content = ProductText::query()
                ->where(['product_id' => $product->id, 'language' => 'uk'])
                ->value('content');

            $this->assertStringContainsString("Korfad Aliano {$model}", $content);
            $this->assertSame(12, ProductCharacteristics::query()->where('product_id', $product->id)->count());
            $this->assertSame(4, ProductFaqs::query()->where('product_id', $product->id)->count());
            $descriptions[] = $content;
        }

        $this->assertCount(5, array_unique($descriptions));
    }

    public function test_it_imports_aluminium_loft_plato_content_and_keeps_every_description_unique(): void
    {
        $models = collect(['ALP-01', 'ALP-02', 'ALP-03', 'ALP-07']);
        $products = $models->mapWithKeys(function (string $model) {
            $slug = 'mizhkimnatni-dveri-aluminium-loft-plato-'.strtolower($model).'-korfad';
            $product = $this->makeProduct(['slug' => $slug]);

            foreach (['uk', 'ru'] as $language) {
                ProductText::query()->create([
                    'product_id' => $product->id,
                    'language' => $language,
                    'content' => str_repeat('Спільний опис Korfad. ', 20),
                ]);
            }

            return [$model => $product];
        });

        $path = database_path('content/products/2026_09_22_korfad_aluminium_loft_plato_batch_01.json');
        $importer = app(ProductContentImporter::class);
        $first = $importer->importFile($path);
        $second = $importer->importFile($path);

        $this->assertSame(4, $first['products']);
        $this->assertSame([], $first['missing']);
        $this->assertSame(0, $second['products']);

        $descriptions = [];
        foreach ($products as $model => $product) {
            foreach (['uk', 'ru'] as $language) {
                $content = ProductText::query()
                    ->where(['product_id' => $product->id, 'language' => $language])
                    ->value('content');

                $this->assertStringContainsString("Korfad Aluminium Loft Plato {$model}", $content);
                $this->assertSame(1, substr_count($content, '<h2>'));
                $this->assertSame(3, substr_count($content, '<h3>'));
            }

            $this->assertSame(12, ProductCharacteristics::query()->where('product_id', $product->id)->count());
            $this->assertSame(4, ProductFaqs::query()->where('product_id', $product->id)->count());
            $descriptions[] = ProductText::query()
                ->where(['product_id' => $product->id, 'language' => 'uk'])
                ->value('content');
        }

        $this->assertCount(4, array_unique($descriptions));
    }

    public function test_it_imports_classico_content_and_keeps_every_description_unique(): void
    {
        $models = collect(['CL-02', 'CL-05', 'CL-07', 'CL-08', 'CL-09']);
        $products = $models->mapWithKeys(function (string $model) {
            $slug = 'mizhkimnatni-dveri-classico-'.strtolower($model).'-korfad';
            $product = $this->makeProduct(['slug' => $slug]);

            foreach (['uk', 'ru'] as $language) {
                ProductText::query()->create([
                    'product_id' => $product->id,
                    'language' => $language,
                    'content' => str_repeat('Спільний опис Korfad Classico. ', 20),
                ]);
            }

            return [$model => $product];
        });

        $path = database_path('content/products/2026_09_22_korfad_classico_batch_01.json');
        $importer = app(ProductContentImporter::class);
        $first = $importer->importFile($path);
        $second = $importer->importFile($path);

        $this->assertSame(5, $first['products']);
        $this->assertSame([], $first['missing']);
        $this->assertSame(0, $second['products']);

        $descriptions = [];
        foreach ($products as $model => $product) {
            foreach (['uk', 'ru'] as $language) {
                $content = ProductText::query()
                    ->where(['product_id' => $product->id, 'language' => $language])
                    ->value('content');

                $this->assertStringContainsString("Korfad Classico {$model}", $content);
                $this->assertSame(1, substr_count($content, '<h2>'));
                $this->assertSame(3, substr_count($content, '<h3>'));
            }

            $this->assertSame(12, ProductCharacteristics::query()->where('product_id', $product->id)->count());
            $this->assertSame(4, ProductFaqs::query()->where('product_id', $product->id)->count());
            $descriptions[] = ProductText::query()
                ->where(['product_id' => $product->id, 'language' => 'uk'])
                ->value('content');
        }

        $this->assertCount(5, array_unique($descriptions));
    }

    public function test_it_imports_florence_content_and_keeps_every_description_unique(): void
    {
        $models = collect(['FL-01', 'FL-02', 'FL-03', 'FL-04', 'FL-05']);
        $products = $models->mapWithKeys(function (string $model) {
            $slug = 'mizhkimnatni-dveri-florence-'.strtolower($model).'-korfad';
            $product = $this->makeProduct(['slug' => $slug]);

            foreach (['uk', 'ru'] as $language) {
                ProductText::query()->create([
                    'product_id' => $product->id,
                    'language' => $language,
                    'content' => str_repeat('Спільний опис Korfad Florence. ', 20),
                ]);
            }

            return [$model => $product];
        });

        $path = database_path('content/products/2026_09_22_korfad_florence_batch_01.json');
        $importer = app(ProductContentImporter::class);
        $first = $importer->importFile($path);
        $second = $importer->importFile($path);

        $this->assertSame(5, $first['products']);
        $this->assertSame([], $first['missing']);
        $this->assertSame(0, $second['products']);

        $descriptions = [];
        foreach ($products as $model => $product) {
            foreach (['uk', 'ru'] as $language) {
                $content = ProductText::query()
                    ->where(['product_id' => $product->id, 'language' => $language])
                    ->value('content');

                $this->assertStringContainsString("Korfad Florence {$model}", $content);
                $this->assertSame(1, substr_count($content, '<h2>'));
                $this->assertSame(3, substr_count($content, '<h3>'));
            }

            $this->assertSame(12, ProductCharacteristics::query()->where('product_id', $product->id)->count());
            $this->assertSame(4, ProductFaqs::query()->where('product_id', $product->id)->count());
            $descriptions[] = ProductText::query()
                ->where(['product_id' => $product->id, 'language' => 'uk'])
                ->value('content');
        }

        $this->assertCount(5, array_unique($descriptions));
    }

    public function test_it_imports_plato_content_and_keeps_every_description_unique(): void
    {
        $models = collect([
            'DLP-01' => 'mizhkimnatni-dveri-deco-loft-plato-dlp-01-korfad',
            'GLP-01' => 'mizhkimnatni-dveri-glass-loft-plato-glp-01-korfad',
            'GLP-02' => 'mizhkimnatni-dveri-glass-loft-plato-glp-02-korfad',
            'LP-01' => 'mizhkimnatni-dveri-loft-plato-lp-01-korfad',
            'WP-01' => 'mizhkimnatni-dveri-wood-plato-wp-01-korfad',
        ]);
        $products = $models->mapWithKeys(function (string $slug, string $model) {
            $product = $this->makeProduct(['slug' => $slug]);

            foreach (['uk', 'ru'] as $language) {
                ProductText::query()->create([
                    'product_id' => $product->id,
                    'language' => $language,
                    'content' => str_repeat('Спільний опис Korfad Plato. ', 20),
                ]);
            }

            return [$model => $product];
        });

        $path = database_path('content/products/2026_09_23_korfad_plato_batch_01.json');
        $importer = app(ProductContentImporter::class);
        $first = $importer->importFile($path);
        $second = $importer->importFile($path);

        $this->assertSame(5, $first['products']);
        $this->assertSame([], $first['missing']);
        $this->assertSame(0, $second['products']);

        $descriptions = [];
        foreach ($products as $model => $product) {
            foreach (['uk', 'ru'] as $language) {
                $content = ProductText::query()
                    ->where(['product_id' => $product->id, 'language' => $language])
                    ->value('content');

                $this->assertStringContainsString($model, $content);
                $this->assertSame(1, substr_count($content, '<h2>'));
                $this->assertSame(3, substr_count($content, '<h3>'));
            }

            $this->assertSame(12, ProductCharacteristics::query()->where('product_id', $product->id)->count());
            $this->assertSame(4, ProductFaqs::query()->where('product_id', $product->id)->count());
            $descriptions[] = ProductText::query()
                ->where(['product_id' => $product->id, 'language' => 'uk'])
                ->value('content');
        }

        $this->assertCount(5, array_unique($descriptions));
    }

    public function test_it_imports_the_first_exellence_batch_and_keeps_every_description_unique(): void
    {
        $models = collect(['ANIMAS', 'ASATI', 'ATLANT', 'BONETTI', 'BRILLAR']);
        $products = $models->mapWithKeys(function (string $model) {
            $slug = 'mizhkimnatni-dveri-korfad-exellence-'.strtolower($model);
            $product = $this->makeProduct(['slug' => $slug]);

            foreach (['uk', 'ru'] as $language) {
                ProductText::query()->create([
                    'product_id' => $product->id,
                    'language' => $language,
                    'content' => str_repeat('Спільний опис Korfad Exellence. ', 20),
                ]);
            }

            return [$model => $product];
        });

        $path = database_path('content/products/2026_09_23_korfad_exellence_batch_01.json');
        $importer = app(ProductContentImporter::class);
        $first = $importer->importFile($path);
        $second = $importer->importFile($path);

        $this->assertSame(5, $first['products']);
        $this->assertSame([], $first['missing']);
        $this->assertSame(0, $second['products']);

        $descriptions = [];
        foreach ($products as $model => $product) {
            foreach (['uk', 'ru'] as $language) {
                $content = ProductText::query()
                    ->where(['product_id' => $product->id, 'language' => $language])
                    ->value('content');

                $this->assertStringContainsString('Korfad Exellence '.ucfirst(strtolower($model)), $content);
                $this->assertSame(1, substr_count($content, '<h2>'));
                $this->assertSame(3, substr_count($content, '<h3>'));
            }

            $this->assertSame(12, ProductCharacteristics::query()->where('product_id', $product->id)->count());
            $this->assertSame(4, ProductFaqs::query()->where('product_id', $product->id)->count());
            $descriptions[] = ProductText::query()
                ->where(['product_id' => $product->id, 'language' => 'uk'])
                ->value('content');
        }

        $this->assertCount(5, array_unique($descriptions));
    }
}
