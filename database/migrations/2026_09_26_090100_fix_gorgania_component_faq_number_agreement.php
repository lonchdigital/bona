<?php

use App\Models\Product;
use App\Models\ProductFaqs;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $entries = json_decode(
            (string) file_get_contents(database_path('content/products/2026_09_25_gorgania_components_batch_01.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $productIds = Product::query()
            ->whereIn('slug', array_column($entries, 'slug'))
            ->pluck('id');

        $corrections = [
            'uk' => [
                'from' => 'У чинній картці доступно 12 варіанти. Назву кольору слід звірити з полотном і сусідніми деталями, а відтінок перевірити за зразком.',
                'to' => 'У чинній картці доступно 12 варіантів. Назву кольору слід звірити з полотном і сусідніми деталями, а відтінок перевірити за зразком.',
            ],
            'ru' => [
                'from' => 'В действующей карточке доступно 12 варианта. Название цвета следует сверить с полотном и соседними деталями, а оттенок проверить по образцу.',
                'to' => 'В действующей карточке доступно 12 вариантов. Название цвета следует сверить с полотном и соседними деталями, а оттенок проверить по образцу.',
            ],
        ];

        ProductFaqs::query()
            ->whereIn('product_id', $productIds)
            ->each(function (ProductFaqs $faq) use ($corrections): void {
                $changed = false;

                foreach ($corrections as $locale => $correction) {
                    if ($faq->getTranslation('answer', $locale, false) !== $correction['from']) {
                        continue;
                    }

                    $faq->setTranslation('answer', $locale, $correction['to']);
                    $changed = true;
                }

                if ($changed) {
                    $faq->save();
                }
            });
    }

    public function down(): void
    {
        // Editorial content can be changed by a manager after import, so it is not rolled back automatically.
    }
};
