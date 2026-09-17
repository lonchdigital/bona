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
        ProductText::query()->create(['product_id' => $boilerplate->id, 'language' => 'uk', 'content' => $longText]);
        $replacePath = tempnam(sys_get_temp_dir(), 'content').'.json';
        file_put_contents($replacePath, json_encode([['slug' => 'replace-me', 'replace_content' => true, 'content' => ['uk' => '<p>Новий унікальний опис.</p>']]], JSON_UNESCAPED_UNICODE));
        app(ProductContentImporter::class)->importFile($replacePath);
        $this->assertSame('<p>Новий унікальний опис.</p>', ProductText::query()->where(['product_id' => $boilerplate->id, 'language' => 'uk'])->value('content'));

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
}
