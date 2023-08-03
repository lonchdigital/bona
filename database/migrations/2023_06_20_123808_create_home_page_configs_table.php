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
        Schema::create('home_page_configs', function (Blueprint $table) {
            $table->id();
            $table->json('slider_title');
            $table->unsignedBigInteger('collection_id');
            $table->foreign('collection_id')
                ->references('id')
                ->on('collections');
            $table->string('slider_logo_image_path');
            $table->unsignedBigInteger('product_field_id');
            $table->foreign('product_field_id')
                ->references('id')
                ->on('product_fields');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_page_configs');
    }
};
