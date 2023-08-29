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
        Schema::table('home_page_slides', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->json('title')->after('id');
            $table->json('button_text')->after('title');
            $table->string('button_url')->after('button_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('home_page_slides', function (Blueprint $table) {
            $table->json('description')->after('id');
            $table->dropColumn('title');
            $table->dropColumn('button_text');
            $table->dropColumn('button_url');
        });
    }
};
