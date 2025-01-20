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
            $table->string('slide_url')->after('description')->nullable();
            $table->boolean('display_button')->after('slide_url')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('home_page_slides', function (Blueprint $table) {
            $table->dropColumn('slide_url');
            $table->dropColumn('display_button');
        });
    }
};
