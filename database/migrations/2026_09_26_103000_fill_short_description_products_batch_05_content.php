<?php

use App\Services\Product\ProductContentImporter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(ProductContentImporter::class)->importFile(
            database_path('content/products/2026_09_26_short_description_products_batch_05.json'),
        );
    }

    public function down(): void
    {
        // Editorial content can be changed by a manager after import, so it is not removed automatically.
    }
};
