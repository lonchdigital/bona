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
        Schema::table('cart_products', function (Blueprint $table) {
            $table->json('attributes')->nullable()->after('count');
            $table->float('attributes_price')->nullable()->after('attributes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_products', function (Blueprint $table) {
            $table->dropColumn('attributes');
            $table->dropColumn('attributes_price');
        });
    }
};
