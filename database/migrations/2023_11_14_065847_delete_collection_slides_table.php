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
        Schema::table('collection_slides', function (Blueprint $table) {
            $table->dropForeign(['collection_id']);
        });

        Schema::dropIfExists('collection_slides');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('collection_slides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('collection_id');
            $table->foreign('collection_id')
                ->references('id')
                ->on('collections');
            $table->string('image_1_path');
            $table->string('image_2_path');
            $table->timestamps();
        });
    }
};
