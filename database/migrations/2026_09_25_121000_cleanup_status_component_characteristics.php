<?php

use App\Models\Product;
use App\Models\ProductCharacteristics;
use App\Services\Product\ProductContentImporter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $legacySizes = [
            'komplekt-teleskopichnogo-korobu-ral-9003-2150h80mm-status' => '2150х80мм',
            'komplekt-teleskopichnoyi-lishtvi-ral-9003-2200h80mm-status' => '2200х80мм',
            'komplekt-doboru-ral-9003-2200h90mm-status' => '2200х90мм',
            'komplekt-doboru-ral-9003-2200h180mm-status' => '2200х180мм',
            'komplekt-doboru-ral-9003-2200h400mm-status' => '2200х400мм',
        ];

        Product::query()
            ->whereIn('slug', array_keys($legacySizes))
            ->get()
            ->each(function (Product $product) use ($legacySizes): void {
                ProductCharacteristics::query()
                    ->where('product_id', $product->id)
                    ->get()
                    ->filter(function (ProductCharacteristics $characteristic) use ($product, $legacySizes): bool {
                        $ukName = $characteristic->getTranslation('name', 'uk', false);
                        $ruName = $characteristic->getTranslation('name', 'ru', false);
                        $ukValue = $characteristic->getTranslation('value', 'uk', false);
                        $ruValue = $characteristic->getTranslation('value', 'ru', false);

                        $isLegacySize = $ukName === 'Розмір:'
                            && $ruName === 'Размер:'
                            && $ukValue === $legacySizes[$product->slug]
                            && $ruValue === $legacySizes[$product->slug];

                        $isLegacyColor = $ukName === 'Колір:'
                            && $ruName === 'Цвет:'
                            && $ukValue === 'Ral 9003 (білий)'
                            && $ruValue === 'Ral 9003 (белый)';

                        return $isLegacySize || $isLegacyColor;
                    })
                    ->each->delete();
            });

        app(ProductContentImporter::class)->importFile(
            database_path('content/products/2026_09_25_status_components_batch_01.json'),
        );
    }

    public function down(): void
    {
        // The migration removes exact legacy rows and keeps the reviewed replacement characteristics.
    }
};
