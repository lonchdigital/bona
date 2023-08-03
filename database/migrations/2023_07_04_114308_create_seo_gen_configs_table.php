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
        Schema::create('seo_gen_configs', function (Blueprint $table) {
            $table->id();
            $table->string('page_type');
            $table->unsignedBigInteger('product_type_id')->nullable();
            $table->foreign('product_type_id')
                ->references('id')
                ->on('product_types');
            $table->json('html_title_tag');
            $table->json('html_h1_tag');
            $table->json('meta_title_tag');
            $table->json('meta_description_tag');
            $table->json('meta_keywords_tag');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_gen_configs');
    }
};
