<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_page_configs', function (Blueprint $table) {
            $table->json('style_section')->nullable()->after('product_types');
        });
    }

    public function down(): void
    {
        Schema::table('home_page_configs', function (Blueprint $table) {
            $table->dropColumn('style_section');
        });
    }
};
