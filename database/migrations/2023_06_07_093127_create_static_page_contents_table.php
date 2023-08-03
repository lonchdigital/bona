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
        Schema::create('static_page_contents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('static_page_id');
            $table->foreign('static_page_id')
                ->references('id')
                ->on('static_pages');
            $table->string('language');
            $table->text('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('static_page_contents');
    }
};
