<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Color;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeOptions;
use App\Models\ProductCharacteristics;
use App\Models\ProductField;
use App\Models\ProductFieldOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class ReferenceRussianTranslationsTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_reference_translations_are_filled_without_overwriting_reviewed_admin_values(): void
    {
        $white = Color::query()->create([
            'creator_id' => $this->author()->id,
            'name' => ['uk' => 'Білий пп', 'ru' => 'Білий пп'],
            'slug' => 'bily-pp',
            'display_as_image' => false,
            'hex' => '#ffffff',
        ]);
        $satin = Color::query()->create([
            'creator_id' => $this->author()->id,
            'name' => ['uk' => 'Сатин білий'],
            'slug' => 'satyn-bilyi',
            'display_as_image' => false,
            'hex' => '#f7f7f5',
        ]);
        $custom = Color::query()->create([
            'creator_id' => $this->author()->id,
            'name' => ['uk' => 'Білий пп', 'ru' => 'Белый полипропилен'],
            'slug' => 'bily-pp-reviewed',
            'display_as_image' => false,
            'hex' => '#ffffff',
        ]);
        $brand = Brand::query()->create([
            'creator_id' => $this->author()->id,
            'name' => ['uk' => 'Двері Україна', 'ru' => 'Двері Україна'],
            'slug' => 'dveri-ukraina',
            'description' => ['uk' => '', 'ru' => ''],
        ]);

        $field = ProductField::query()->create([
            'creator_id' => $this->author()->id,
            'field_name' => ['uk' => 'Відкривання', 'ru' => 'Открывание'],
            'slug' => 'opening-side',
            'field_type_id' => 4,
        ]);
        $fieldOption = ProductFieldOption::query()->create([
            'product_field_id' => $field->id,
            'name' => ['uk' => 'ліве', 'ru' => 'ліве'],
            'slug' => 'left',
        ]);

        $product = $this->makeProduct();
        $attribute = ProductAttribute::query()->create([
            'attribute_name' => ['uk' => 'Відкривання', 'ru' => 'Открывание'],
            'slug' => 'opening',
        ]);
        $attributeOption = ProductAttributeOptions::query()->create([
            'product_id' => $product->id,
            'product_attribute_id' => $attribute->id,
            'name' => ['uk' => 'праве внутрішнє (INSIDE)'],
            'price' => 0,
        ]);
        $characteristic = ProductCharacteristics::query()->create([
            'product_id' => $product->id,
            'name' => ['uk' => 'Колір:', 'ru' => 'Колір:'],
            'value' => ['uk' => 'Білий', 'ru' => 'Белый'],
        ]);

        $migration = require database_path('migrations/2026_09_26_117100_fill_reference_russian_translations.php');
        $migration->up();
        $migration->up();

        $this->assertSame('Белый пп', $white->fresh()->getTranslation('name', 'ru'));
        $this->assertSame('Сатин белый', $satin->fresh()->getTranslation('name', 'ru'));
        $this->assertSame('Белый полипропилен', $custom->fresh()->getTranslation('name', 'ru'));
        $this->assertSame('Двери Украина', $brand->fresh()->getTranslation('name', 'ru'));
        $this->assertSame('левое', $fieldOption->fresh()->getTranslation('name', 'ru'));
        $this->assertSame('правое внутреннее (INSIDE)', $attributeOption->fresh()->getTranslation('name', 'ru'));
        $this->assertSame('Цвет:', $characteristic->fresh()->getTranslation('name', 'ru'));
    }
}
