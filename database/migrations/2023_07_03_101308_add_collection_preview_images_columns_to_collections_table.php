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
            $table->string('preview_image_1')->after('slug');
            $table->string('preview_image_2')->after('slug');
            $table->string('preview_image_3')->after('slug');
            $table->string('preview_image_4')->after('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->dropColumn(['preview_image_1', 'preview_image_2', 'preview_image_3', 'preview_image_4']);
        });
    }
};
