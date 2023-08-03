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
        Schema::create('product_field_filter_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_field_id');
            $table->foreign('product_field_id')
                ->references('id')
                ->on('product_fields');
            $table->json('name');
            $table->string('slug');
            $table->float('from');
            $table->float('to');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_field_filter_options');
    }
};
