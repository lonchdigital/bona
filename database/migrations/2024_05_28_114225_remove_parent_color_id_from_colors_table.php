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
        Schema::table('colors', function (Blueprint $table) {
            $table->dropForeign(['parent_color_id']);
            $table->dropColumn(['parent_color_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('colors', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_color_id')
                ->nullable()
                ->default(null)
                ->after('creator_id');
            $table->foreign('parent_color_id')
                ->references('id')
                ->on('colors');
        });
    }
};
