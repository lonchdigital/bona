<?php

use App\Services\FilterGroups\FilterGroupContentImporter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(FilterGroupContentImporter::class)->importFile(
            database_path('content/filter-groups/2026_09_26_interior_door_styles.json'),
        );
    }

    public function down(): void
    {
        // Editorial content can be changed in admin after import, so it is not removed automatically.
    }
};
