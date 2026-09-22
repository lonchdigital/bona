<?php

use App\Services\ProductCategory\CategoryContentImporter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(CategoryContentImporter::class)->importFile(database_path('content/categories/2026_09_22_accessory_categories.json'));
    }

    public function down(): void
    {
        // Content written or adjusted by managers after the import must not be removed.
    }
};
