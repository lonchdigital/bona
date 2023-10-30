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
        Schema::table('product_fields', function (Blueprint $table) {
            $table->boolean('display_on_single')->after('as_image')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_fields', function (Blueprint $table) {
            $table->dropColumn('display_on_single');
        });
    }
};
