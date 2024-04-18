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
        Schema::table('contact_configs', function (Blueprint $table) {
            $table->text('meta_tags')->after('meta_keywords')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_configs', function (Blueprint $table) {
            $table->dropColumn('meta_tags');
        });
    }
};
