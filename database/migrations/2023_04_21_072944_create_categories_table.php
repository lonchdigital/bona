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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('creator_id');
            $table->foreign('creator_id')
                ->references('id')
                ->on('users');
            $table->unsignedBigInteger('product_type_id');
            $table->foreign('product_type_id')
                ->references('id')
                ->on('product_types');
            $table->json('name');
            $table->string('slug');
            $table->string('image_path');
            $table->unsignedBigInteger('count_of_products')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
