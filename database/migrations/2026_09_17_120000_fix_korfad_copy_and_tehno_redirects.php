<?php

use App\Models\Product;
use App\Models\ProductSlugRedirect;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Typos in the description shared by Korfad doors. */
    private const COPY_FIXES = [
        'uk' => ['ТМ МKorfad' => 'ТМ Korfad', 'г. Корюківка' => 'м. Корюківка', 'плівкой' => 'плівкою', 'придає їм' => 'надає їм'],
        'ru' => ['ТМ МKorfad' => 'ТМ Korfad'],
    ];

    public function up(): void
    {
        foreach (self::COPY_FIXES as $locale => $fixes) {
            DB::table('product_texts')
                ->where('language', $locale)
                ->where('content', 'like', '%МKorfad%')
                ->orderBy('id')
                ->each(function ($row) use ($fixes) {
                    DB::table('product_texts')->where('id', $row->id)->update(['content' => strtr((string) $row->content, $fixes)]);
                });
        }

        // Earlier Techno door slugs Search Console still crawls; the color
        // moved behind the size when the catalog was re-imported.
        $colors = [
            'antracit' => ['tehno-1' => 'antracit-7024', 'tehno-baza' => 'antracit-ral-7024'],
            'korichneva-shagren' => ['tehno-1' => 'korichneva-shagren-ral-8017', 'tehno-baza' => 'korichneva-shagren-ral-8017'],
        ];
        $heights = ['tehno-1' => '2050', 'tehno-baza' => '2070'];

        foreach ($colors as $oldColor => $models) {
            foreach ($models as $model => $newColor) {
                foreach (['860', '960', '1200'] as $width) {
                    $oldSlug = "{$model}-{$oldColor}-{$heights[$model]}-{$width}";
                    $newSlug = "{$model}-{$heights[$model]}-{$width}-{$newColor}";
                    $productId = Product::query()->where('slug', $newSlug)->value('id');

                    if ($productId === null || Product::query()->where('slug', $oldSlug)->exists()) {
                        continue;
                    }

                    ProductSlugRedirect::query()->firstOrCreate(['slug' => $oldSlug], ['product_id' => $productId]);
                }
            }
        }
    }

    public function down(): void
    {
        // Typo fixes and redirects are not reverted.
    }
};
