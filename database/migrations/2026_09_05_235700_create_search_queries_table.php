<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_queries', function (Blueprint $table) {
            $table->id();
            $table->string('query', 160);
            $table->string('normalized_query', 160);
            $table->string('locale', 10);
            $table->unsignedBigInteger('search_count')->default(0);
            $table->unsignedInteger('results_count')->default(0);
            $table->timestamp('first_searched_at');
            $table->timestamp('last_searched_at');
            $table->timestamps();

            $table->unique(['normalized_query', 'locale']);
            $table->index('last_searched_at');
            $table->index('search_count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_queries');
    }
};
