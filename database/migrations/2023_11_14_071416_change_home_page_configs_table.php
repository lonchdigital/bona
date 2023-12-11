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
        Schema::table('home_page_configs', function (Blueprint $table) {
            $table->dropForeign(['collection_id']);
            $table->dropColumn(['collection_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('home_page_configs', function (Blueprint $table) {
            $table->unsignedBigInteger('collection_id')->nullable();
            $table->foreign('collection_id')
                ->references('id')
                ->on('collections');
        });
    }
};
