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
        Schema::create('product_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('creator_id');
            $table->foreign('creator_id')
                ->references('id')
                ->on('users');
            $table->string('name');
            $table->string('slug')->unique();
            $table->json('product_point_name')->nullable();
            $table->json('meta_title');
            $table->json('meta_description');
            $table->json('meta_keywords');
            $table->boolean('has_brand')->default(false);
            $table->boolean('has_color')->default(false);
            $table->boolean('has_collection')->default(false);
            $table->boolean('has_category')->default(false);
            $table->boolean('has_size')->default(false);

            $table->boolean('has_length')->default(false);
            $table->boolean('filter_by_length')->default(false);
            $table->integer('product_size_length_filter_type_id')->nullable();
            $table->boolean('product_size_length_show_on_main_filter')->default(false);
            $table->integer('product_size_length_filter_full_position_id')->nullable();
            $table->json('product_size_length_filter_name')->nullable();

            $table->boolean('has_width')->default(false);
            $table->boolean('filter_by_width')->default(false);
            $table->integer('product_size_width_filter_type_id')->nullable();
            $table->boolean('product_size_width_show_on_main_filter')->default(false);
            $table->integer('product_size_width_filter_full_position_id')->nullable();
            $table->json('product_size_width_filter_name')->nullable();

            $table->boolean('has_height')->default(false);
            $table->boolean('filter_by_height')->default(false);
            $table->integer('product_size_height_filter_type_id')->nullable();
            $table->boolean('product_size_height_show_on_main_filter')->default(false);
            $table->integer('product_size_height_filter_full_position_id')->nullable();
            $table->json('product_size_height_filter_name')->nullable();

            $table->json('size_points')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_types');
    }
};
