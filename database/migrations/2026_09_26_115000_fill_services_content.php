<?php

use App\Services\ServicesPage\ServicesContentImporter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services_configs', function (Blueprint $table) {
            $table->json('title')->nullable()->after('id');
            $table->json('intro')->nullable()->after('title');
            $table->json('content')->nullable()->after('intro');
        });

        app(ServicesContentImporter::class)->importFile(
            database_path('content/services/2026_09_26_services_content.json'),
        );
    }

    public function down(): void
    {
        Schema::table('services_configs', function (Blueprint $table) {
            $table->dropColumn(['title', 'intro', 'content']);
        });

        // FAQ content can be changed in admin after import, so it is not removed automatically.
    }
};
