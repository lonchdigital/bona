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
        Schema::create('product_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('creator_id');
            $table->foreign('creator_id')
                ->references('id')
                ->on('users');
            $table->json('field_name');
            $table->string('slug')->unique();
            $table->integer('field_type_id');
            $table->json('field_size_name')->nullable();
            $table->boolean('is_multiselectable')->default(false);
            $table->boolean('as_image')->default(false);
            $table->boolean('is_mandatory')->default(false);
            $table->unsignedBigInteger('numeric_field_filter_type_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_fields');
    }
};
