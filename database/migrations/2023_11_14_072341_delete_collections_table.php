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
        Schema::table('collections', function (Blueprint $table) {
            $table->dropForeign(['creator_id']);
            $table->dropForeign(['brand_id']);
        });

        Schema::dropIfExists('collections');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('creator_id');
            $table->foreign('creator_id')
                ->references('id')
                ->on('users');
            $table->unsignedBigInteger('brand_id');
            $table->foreign('brand_id')
                ->references('id')
                ->on('brands');
            $table->string('name');
            $table->string('slug');
            $table->string('preview_image_1')->after('slug');
            $table->string('preview_image_2')->after('slug');
            $table->string('preview_image_3')->after('slug');
            $table->string('preview_image_4')->after('slug');
            $table->timestamps();
        });
    }
};
