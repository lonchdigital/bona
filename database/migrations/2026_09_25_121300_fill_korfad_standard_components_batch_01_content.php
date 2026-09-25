<?php

use App\Models\Product;
use App\Services\Product\ProductContentImporter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(ProductContentImporter::class)->importFile(
            database_path('content/products/2026_09_25_korfad_standard_components_batch_01.json'),
        );

        $profile = Product::query()
            ->where('slug', 'h-podibniy-z-iednuvalniy-profil-38h2070-mdf-korfad')
            ->first();

        if ($profile?->getTranslation('name', 'ru', false) === 'H-образный соединительный профиль 38х2070 МДФ Korfad H-образный соединительный профиль 38х2070 МДФ Korfad') {
            $profile
                ->setTranslation('name', 'ru', 'H-образный соединительный профиль 38х2070 МДФ Korfad')
                ->save();
        }
    }

    public function down(): void
    {
        // Editorial content can be changed by a manager after import, so it is not removed automatically.
    }
};
