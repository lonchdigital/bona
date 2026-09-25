<?php

use App\Services\Product\ProductContentImporter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(ProductContentImporter::class)->importFile(
            database_path('content/products/2026_09_25_estet_doors_components_batch_01.json'),
        );
    }

    public function down(): void
    {
        // Editorial content can be changed by a manager after import, so it is not removed automatically.
    }
};
