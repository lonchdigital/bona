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
        Schema::table('home_page_testimonials', function (Blueprint $table) {
            $table->tinyInteger('rating')->after('review')->nullable();
            $table->date('date')->after('rating')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('home_page_testimonials', function (Blueprint $table) {
            $table->dropColumn('rating');
            $table->dropColumn('date');
        });
    }
};
