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
        Schema::table('product_types', function (Blueprint $table) {
            $table->json('meta_product_title')->nullable()->after('meta_keywords');
            $table->json('meta_product_description')->nullable()->after('meta_product_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_types', function (Blueprint $table) {
            $table->dropColumn('meta_product_title');
            $table->dropColumn('meta_product_description');
        });
    }
};
