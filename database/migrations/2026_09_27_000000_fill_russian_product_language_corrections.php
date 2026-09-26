<?php

use App\Services\Product\ProductContentImporter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(ProductContentImporter::class)->importFile(
            database_path('content/products/2026_09_27_russian_language_corrections.json'),
        );
    }

    public function down(): void
    {
        // Reviewed catalogue translations may be edited later in admin, so they are kept on rollback.
    }
};
