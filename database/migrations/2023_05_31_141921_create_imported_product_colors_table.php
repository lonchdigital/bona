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
        Schema::create('imported_product_colors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('imported_product_id');
            $table->foreign('imported_product_id')
                ->references('id')
                ->on('imported_products');
            $table->unsignedBigInteger('color_id');
            $table->foreign('color_id')
                ->references('id')
                ->on('colors');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imported_product_colors');
    }
};
