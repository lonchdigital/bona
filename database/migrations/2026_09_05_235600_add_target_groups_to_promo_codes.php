<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_code_brand', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->unique(['promo_code_id', 'brand_id']);
        });

        Schema::create('promo_code_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->unique(['promo_code_id', 'category_id']);
        });

        Schema::create('promo_code_product_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_type_id')->constrained()->cascadeOnDelete();
            $table->unique(['promo_code_id', 'product_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_code_product_type');
        Schema::dropIfExists('promo_code_category');
        Schema::dropIfExists('promo_code_brand');
    }
};
