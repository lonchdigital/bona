<?php

use App\Services\Product\ProductContentImporter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(ProductContentImporter::class)->importFile(database_path('content/products/2026_09_22_korfad_aluminium_loft_plato_batch_01.json'));
    }

    public function down(): void
    {
        // Content written or adjusted by managers after the import must not be removed.
    }
};
