<?php

use App\Services\Product\ProductContentImporter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Batch 01 is safe to repeat and now also replaces its duplicated
        // legacy meta titles through the explicit replace_meta flag.
        app(ProductContentImporter::class)->importFile(database_path('content/products/2026_09_22_korfad_aliano_batch_01.json'));
        app(ProductContentImporter::class)->importFile(database_path('content/products/2026_09_22_korfad_aliano_batch_02.json'));
    }

    public function down(): void
    {
        // Content written or adjusted by managers after the import must not be removed.
    }
};
