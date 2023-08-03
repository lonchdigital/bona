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
        Schema::create('product_field_product_type', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_type_id');
            $table->foreign('product_type_id')
                ->references('id')
                ->on('product_types');
            $table->unsignedBigInteger('product_field_id');
            $table->foreign('product_field_id')
                ->references('id')
                ->on('product_fields');
            $table->boolean('show_as_filter')->default(false);
            $table->boolean('show_on_main_filters_list')->default(false);
            $table->json('filter_name')->nullable();
            $table->unsignedBigInteger('filter_full_position_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_field_product_type');
    }
};
