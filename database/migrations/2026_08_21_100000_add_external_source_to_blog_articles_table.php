<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('blog_articles', function (Blueprint $table) {
            // Kept short so the composite index below stays well inside the
            // InnoDB key limit on utf8mb4.
            $table->string('external_source', 64)->after('slug')->nullable();
            $table->string('external_id', 191)->after('external_source')->nullable();

            $table->index(['external_source', 'external_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_articles', function (Blueprint $table) {
            $table->dropIndex(['external_source', 'external_id']);

            $table->dropColumn('external_source');
            $table->dropColumn('external_id');
        });
    }
};
