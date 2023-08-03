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
        Schema::create('imported_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_product_id')->nullable();
            $table->foreign('parent_product_id')
                ->references('id')
                ->on('imported_products');
            $table->unsignedBigInteger('product_type_id');
            $table->foreign('product_type_id')
                ->references('id')
                ->on('product_types');
            $table->string('sku');
            $table->json('name');
            $table->string('slug')->unique();
            $table->float('old_price_in_currency')->nullable();
            $table->float('price_in_currency')->nullable();
            $table->unsignedBigInteger('price_currency_id')->nullable();
            $table->foreign('price_currency_id')
                ->references('id')
                ->on('currencies');
            //use data class instead of foreign table
            $table->unsignedBigInteger('availability_status_id');
            $table->json('special_offers')->nullable();
            $table->string('preview_image_path')->nullable();
            $table->string('main_image_path')->nullable();
            $table->string('pattern_image_path')->nullable();
            $table->json('gallery_images')->nullable();
            $table->unsignedBigInteger('main_color_id')->nullable();
            $table->foreign('main_color_id')
                ->references('id')
                ->on('colors');
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->foreign('brand_id')
                ->references('id')
                ->on('brands');
            $table->unsignedBigInteger('collection_id')->nullable();
            $table->foreign('collection_id')
                ->references('id')
                ->on('collections');
            $table->unsignedBigInteger('country_id');
            $table->foreign('country_id')
                ->references('id')
                ->on('countries');
            $table->json('meta_title');
            $table->json('meta_description');
            $table->json('meta_keywords');
            $table->float('length')->nullable();
            $table->float('width')->nullable();
            $table->float('height')->nullable();
            $table->unsignedBigInteger('orders_count')->default(0);
            $table->json('custom_fields')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imported_products');
    }
};
