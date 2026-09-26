<?php

use App\Services\Product\ProductContentImporter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $importer = app(ProductContentImporter::class);

        $importer->importFile(
            database_path('content/products/2026_09_25_remaining_components_batch_01.json'),
        );

        // Reapply the Gorgania package after correcting number agreement in both locales.
        $importer->importFile(
            database_path('content/products/2026_09_25_gorgania_components_batch_01.json'),
        );
    }

    public function down(): void
    {
        // Editorial content can be changed by a manager after import, so it is not removed automatically.
    }
};
