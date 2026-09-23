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

    public function test_it_imports_the_second_exellence_batch_and_keeps_every_description_unique(): void
    {
        $models = collect(['CALYPSO', 'CELESTIA', 'INFINITY', 'MARION', 'MONARCH']);
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

        $path = database_path('content/products/2026_09_23_korfad_exellence_batch_02.json');
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

    public function test_it_imports_the_third_exellence_batch_and_keeps_every_description_unique(): void
    {
        $models = collect([
            'MONARCH GLASS' => 'mizhkimnatni-dveri-korfad-exellence-monarch-glass',
            'PAULINA' => 'mizhkimnatni-dveri-korfad-exellence-paulina',
            'QUANTUM' => 'mizhkimnatni-dveri-korfad-exellence-quantum',
            'RAMIRA' => 'mizhkimnatni-dveri-korfad-exellence-ramira',
            'ROISEL' => 'mizhkimnatni-dveri-korfad-exellence-roisel',
        ]);
        $products = $models->mapWithKeys(function (string $slug, string $model) {
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

        $path = database_path('content/products/2026_09_23_korfad_exellence_batch_03.json');
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

                $this->assertStringContainsString('Korfad Exellence '.ucwords(strtolower($model)), $content);
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

    public function test_it_imports_the_fourth_korfad_batch_and_keeps_every_description_unique(): void
    {
        $models = collect([
            'SALIN' => [
                'slug' => 'mizhkimnatni-dveri-korfad-exellence-salin',
                'heading' => 'Korfad Exellence Salin',
            ],
            'SPARTA' => [
                'slug' => 'mizhkimnatni-dveri-korfad-exellence-sparta',
                'heading' => 'Korfad Exellence Sparta',
            ],
            'WESTON' => [
                'slug' => 'mizhkimnatni-dveri-korfad-exellence-weston',
                'heading' => 'Korfad Exellence Weston',
            ],
            'WETTER' => [
                'slug' => 'mizhkimnatni-dveri-korfad-exellence-wetter',
                'heading' => 'Korfad Exellence Wetter',
            ],
            'MILANO ML-05' => [
                'slug' => 'mizhkimnatni-dveri-milano-ml-05-korfad',
                'heading' => 'Korfad Milano ML-05',
            ],
        ]);
        $products = $models->mapWithKeys(function (array $details, string $model) {
            $product = $this->makeProduct(['slug' => $details['slug']]);

            foreach (['uk', 'ru'] as $language) {
                ProductText::query()->create([
                    'product_id' => $product->id,
                    'language' => $language,
                    'content' => str_repeat('Спільний опис Korfad. ', 20),
                ]);
            }

            return [$model => ['product' => $product, 'heading' => $details['heading']]];
        });

        $path = database_path('content/products/2026_09_23_korfad_exellence_batch_04.json');
        $importer = app(ProductContentImporter::class);
        $first = $importer->importFile($path);
        $second = $importer->importFile($path);

        $this->assertSame(5, $first['products']);
        $this->assertSame([], $first['missing']);
        $this->assertSame(0, $second['products']);

        $descriptions = [];
        foreach ($products as $details) {
            $product = $details['product'];

            foreach (['uk', 'ru'] as $language) {
                $content = ProductText::query()
                    ->where(['product_id' => $product->id, 'language' => $language])
                    ->value('content');

                $this->assertStringContainsString($details['heading'], $content);
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

    public function test_it_imports_the_first_porto_group_batch_and_keeps_every_description_unique(): void
    {
        $models = collect([
            'PARMA PM-10' => [
                'slug' => 'mizhkimnatni-dveri-parma-pm-10-korfad',
                'heading' => 'Korfad Parma PM-10',
            ],
            'PIANO DELUXE PND-01' => [
                'slug' => 'mizhkimnatni-dveri-piano-deluxe-pnd-01-korfad',
                'heading' => 'Korfad Piano Deluxe PND-01',
            ],
            'PORTO DELUXE PD-01' => [
                'slug' => 'mizhkimnatni-dveri-porto-deluxe-pd-01-korfad',
                'heading' => 'Korfad Porto Deluxe PD-01',
            ],
            'PORTO DELUXE PD-03' => [
                'slug' => 'mizhkimnatni-dveri-porto-deluxe-pd-03-korfad',
                'heading' => 'Korfad Porto Deluxe PD-03',
            ],
            'PORTO PR-01' => [
                'slug' => 'mizhkimnatni-dveri-porto-pr-01-korfad',
                'heading' => 'Korfad Porto PR-01',
            ],
        ]);
        $products = $models->mapWithKeys(function (array $details, string $model) {
            $product = $this->makeProduct(['slug' => $details['slug']]);

            foreach (['uk', 'ru'] as $language) {
                ProductText::query()->create([
                    'product_id' => $product->id,
                    'language' => $language,
                    'content' => str_repeat('Спільний опис Korfad. ', 20),
                ]);
            }

            return [$model => ['product' => $product, 'heading' => $details['heading']]];
        });

        $path = database_path('content/products/2026_09_23_korfad_porto_batch_01.json');
        $importer = app(ProductContentImporter::class);
        $first = $importer->importFile($path);
        $second = $importer->importFile($path);

        $this->assertSame(5, $first['products']);
        $this->assertSame([], $first['missing']);
        $this->assertSame(0, $second['products']);

        $descriptions = [];
        foreach ($products as $details) {
            $product = $details['product'];

            foreach (['uk', 'ru'] as $language) {
                $content = ProductText::query()
                    ->where(['product_id' => $product->id, 'language' => $language])
                    ->value('content');

                $this->assertStringContainsString($details['heading'], $content);
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

    public function test_it_imports_the_second_porto_group_batch_and_keeps_every_description_unique(): void
    {
        $models = collect([
            'PORTO PR-05' => [
                'slug' => 'mizhkimnatni-dveri-porto-pr-05-korfad',
                'heading' => 'Korfad Porto PR-05',
            ],
            'PORTO PR-08' => [
                'slug' => 'mizhkimnatni-dveri-porto-pr-08-korfad',
                'heading' => 'Korfad Porto PR-08',
            ],
            'PORTO PR-10' => [
                'slug' => 'mizhkimnatni-dveri-porto-pr-10-korfad',
                'heading' => 'Korfad Porto PR-10',
            ],
            'PORTO PR-12' => [
                'slug' => 'mizhkimnatni-dveri-porto-pr-12-korfad',
                'heading' => 'Korfad Porto PR-12',
            ],
            'SCALEA SC-04' => [
                'slug' => 'mizhkimnatni-dveri-scalea-sc-04-korfad',
                'heading' => 'Korfad Scalea SC-04',
            ],
        ]);
        $products = $models->mapWithKeys(function (array $details, string $model) {
            $product = $this->makeProduct(['slug' => $details['slug']]);

            foreach (['uk', 'ru'] as $language) {
                ProductText::query()->create([
                    'product_id' => $product->id,
                    'language' => $language,
                    'content' => str_repeat('Спільний опис Korfad. ', 20),
                ]);
            }

            return [$model => ['product' => $product, 'heading' => $details['heading']]];
        });

        $path = database_path('content/products/2026_09_23_korfad_porto_batch_02.json');
        $importer = app(ProductContentImporter::class);
        $first = $importer->importFile($path);
        $second = $importer->importFile($path);

        $this->assertSame(5, $first['products']);
        $this->assertSame([], $first['missing']);
        $this->assertSame(0, $second['products']);

        $descriptions = [];
        foreach ($products as $details) {
            $product = $details['product'];

            foreach (['uk', 'ru'] as $language) {
                $content = ProductText::query()
                    ->where(['product_id' => $product->id, 'language' => $language])
                    ->value('content');

                $this->assertStringContainsString($details['heading'], $content);
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

    public function test_it_imports_the_sanvito_and_valentino_batch_and_keeps_every_description_unique(): void
    {
        $models = collect([
            'SANREMO SR-01' => [
                'slug' => 'mizhkimnatni-dveri-sanremo-sr-01-korfad',
                'heading' => 'Korfad Sanremo SR-01',
            ],
            'SANVITO MIRROR SV-01' => [
                'slug' => 'mizhkimnatni-dveri-sanvito-dzerkalo-sv-01-korfad',
                'heading' => 'Korfad Sanvito SV-01',
            ],
            'SANVITO SV-01' => [
                'slug' => 'mizhkimnatni-dveri-sanvito-sv-01-korfad',
                'heading' => 'Korfad Sanvito SV-01',
            ],
            'VALENTINO DELUXE VLD-01' => [
                'slug' => 'mizhkimnatni-dveri-valentino-deluxe-vld-01-korfad',
                'heading' => 'Korfad Valentino Deluxe VLD-01',
            ],
            'VALENTINO DELUXE VLD-03' => [
                'slug' => 'mizhkimnatni-dveri-valentino-deluxe-vld-03-korfad',
                'heading' => 'Korfad Valentino Deluxe VLD-03',
            ],
        ]);
        $products = $models->mapWithKeys(function (array $details, string $model) {
            $product = $this->makeProduct(['slug' => $details['slug']]);

            foreach (['uk', 'ru'] as $language) {
                ProductText::query()->create([
                    'product_id' => $product->id,
                    'language' => $language,
                    'content' => str_repeat('Спільний опис Korfad. ', 20),
                ]);
            }

            return [$model => ['product' => $product, 'heading' => $details['heading']]];
        });

        $path = database_path('content/products/2026_09_23_korfad_sanvito_valentino_batch_01.json');
        $importer = app(ProductContentImporter::class);
        $first = $importer->importFile($path);
        $second = $importer->importFile($path);

        $this->assertSame(5, $first['products']);
        $this->assertSame([], $first['missing']);
        $this->assertSame(0, $second['products']);

        $descriptions = [];
        foreach ($products as $details) {
            $product = $details['product'];

            foreach (['uk', 'ru'] as $language) {
                $content = ProductText::query()
                    ->where(['product_id' => $product->id, 'language' => $language])
                    ->value('content');

                $this->assertStringContainsString($details['heading'], $content);
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

    public function test_it_imports_the_venecia_batch_and_keeps_every_description_unique(): void
    {
        $models = collect([
            'VENECIA DELUXE VND-02' => [
                'slug' => 'mizhkimnatni-dveri-venecia-deluxe-vnd-02-korfad',
                'heading' => 'Korfad Venecia Deluxe VND-02',
            ],
            'VENECIA DELUXE VND-04' => [
                'slug' => 'mizhkimnatni-dveri-venecia-deluxe-vnd-04-korfad',
                'heading' => 'Korfad Venecia Deluxe VND-04',
            ],
            'VENECIA DELUXE VND-05' => [
                'slug' => 'mizhkimnatni-dveri-venecia-deluxe-vnd-05-korfad',
                'heading' => 'Korfad Venecia Deluxe VND-05',
            ],
        ]);
        $products = $models->mapWithKeys(function (array $details, string $model) {
            $product = $this->makeProduct(['slug' => $details['slug']]);

            foreach (['uk', 'ru'] as $language) {
                ProductText::query()->create([
                    'product_id' => $product->id,
                    'language' => $language,
                    'content' => str_repeat('Спільний опис Korfad. ', 20),
                ]);
            }

            return [$model => ['product' => $product, 'heading' => $details['heading']]];
        });

        $path = database_path('content/products/2026_09_23_korfad_venecia_batch_01.json');
        $importer = app(ProductContentImporter::class);
        $first = $importer->importFile($path);
        $second = $importer->importFile($path);

        $this->assertSame(3, $first['products']);
        $this->assertSame([], $first['missing']);
        $this->assertSame(0, $second['products']);

        $descriptions = [];
        foreach ($products as $details) {
            $product = $details['product'];

            foreach (['uk', 'ru'] as $language) {
                $content = ProductText::query()
                    ->where(['product_id' => $product->id, 'language' => $language])
                    ->value('content');

                $this->assertStringContainsString($details['heading'], $content);
                $this->assertSame(1, substr_count($content, '<h2>'));
                $this->assertSame(3, substr_count($content, '<h3>'));
            }

            $this->assertSame(12, ProductCharacteristics::query()->where('product_id', $product->id)->count());
            $this->assertSame(4, ProductFaqs::query()->where('product_id', $product->id)->count());
            $descriptions[] = ProductText::query()
                ->where(['product_id' => $product->id, 'language' => 'uk'])
                ->value('content');
        }

        $this->assertCount(3, array_unique($descriptions));
    }

    public function test_it_imports_the_mvm_z1210_batch_and_keeps_every_variant_unique(): void
    {
        $variants = collect([
            'HANDLE' => [
                'slug' => 'z-1210-sn-cp-matoviy-nikel-polirovaniy-hrom',
                'heading' => 'Z-1210',
            ],
            'CYLINDER' => [
                'slug' => 'z-1210-sn-cp-z-nakladkoyu-pid-cilindr-matoviy-nikel-polirovaniy-hrom',
                'heading' => 'Z-1210',
            ],
            'WC' => [
                'slug' => 'z-1210-sn-cp-z-nakladkoyu-pid-wc-matoviy-nikel-polirovaniy-hrom',
                'heading' => 'Z-1210',
            ],
        ]);
        $products = $variants->mapWithKeys(function (array $details, string $variant) {
            $product = $this->makeProduct(['slug' => $details['slug']]);

            return [$variant => ['product' => $product, 'heading' => $details['heading']]];
        });

        $path = database_path('content/products/2026_09_23_mvm_z1210_batch_01.json');
        $importer = app(ProductContentImporter::class);
        $first = $importer->importFile($path);
        $second = $importer->importFile($path);

        $this->assertSame(3, $first['products']);
        $this->assertSame([], $first['missing']);
        $this->assertSame(0, $second['products']);

        $descriptions = [];
        foreach ($products as $details) {
            $product = $details['product'];

            foreach (['uk', 'ru'] as $language) {
                $text = ProductText::query()
                    ->where(['product_id' => $product->id, 'language' => $language])
                    ->firstOrFail();

                $this->assertStringContainsString($details['heading'], $text->content);
                $this->assertNotEmpty($text->short_content);
                $this->assertNotEmpty($product->fresh()->getTranslation('meta_title', $language, false));
                $this->assertNotEmpty($product->fresh()->getTranslation('meta_description', $language, false));
                $this->assertSame(1, substr_count($text->content, '<h2>'));
                $this->assertSame(3, substr_count($text->content, '<h3>'));
            }

            $this->assertSame(6, ProductCharacteristics::query()->where('product_id', $product->id)->count());
            $this->assertSame(3, ProductFaqs::query()->where('product_id', $product->id)->count());
            $descriptions[] = ProductText::query()
                ->where(['product_id' => $product->id, 'language' => 'uk'])
                ->value('content');
        }

        $this->assertCount(3, array_unique($descriptions));
    }

    public function test_it_imports_the_mvm_z1220_batch_and_keeps_every_variant_unique(): void
    {
        $this->assertMvmBatchImports('2026_09_23_mvm_z1220_batch_01.json', 9);
    }

    public function test_it_imports_the_mvm_z1259_to_z1319_batch_and_keeps_every_variant_unique(): void
    {
        $this->assertMvmBatchImports('2026_09_23_mvm_z1259_z1319_batch_01.json', 20);
    }

    private function assertMvmBatchImports(string $filename, int $expectedProducts): void
    {
        $path = database_path("content/products/{$filename}");
        $entries = collect(json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR));
        $products = $entries->mapWithKeys(function (array $entry) {
            $product = $this->makeProduct(['slug' => $entry['slug']]);

            return [$entry['slug'] => $product];
        });

        $importer = app(ProductContentImporter::class);
        $first = $importer->importFile($path);
        $second = $importer->importFile($path);

        $this->assertSame($expectedProducts, $first['products']);
        $this->assertSame([], $first['missing']);
        $this->assertSame(0, $second['products']);

        $descriptions = [];
        foreach ($entries as $entry) {
            $product = $products[$entry['slug']];
            $model = collect($entry['characteristics'])
                ->firstWhere('name.uk', 'Модель')['value']['uk'];

            foreach (['uk', 'ru'] as $language) {
                $text = ProductText::query()
                    ->where(['product_id' => $product->id, 'language' => $language])
                    ->firstOrFail();

                $this->assertStringContainsString($model, $text->content);
                $this->assertNotEmpty($text->short_content);
                $this->assertNotEmpty($product->fresh()->getTranslation('meta_title', $language, false));
                $this->assertNotEmpty($product->fresh()->getTranslation('meta_description', $language, false));
                $this->assertSame(1, substr_count($text->content, '<h2>'));
                $this->assertSame(3, substr_count($text->content, '<h3>'));
            }

            $this->assertSame(6, ProductCharacteristics::query()->where('product_id', $product->id)->count());
            $this->assertSame(3, ProductFaqs::query()->where('product_id', $product->id)->count());
            $descriptions[] = ProductText::query()
                ->where(['product_id' => $product->id, 'language' => 'uk'])
                ->value('content');
        }

        $this->assertCount($expectedProducts, array_unique($descriptions));
    }
}
