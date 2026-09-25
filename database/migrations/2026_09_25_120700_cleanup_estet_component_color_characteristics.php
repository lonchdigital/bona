<?php

use App\Models\Product;
use App\Models\ProductCharacteristics;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $slugs = [
            'komplekt-teleskopichnoyi-lishtvi-na-1-storonu-estet',
            'komplekt-teleskopichnoyi-lishtvi-na-dvi-storoni-estet',
            'komplekt-komplanarnoyi-lishtvi-na-1-storonu-estet',
            'komplekt-doboru-100mm-estet',
            'komplekt-doboru-150mm-estet',
            'komplekt-doboru-200mm-estet',
            'plintus-2000-80-10-estet',
        ];

        $productIds = Product::query()->whereIn('slug', $slugs)->pluck('id');

        ProductCharacteristics::query()
            ->whereIn('product_id', $productIds)
            ->get()
            ->filter(function (ProductCharacteristics $characteristic): bool {
                return $characteristic->getTranslation('name', 'uk', false) === 'Оздоблення та кольори'
                    && $characteristic->getTranslation('name', 'ru', false) === 'Отделка и цвета'
                    && $characteristic->getTranslation('value', 'uk', false) === 'біла емаль, емаль RAL 7036, 9001, 1015, 1013, 7016'
                    && $characteristic->getTranslation('value', 'ru', false) === 'белая эмаль, эмаль RAL 7036, 9001, 1015, 1013, 7016';
            })
            ->each->delete();
    }

    public function down(): void
    {
        // The migration only removes exact duplicate rows created by the preceding import.
    }
};
