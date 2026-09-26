<?php

use App\Models\Product;
use App\Models\ProductCharacteristics;
use App\Services\Product\ProductContentImporter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(ProductContentImporter::class)->importFile(
            database_path('content/products/2026_09_26_short_description_products_batch_01.json'),
        );

        $product = Product::query()
            ->where('slug', 'mizhkimnatni-dveri-elegance-wood')
            ->first();

        if ($product === null) {
            return;
        }

        $seen = [];

        ProductCharacteristics::query()
            ->where('product_id', $product->id)
            ->orderBy('id')
            ->get()
            ->each(function (ProductCharacteristics $characteristic) use (&$seen): void {
                $signature = collect(['uk', 'ru'])
                    ->flatMap(fn (string $locale): array => [
                        mb_strtolower(trim($characteristic->getTranslation('name', $locale, false), " \t\n\r\0\x0B:")),
                        trim($characteristic->getTranslation('value', $locale, false)),
                    ])
                    ->implode("\n");

                if (isset($seen[$signature])) {
                    $characteristic->delete();

                    return;
                }

                $seen[$signature] = true;
            });
    }

    public function down(): void
    {
        // Editorial content can be changed by a manager after import, so it is not removed automatically.
    }
};
