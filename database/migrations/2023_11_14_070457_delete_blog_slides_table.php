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
        Schema::table('blog_slides', function (Blueprint $table) {
            $table->dropForeign(['collection_id']);
        });

        Schema::dropIfExists('blog_slides');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('blog_slides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('collection_id');
            $table->foreign('collection_id')
                ->references('id')
                ->on('collections');
            $table->json('description');
            $table->string('slide_image_1_path');
            $table->string('slide_image_2_path');
            $table->timestamps();
        });
    }
};
