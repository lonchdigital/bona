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
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['parent_product_id']);
            $table->dropColumn(['parent_product_id']);

            $table->dropColumn(['pattern_image_path']);
            $table->dropColumn(['gallery_images']);

            $table->dropForeign(['collection_id']);
            $table->dropColumn(['collection_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {

            $table->unsignedBigInteger('parent_product_id')->nullable();
            $table->foreign('parent_product_id')
                ->references('id')
                ->on('products');

            $table->string('pattern_image_path');
            $table->json('gallery_images');

            $table->unsignedBigInteger('collection_id')->nullable();
            $table->foreign('collection_id')
                ->references('id')
                ->on('collections');
        });
    }
};
