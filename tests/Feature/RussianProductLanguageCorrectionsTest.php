<?php

namespace Tests\Feature;

use App\Services\Product\ProductContentImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class RussianProductLanguageCorrectionsTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_language_corrections_update_names_and_omega_meta_idempotently(): void
    {
        $this->seedCurrency();

        $expectedNames = [
            'luce-p-biliy' => ['uk' => 'LUCE P, білий', 'ru' => 'LUCE P, белый'],
            'mirou-ultra-qdoors-vhidni' => ['uk' => 'Мироу Ультра Qdoors вхідні', 'ru' => 'Мироу Ультра Qdoors входные'],
            'plintus-farbovaniy-provence-mod.-pmp018-2800-100-12mm' => ['uk' => 'Плінтус фарбований Provence мод. PMP018 2800*100*12мм', 'ru' => 'Плинтус крашеный Provence мод. PMP018 2800*100*12мм'],
            'tehnichni-ei-60-2070-1200-shagren-ral-7024' => ['uk' => 'Технічні EI 60 2070*1200 шагрень RAL 7024', 'ru' => 'Технические EI 60 2070*1200 шагрень RAL 7024'],
            'tehnichni-ei-60-2070-1200-shagren-ral-7035' => ['uk' => 'Технічні EI 60 2070*1200 шагрень RAL 7035', 'ru' => 'Технические EI 60 2070*1200 шагрень RAL 7035'],
            'tehnichni-ei-60-2070-870-shagren-ral-7024' => ['uk' => 'Технічні EI 60 2070*870 шагрень RAL 7024', 'ru' => 'Технические EI 60 2070*870 шагрень RAL 7024'],
            'tehnichni-ei-60-2070-870-shagren-ral-7035' => ['uk' => 'Технічні EI 60 2070*870 шагрень RAL 7035', 'ru' => 'Технические EI 60 2070*870 шагрень RAL 7035'],
            'tehnichni-ei-60-2070-960-shagren-ral-7024' => ['uk' => 'Технічні EI 60 2070*960 шагрень RAL 7024', 'ru' => 'Технические EI 60 2070*960 шагрень RAL 7024'],
            'tehnichni-ei-60-2070-960-shagren-ral-7035' => ['uk' => 'Технічні EI 60 2070*960 шагрень RAL 7035', 'ru' => 'Технические EI 60 2070*960 шагрень RAL 7035'],
        ];

        $products = collect($expectedNames)->mapWithKeys(function (array $names, string $slug) {
            return [$slug => $this->makeProduct([
                'slug' => $slug,
                'name' => ['uk' => $names['uk'], 'ru' => $names['uk']],
            ])];
        });
        $omega = $this->makeProduct([
            'slug' => 'komplekt-komplanarnoyi-lishtvi-inside-na-2-storoni-omega',
            'name' => ['uk' => 'Комплект компланарної лиштви', 'ru' => 'Комплект компланарных наличников'],
            'meta_title' => ['uk' => 'Старий title', 'ru' => 'Старый title'],
            'meta_description' => ['uk' => 'Старий опис', 'ru' => 'Старое описание'],
        ]);

        $path = database_path('content/products/2026_09_27_russian_language_corrections.json');
        $importer = app(ProductContentImporter::class);
        $first = $importer->importFile($path);
        $second = $importer->importFile($path);

        $this->assertSame(10, $first['products']);
        $this->assertSame([], $first['missing']);
        $this->assertSame(0, $second['products']);

        foreach ($expectedNames as $slug => $names) {
            $product = $products[$slug]->fresh();
            $this->assertSame($names['uk'], $product->getTranslation('name', 'uk', false));
            $this->assertSame($names['ru'], $product->getTranslation('name', 'ru', false));
        }

        $this->assertSame(
            'Компланарные наличники Omega Inside на 2 стороны | Bona',
            $omega->fresh()->getTranslation('meta_title', 'ru', false),
        );
        $this->assertSame(
            'Комплект компланарных наличников Omega Inside на две стороны дверного блока. Подбор совместимого погонажа в Одессе и доставка заказов по Украине.',
            $omega->fresh()->getTranslation('meta_description', 'ru', false),
        );

        $this->get('/ru/product/luce-p-biliy')
            ->assertOk()
            ->assertSee('<h1 id="product-title">LUCE P, белый</h1>', false);
        $this->get('/ru/product/komplekt-komplanarnoyi-lishtvi-inside-na-2-storoni-omega')
            ->assertOk()
            ->assertSee('<title>Компланарные наличники Omega Inside на 2 стороны | Bona</title>', false);
    }
}
